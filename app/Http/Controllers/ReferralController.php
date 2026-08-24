<?php

namespace App\Http\Controllers;

use App\Models\Referral;
use App\Services\ReferralService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ReferralController extends Controller
{
    public function __construct(private readonly ReferralService $referralService) {}

    /**
     * Landing for a referral link: stores the code and directs to register.
     */
    public function show(Request $request, string $code): RedirectResponse
    {
        session(['referral_code' => strtoupper($code)]);

        return redirect()->route('register');
    }

    /**
     * Authenticated referrals dashboard (own link + stats).
     */
    public function index(): View
    {
        $user = auth()->user();
        $link = $this->referralService->referralUrl($user);
        $referrals = Referral::where('referrer_id', $user->id)->latest()->get();
        $referredCount = $referrals->count();

        return view('referrals.index', compact('link', 'referrals', 'referredCount'));
    }
}
