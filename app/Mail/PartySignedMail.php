<?php

namespace App\Mail;

use App\Models\Contract;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class PartySignedMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public Contract $contract,
        public string $signerName,
        public string $signerRole,
        public string $actionUrl,
        public bool $isPendingParty = true
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Firma registrada ('.ucfirst($this->signerRole).') · '.$this->contract->reference,
        );
    }

    public function content(): Content
    {
        return new Content(
            markdown: 'mail.party-signed',
            with: [
                'contract' => $this->contract,
                'signerName' => $this->signerName,
                'signerRole' => $this->signerRole,
                'actionUrl' => $this->actionUrl,
                'isPendingParty' => $this->isPendingParty,
            ],
        );
    }
}
