<?php

namespace App\Http\Controllers;

use App\Entities\Donations\Repositories\DonationRepository;
use App\Services\Donations\DonationCreationService;
use App\Services\Donations\DonationStatsService;
use Illuminate\Support\Facades\View;
use Illuminate\Http\Request;
use Illuminate\Contracts\Auth\Guard as Auth;
use App\Library\Discourse\Api\DiscourseUserApi;
use App\Entities\Groups\Repositories\GroupRepository;
use App\Http\WebController;

class DonationController extends WebController
{
    /**
     * @var DonationRepository
     */
    private $donationRepository;

    /**
     * @var DonationCreationService
     */
    private $donationCreationService;

    /**
     * @var DiscourseUserApi
     */
    private $discourseUserApi;


    /**
     * @var GroupRepository
     */
    private $groupRepository;

    /**
     * @var Auth
     */
    private $auth;


    public function __construct(
        DonationRepository $donationRepository,
        DonationCreationService $donationCreationService,
        DiscourseUserApi $discourseUserApi,
        GroupRepository $groupRepository,
        Auth $auth
    ) {
        $this->donationRepository = $donationRepository;
        $this->donationCreationService = $donationCreationService;
        $this->discourseUserApi = $discourseUserApi;
        $this->groupRepository = $groupRepository;
        $this->auth = $auth;
    }

    public function getView()
    {
        return view('front.pages.donate.donate');
    }

    public function donate(Request $request)
    {
        $email = $request->get('stripe_email');
        $stripeToken = $request->get('stripe_token');
        $amount = $request->get('stripe_amount_in_cents');

        if ($amount <= 0) {
            abort(401, "Attempted to donate zero dollars");
        }

        $account = $this->auth->user();
        $accountId = $account !== null ? $account->getKey() : null;

        try {
            $donation = $this->donationCreationService->donate($stripeToken, $email, $amount, $accountId);
        } catch (\Stripe\Error\Card $exception) {
            $body = $exception->getJsonBody();
            $message = $body['error']['message'];

            return view('front.pages.donate.donate-error', [
                'message' => $message
            ]);
        } catch (\Stripe\Error\Base $e) {
            app('sentry')->captureException($e);
            return view('front.pages.donate.donate-error', [
                'message' => "There was a problem processing your transaction, please try again later."
            ]);
        }

        // add user to donator group if they're logged in
        if ($account !== null) {
            $donatorGroup = $this->groupRepository->getGroupByName("donator");
            $donatorGroupId = $donatorGroup->getKey();

            if ($account->groups->contains($donatorGroupId) === false) {
               $account->groups->attach($donatorGroupId);
            }
        }

        return view('front.pages.donate.donate-thanks', [
            'donation' => $donation,
        ]);
    }
}
