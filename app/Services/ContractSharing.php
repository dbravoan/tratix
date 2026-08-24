<?php

namespace App\Services;

use App\Models\Contract;
use App\Models\Party;

/**
 * Builds the sharing payload (Email / WhatsApp / copyable link) for a contract
 * depending on its state. Centralises message text so the modal and tests agree.
 */
class ContractSharing
{
    /**
     * The link to share with the counterparty, depending on the contract state.
     */
    public function shareLink(Contract $contract, ?string $role = null): ?string
    {
        $token = $contract->access_token;

        if (! $token) {
            return null;
        }

        return match ($contract->status) {
            'borrador', 'en_revision' => route('review.show', $token),
            'lista_para_firma', 'en_firma' => $role
                ? route('sign.show', ['token' => $token, 'role' => $role])
                : route('sign.show', $token),
            'firmado' => $contract->final_pdf_path
                ? route('sign.download', $token)
                : null,
            default => null,
        };
    }

    /**
     * A short human action label for the current state.
     */
    public function actionLabel(Contract $contract): string
    {
        return match ($contract->status) {
            'borrador' => 'complete sus datos fiscales y revise el borrador del contrato',
            'en_revision' => 'revise el borrador del contrato y, si lo desea, proponga cambios',
            'lista_para_firma', 'en_firma' => 'firme electrónicamente el contrato',
            'firmado' => 'descargue el PDF firmado del contrato',
            default => 'vea el contrato',
        };
    }

    /**
     * The counterparty (the role not owned by the creator).
     */
    public function counterparty(Contract $contract): ?Party
    {
        return $contract->counterparty();
    }

    /**
     * The target email for sharing: counterparty email, else the invited email.
     */
    public function targetEmail(Contract $contract): ?string
    {
        return $this->counterparty($contract)?->email
            ?? $contract->invited_email
            ?? null;
    }

    /**
     * The target WhatsApp number (digits only) for the counterparty, if any.
     */
    public function targetWhatsApp(Contract $contract): ?string
    {
        $phone = $this->counterparty($contract)?->phone;

        if (blank($phone)) {
            return null;
        }

        return preg_replace('/[^0-9]/', '', (string) $phone) ?: null;
    }

    /**
     * Default message text (without the link).
     */
    public function message(Contract $contract): string
    {
        return "Hola, te comparto el contrato {$contract->reference} ({$contract->title}). "
            ."Puedes {$this->actionLabel($contract)} desde aquí: ";
    }

    /**
     * Full WhatsApp share URL (generic or direct to a number).
     */
    public function whatsAppUrl(Contract $contract): ?string
    {
        $link = $this->shareLink($contract);

        if (! $link) {
            return null;
        }

        $text = rawurlencode($this->message($contract).$link);
        $number = $this->targetWhatsApp($contract);

        return $number
            ? "https://wa.me/{$number}?text={$text}"
            : "https://wa.me/?text={$text}";
    }

    /**
     * Full mailto: URL with subject and body (link included).
     */
    public function mailToUrl(Contract $contract): ?string
    {
        $link = $this->shareLink($contract);

        if (! $link) {
            return null;
        }

        $to = $this->targetEmail($contract) ?? '';

        $subject = rawurlencode("Contrato {$contract->reference}: ".$this->actionLabel($contract));
        $body = rawurlencode($this->message($contract).$link);

        return "mailto:{$to}?subject={$subject}&body={$body}";
    }
}
