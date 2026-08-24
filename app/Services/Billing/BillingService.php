<?php

namespace App\Services\Billing;

use App\Models\User;

class BillingService
{
    public function __construct(private readonly BillingGateway $gateway) {}

    public function gateway(): BillingGateway
    {
        return $this->gateway;
    }

    public function checkoutUrl(User $user, string $plan): string
    {
        $success = route('billing.success').'?plan='.$plan;
        $cancel = route('billing.pricing');

        return $this->gateway->checkout($user, $plan, $success, $cancel);
    }

    public function activatePlan(User $user, string $plan): void
    {
        $user->update(['plan' => $plan]);
    }

    public function hasActiveSubscription(User $user): bool
    {
        return $user->plan === 'pro';
    }
}
