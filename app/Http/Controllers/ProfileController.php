<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProfileUpdateRequest;
use App\Models\Referral;
use App\Models\Signature;
use App\Services\CreditService;
use App\Services\ReferralService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Redirect;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\Response;

class ProfileController extends Controller
{
    public function __construct(
        private readonly ReferralService $referrals,
        private readonly CreditService $credits,
    ) {}

    /**
     * Display the user's profile and premium management center.
     */
    public function edit(Request $request): View
    {
        $user = $request->user();

        $totalContracts = $user->contracts()->count();
        $thisMonthContracts = $this->credits->usedThisMonth($user);
        $sealedContracts = $user->contracts()->where('status', 'firmado')->count();
        $pendingSignatures = $user->contracts()->whereIn('status', ['lista_para_firma', 'en_firma'])->count();

        $referralLink = $this->referrals->referralUrl($user);
        $userReferrals = Referral::where('referrer_id', $user->id)->with('referred')->latest()->get();

        return view('profile.edit', [
            'user' => $user,
            'totalContracts' => $totalContracts,
            'thisMonthContracts' => $thisMonthContracts,
            'sealedContracts' => $sealedContracts,
            'pendingSignatures' => $pendingSignatures,
            'referralLink' => $referralLink,
            'userReferrals' => $userReferrals,
            'plans' => config('billing.plans'),
            'gateway' => config('billing.gateway'),
        ]);
    }

    /**
     * Update the user's profile information.
     */
    public function update(ProfileUpdateRequest $request): RedirectResponse
    {
        $data = $request->validated();

        // Checkbox booleans
        $data['notify_comments'] = $request->boolean('notify_comments');
        $data['notify_proposals'] = $request->boolean('notify_proposals');
        $data['notify_signatures'] = $request->boolean('notify_signatures');
        $data['notify_summary'] = $request->boolean('notify_summary');

        $request->user()->fill($data);

        if ($request->user()->isDirty('email')) {
            $request->user()->email_verified_at = null;
        }

        $request->user()->save();

        return Redirect::route('profile.edit')->with('status', 'profile-updated')->with('success', 'Perfil y preferencias actualizadas correctamente.');
    }

    /**
     * Quick demo plan switch for development/testing.
     */
    public function switchPlanDemo(Request $request): RedirectResponse
    {
        if (config('billing.gateway') !== 'demo') {
            abort(403);
        }

        $plan = $request->validate(['plan' => ['required', 'string', 'in:free,pro,business']])['plan'];

        $user = $request->user();
        $user->update(['plan' => $plan]);

        return back()->with('success', 'Plan cambiado a '.ucfirst($plan).' en modo demostración.');
    }

    /**
     * Delete the user's account.
     */
    public function destroy(Request $request): RedirectResponse
    {
        $request->validateWithBag('userDeletion', [
            'password' => ['required', 'current_password'],
        ]);

        $user = $request->user();

        Auth::logout();

        $user->delete();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return Redirect::to('/');
    }

    /**
     * Export all personal data in structured JSON (GDPR Art. 20 - Data Portability).
     */
    public function exportGdprData(Request $request): Response
    {
        $user = $request->user();

        $contracts = $user->contracts()
            ->with(['parties', 'versions', 'documents'])
            ->get()
            ->map(function ($contract) {
                return [
                    'reference' => $contract->reference,
                    'title' => $contract->title,
                    'contract_type' => $contract->contract_type,
                    'status' => $contract->status,
                    'applicable_law' => $contract->applicable_law,
                    'total_amount' => $contract->total_amount,
                    'currency' => $contract->currency,
                    'city' => $contract->city,
                    'signing_date' => $contract->signing_date?->toDateString(),
                    'sealed_at' => $contract->sealed_at?->toIso8601String(),
                    'parties' => $contract->parties->map(fn ($p) => [
                        'role' => $p->role,
                        'full_name' => $p->full_name,
                        'tax_id' => $p->tax_id,
                        'email' => $p->email,
                        'city' => $p->city,
                        'country' => $p->country,
                    ]),
                ];
            });

        $signatures = Signature::where('signer_email', $user->email)
            ->with('contract')
            ->get()
            ->map(fn ($s) => [
                'contract_reference' => $s->contract?->reference,
                'party_role' => $s->party_role,
                'signature_type' => $s->signature_type,
                'signed_at' => $s->signed_at?->toIso8601String(),
                'ip' => $s->ip,
                'otp_verified' => $s->otp_verified,
                'consent_text' => $s->consent_text,
            ]);

        $payload = [
            'rgpd_export_metadata' => [
                'exported_at' => now()->toIso8601String(),
                'regulation' => 'Reglamento General de Protección de Datos (RGPD UE 2016/679) - Artículo 20 (Derecho a la portabilidad de los datos)',
                'controller' => config('app.name'),
                'contact' => 'privacidad@'.request()->getHost(),
            ],
            'user_account' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'plan' => $user->plan,
                'created_at' => $user->created_at?->toIso8601String(),
                'email_verified_at' => $user->email_verified_at?->toIso8601String(),
            ],
            'fiscal_profile' => [
                'company_name' => $user->company_name,
                'tax_id' => $user->tax_id,
                'phone' => $user->phone,
                'address' => $user->address,
                'postal_code' => $user->postal_code,
                'city' => $user->city,
                'country' => $user->country,
            ],
            'notification_preferences' => [
                'notify_comments' => (bool) $user->notify_comments,
                'notify_proposals' => (bool) $user->notify_proposals,
                'notify_signatures' => (bool) $user->notify_signatures,
                'notify_summary' => (bool) $user->notify_summary,
            ],
            'contracts' => $contracts,
            'signatures_and_consents' => $signatures,
        ];

        $json = json_encode($payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        $filename = 'mis-datos-personales-rgpd-'.now()->format('Ymd-His').'.json';

        return response($json, 200, [
            'Content-Type' => 'application/json',
            'Content-Disposition' => 'attachment; filename="'.$filename.'"',
        ]);
    }

    /**
     * Submit a formal GDPR rights exercise request.
     */
    public function requestGdprRight(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'right_type' => ['required', 'string', 'in:acceso,rectificacion,supresion,limitacion,portabilidad,oposicion'],
            'description' => ['required', 'string', 'min:10', 'max:2000'],
        ]);

        Log::info('GDPR Right Requested', [
            'user_id' => $request->user()->id,
            'user_email' => $request->user()->email,
            'right_type' => $data['right_type'],
            'description' => $data['description'],
            'ip' => $request->ip(),
            'requested_at' => now()->toIso8601String(),
        ]);

        return back()->with('success', 'Tu solicitud formal de ejercicio del derecho de '.strtoupper($data['right_type']).' ha sido registrada con acuse de recibo legal. Se tramitará conforme al RGPD en un plazo máximo de 30 días.');
    }
}
