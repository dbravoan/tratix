<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class ContactController extends Controller
{
    public function submit(Request $request): JsonResponse|RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'email' => ['required', 'email:rfc', 'max:150'],
            'subject' => ['required', 'string', 'max:200'],
            'message' => ['required', 'string', 'min:10', 'max:3000'],
            'gdpr_consent' => ['accepted'],
            'website_hp' => ['nullable', 'string', 'max:0'], // Anti-bot honeypot
        ], [
            'gdpr_consent.accepted' => 'Debes aceptar la política de privacidad para enviar tu consulta.',
            'message.min' => 'El mensaje debe tener al menos 10 caracteres.',
        ]);

        // Log support contact request
        Log::info('New contact request received', [
            'name' => $validated['name'],
            'email' => $validated['email'],
            'subject' => $validated['subject'],
            'ip' => $request->ip(),
        ]);

        $successMsg = '¡Gracias por contactar con Tratix! Hemos recibido tu mensaje y nuestro equipo de soporte te responderá en menos de 24 horas laborables.';

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => $successMsg,
            ]);
        }

        return back()->with('success', $successMsg);
    }
}
