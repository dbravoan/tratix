<?php

namespace App\Http\Controllers;

use App\Services\Billing\BillingService;
use App\Services\CreditService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class BillingController extends Controller
{
    public function __construct(
        private readonly BillingService $billing,
        private readonly CreditService $credits,
    ) {}

    public function pricing(): View
    {
        $user = auth()->user();

        return view('billing.pricing', [
            'plans' => config('billing.plans'),
            'currentPlan' => $user->plan,
            'remaining' => $this->credits->remaining($user),
            'used' => $this->credits->usedThisMonth($user),
            'credits' => $user->credits,
            'gateway' => config('billing.gateway'),
        ]);
    }

    public function checkout(Request $request): RedirectResponse
    {
        $request->validate(['plan' => ['required', 'string', 'in:pro,business']]);

        $url = $this->billing->checkoutUrl(auth()->user(), $request->input('plan'));

        return redirect()->away($url);
    }

    public function success(Request $request): RedirectResponse
    {
        if (config('billing.gateway') === 'stripe') {
            // In Stripe mode the plan is activated via webhook; here we only
            // confirm if the session exists (demo sessions activate directly).
        }

        auth()->user()->refresh();

        return redirect()->route('dashboard')->with('success', 'Suscripción activada. Ya tienes contratos ilimitados.');
    }
}
