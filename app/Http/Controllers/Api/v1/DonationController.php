<?php

namespace App\Http\Controllers\Api\v1;

use App\Entities\Donations\Models\Donation;
use App\Entities\Donations\Models\DonationPerk;
use App\Entities\Groups\Models\Group;
use App\Entities\Payments\AccountPaymentType;
use App\Entities\Payments\Models\AccountPayment;
use App\Entities\Payments\Models\AccountPaymentSession;
use App\Http\ApiController;
use App\Library\Stripe\StripeHandler;
use App\Library\Stripe\StripeWebhook;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

final class DonationController extends ApiController
{
    /**
     * @var StripeHandler
     */
    private $stripeHandler;

    public function __construct(StripeHandler $stripeHandler)
    {
        $this->stripeHandler = $stripeHandler;
    }

    public function create(Request $request)
    {
        $accountId = $request->get('account_id');
        $amountInDollars = $request->get('amount', 3.00);
        $amountInCents = $amountInDollars * 100;

        $pcbSessionUuid = Str::uuid();
        $stripeSessionId = $this->stripeHandler->createCheckoutSession($pcbSessionUuid, $amountInCents);

        $session = AccountPaymentSession::create([
            'session_id' => $pcbSessionUuid->toString(),
            'account_id' => $accountId,
            'is_processed' => false,
        ]);

        Log::debug('Generated payment session', ['session' => $session]);

        return [
            'data' => [
                'session_id' => $stripeSessionId,
            ],
        ];
    }

    /**
     * Receives a Webhook from Stripe for payments.
     *
     *
     * @return StripeWebhook
     *
     * @throws \Exception
     *
     * Example Webhook Payload:
     * {
     *    "created": 1326853478,
     *    "livemode": false,
     *    "id": "evt_00000000000000",
     *    "type": "checkout.session.completed",
     *    "object": "event",
     *    "request": null,
     *    "pending_webhooks": 1,
     *    "api_version": "2018-07-27",
     *    "data": {
     *       "object": {
     *           "id": "cs_00000000000000",
     *           "object": "checkout.session",
     *           "billing_address_collection": null,
     *           "cancel_url": "https://example.com/cancel",
     *           "client_reference_id": null,
     *           "customer": null,
     *           "customer_email": null,
     *           "display_items": [
     *               {
     *                   "amount": 1500,
     *                   "currency": "usd",
     *                   "custom": {
     *                       "description": "Comfortable cotton t-shirt",
     *                       "images": null,
     *                       "name": "T-shirt"
     *                   },
     *                   "quantity": 2,
     *                   "type": "custom"
     *               }
     *           ],
     *           "livemode": false,
     *           "locale": null,
     *           "mode": null,
     *           "payment_intent": "pi_00000000000000",
     *           "payment_method_types": [
     *               "card"
     *           ],
     *           "setup_intent": null,
     *           "submit_type": null,
     *           "subscription": null,
     *           "success_url": "https://example.com/success"
     *       }
     *    }
     * }
     */
    public function store(StripeWebhook $webhook)
    {
        // Sanity check
        if ($webhook->getAmountInCents() <= 0) {
            throw new \Exception('Received a zero amount donation from Stripe');
        }

        $session = AccountPaymentSession::where('session_id', $webhook->getSessionId())->first();
        if ($session === null) {
            throw new \Exception('Could not fulfill donation. Internal session id not found: '.$webhook->getSessionId());
        }
        Log::debug('Found associated session', ['session' => $session]);

        $accountId = $session->account !== null ? $session->account->getKey() : null;
        $amountInCents = $webhook->getAmountInCents();
        $amountInDollars = (float) ($amountInCents / 100);

        $numberOfMonthsOfPerks = 0;
        $donationExpiry = null;
        $isLifetime = $amountInDollars >= Donation::LIFETIME_REQUIRED_AMOUNT;

        if (! $isLifetime) {
            $numberOfMonthsOfPerks = floor($amountInDollars / Donation::ONE_MONTH_REQUIRED_AMOUNT);
            $donationExpiry = now()->addMonths($numberOfMonthsOfPerks);
        }

        $donation = null;
        DB::beginTransaction();
        try {
            $donation = Donation::create([
                'account_id' => $accountId,
                'amount' => $amountInDollars,
            ]);

            if ($accountId !== null) {
                DonationPerk::create([
                    'donation_id' => $donation->getKey(),
                    'account_id' => $accountId,
                    'is_active' => true,
                    'is_lifetime_perks' => $isLifetime,
                    'expires_at' => $donationExpiry,
                ]);
            }

            AccountPayment::create([
                'payment_type' => AccountPaymentType::Donation,
                'payment_id' => $donation->getKey(),
                'payment_amount' => $amountInCents,
                'payment_source' => $webhook->getTransactionId(),
                'account_id' => $accountId,
                'is_processed' => true,
                'is_refunded' => false,
                'is_subscription_payment' => false,
            ]);

            $session->is_processed = true;
            $session->save();

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }

        // Add user to Donator group if they're logged in
        if ($session->account !== null && ($numberOfMonthsOfPerks > 0 || $isLifetime)) {
            Log::debug('Adding donator perks to account');

            $donatorGroup = Group::where('name', 'donator')->first();
            $donatorGroupId = $donatorGroup->getKey();

            if (! $session->account->groups->contains($donatorGroupId)) {
                $session->account->groups()->attach($donatorGroupId);
            }

            // Detach the user from the member group
            $memberGroup = Group::where('is_default', true)->first();
            $memberGroupId = $memberGroup->getKey();
            $session->account->groups()->detach($memberGroupId);
        }

        return response()->json(null, 200);
    }
}
