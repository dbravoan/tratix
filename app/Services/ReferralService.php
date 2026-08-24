<?php

namespace App\Services;

use App\Models\Referral;
use App\Models\User;

class ReferralService
{
    public function generateCode(User $user): string
    {
        if ($user->referral_code) {
            return $user->referral_code;
        }

        $code = strtoupper(substr(md5($user->id.'-'.uniqid()), 0, 8));
        $user->update(['referral_code' => $code]);

        return $code;
    }

    public function referralUrl(User $user): string
    {
        return url('/ref/'.$this->generateCode($user));
    }

    /**
     * Registers a referral: both referrer and referred get their rewards.
     */
    public function applyReferral(string $code, User $referred): void
    {
        $referrer = User::where('referral_code', strtoupper($code))->first();

        if (! $referrer || $referrer->id === $referred->id) {
            return;
        }

        $already = Referral::where('referred_id', $referred->id)->exists();
        if ($already) {
            return;
        }

        Referral::create([
            'referrer_id' => $referrer->id,
            'referred_id' => $referred->id,
            'code' => strtoupper($code),
        ]);

        $refCfg = config('billing.referral');

        if ($referrer->plan === 'free') {
            $referrer->increment('credits', (int) ($refCfg['referrer_credits'] ?? 0));
        }
        $referred->increment('credits', (int) ($refCfg['referred_credits'] ?? 0));
    }
}
