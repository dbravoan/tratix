<?php

namespace App\Services;

use App\Models\Contract;
use App\Models\User;

/**
 * Freemium credits: free plan allows a limited number of contracts per month;
 * Pro plan is unlimited. Used to gate contract creation and surface upgrades.
 */
class CreditService
{
    public const FREE_MONTHLY_CONTRACTS = 3;

    public function monthlyLimit(User $user): ?int
    {
        if (in_array($user->plan, ['pro', 'business'], true)) {
            return null; // unlimited
        }

        $planLimit = config("billing.plans.{$user->plan}.monthly_contracts");

        return $planLimit !== null ? (int) $planLimit : (int) config('billing.plans.free.monthly_contracts', self::FREE_MONTHLY_CONTRACTS);
    }

    public function usedThisMonth(User $user): int
    {
        return Contract::query()
            ->where('user_id', $user->id)
            ->where('created_at', '>=', now()->startOfMonth())
            ->where('status', '!=', 'cancelado')
            ->count();
    }

    public function remaining(User $user): int
    {
        $limit = $this->monthlyLimit($user);

        if ($limit === null) {
            return PHP_INT_MAX;
        }

        $freeRemaining = max(0, $limit - $this->usedThisMonth($user));

        return $freeRemaining + max(0, (int) $user->credits);
    }

    public function canCreate(User $user): bool
    {
        return $this->remaining($user) > 0;
    }

    /**
     * Consumes a credit if the user has exhausted their free monthly allowance.
     */
    public function consumeIfApplicable(User $user): void
    {
        $limit = $this->monthlyLimit($user);

        if ($limit === null) {
            return;
        }

        // If user already used all free allowance, deduct 1 credit if available
        if ($this->usedThisMonth($user) > $limit && $user->credits > 0) {
            $user->decrement('credits');
        }
    }
}
