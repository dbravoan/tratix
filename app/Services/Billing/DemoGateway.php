<?php

namespace App\Services\Billing;

use App\Models\User;
use Illuminate\Support\Facades\Log;

/**
 * Local gateway for development and tests: activates the plan immediately and
 * records a "payment" event. No external credentials required.
 */
class DemoGateway implements BillingGateway
{
    public function checkout(User $user, string $plan, string $successUrl, string $cancelUrl): string
    {
        $user->update(['plan' => $plan]);

        Log::info('billing.demo.checkout', [
            'user_id' => $user->id,
            'plan' => $plan,
            'success_url' => $successUrl,
        ]);

        return $successUrl.'?session_id=demo_'.str()->uuid();
    }

    public function handleWebhook(array $payload): void
    {
        Log::info('billing.demo.webhook', $payload);
    }
}
