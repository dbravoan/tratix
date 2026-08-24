<?php

namespace App\Http\Controllers;

use App\Services\Billing\BillingService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Stripe\Exception\SignatureVerificationException;
use Stripe\Webhook;

class BillingWebhookController extends Controller
{
    public function __construct(private readonly BillingService $billing) {}

    public function handle(Request $request): JsonResponse
    {
        $payload = json_decode($request->getContent(), true);

        if (! is_array($payload)) {
            return response()->json(['error' => 'Invalid payload'], 400);
        }

        if (config('billing.gateway') === 'stripe') {
            $secret = config('billing.stripe.webhook_secret');
            if (empty($secret)) {
                Log::error('billing.stripe.webhook_secret_missing');

                return response()->json(['error' => 'Stripe webhook secret is not configured.'], 400);
            }

            $signature = $request->header('Stripe-Signature');
            if (empty($signature)) {
                return response()->json(['error' => 'Missing Stripe-Signature header.'], 400);
            }

            try {
                Webhook::constructEvent(
                    $request->getContent(),
                    $signature,
                    $secret
                );
            } catch (\UnexpectedValueException|SignatureVerificationException $e) {
                Log::warning('billing.stripe.webhook_invalid_signature', ['error' => $e->getMessage()]);

                return response()->json(['error' => 'Invalid signature'], 400);
            }
        }

        $this->billing->gateway()->handleWebhook($payload);

        return response()->json(['status' => 'ok']);
    }
}
