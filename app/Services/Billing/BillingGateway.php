<?php

namespace App\Services\Billing;

use App\Models\User;

interface BillingGateway
{
    /**
     * Create a checkout session and return its URL.
     */
    public function checkout(User $user, string $plan, string $successUrl, string $cancelUrl): string;

    /**
     * Handle a completed payment webhook event.
     */
    public function handleWebhook(array $payload): void;
}
