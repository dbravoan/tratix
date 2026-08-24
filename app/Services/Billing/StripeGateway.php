<?php

namespace App\Services\Billing;

use App\Models\User;
use Illuminate\Support\Facades\Log;
use Stripe\Checkout\Session;
use Stripe\Stripe;

/**
 * Stripe gateway. Active when STRIPE_SECRET is configured.
 *
 * @see https://stripe.com/docs/payments/checkout
 */
class StripeGateway implements BillingGateway
{
    public function __construct()
    {
        Stripe::setApiKey(config('billing.stripe.secret'));
    }

    public function checkout(User $user, string $plan, string $successUrl, string $cancelUrl): string
    {
        $planConfig = config("billing.plans.{$plan}");
        $priceId = $planConfig['stripe_price_id'] ?? null;

        if (! $priceId) {
            throw new \RuntimeException("No se ha configurado un precio de Stripe para el plan {$plan}.");
        }

        $session = Session::create([
            'mode' => 'subscription',
            'customer_email' => $user->email,
            'line_items' => [[
                'price' => $priceId,
                'quantity' => 1,
            ]],
            'success_url' => $successUrl,
            'cancel_url' => $cancelUrl,
            'metadata' => ['user_id' => $user->id, 'plan' => $plan],
        ]);

        return $session->url;
    }

    public function handleWebhook(array $payload): void
    {
        $eventType = $payload['type'] ?? null;
        $session = $payload['data']['object'] ?? [];

        if ($eventType === 'checkout.session.completed') {
            $userId = $session['metadata']['user_id'] ?? null;
            $plan = $session['metadata']['plan'] ?? 'pro';

            if ($userId && $user = User::find($userId)) {
                $user->update(['plan' => $plan]);
                Log::info('billing.stripe.subscription_activated', ['user_id' => $userId, 'plan' => $plan]);
            }
        }
    }
}
