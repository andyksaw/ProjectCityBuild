<?php

namespace App\Http\Controllers\Api\v1;

use App\Http\ApiController;
use App\Library\Stripe\StripeHandler;
use App\Library\Stripe\StripeWebhookEvent;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

final class WebhookController extends ApiController
{
    public function stripe(Request $request, StripeHandler $stripeHandler)
    {
        $endpointSecret = config('services.stripe.webhook.secret');
        $payload = $request->getContent();
        $signature = $request->headers->get('Stripe-Signature');

        Log::debug('Received Stripe webhook', [
            'payload' => $payload,
            'signature' => $signature,
        ]);

        $webhook = $stripeHandler->getWebhookEvent($payload, $signature, $endpointSecret);

        if ($webhook === null) {
            Log::debug('No handler for webhook event');

            return;
        }

        switch ($webhook->getEvent()) {
            case StripeWebhookEvent::CheckoutSessionCompleted:
                Log::info('Webhook acknowledged');

                $controller = new DonationController($stripeHandler);

                return $controller->store($webhook);

            default:
                Log::info('Webhook ignored');

                return response()->json(null, 204);
        }
    }
}
