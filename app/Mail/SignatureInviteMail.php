<?php

namespace App\Mail;

use App\Models\Contract;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class SignatureInviteMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public Contract $contract, public string $signUrl) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Te invitan a firmar un contrato de compraventa · '.$this->contract->reference,
        );
    }

    public function content(): Content
    {
        return new Content(
            markdown: 'mail.signature-invite',
            with: [
                'contract' => $this->contract,
                'signUrl' => $this->signUrl,
            ],
        );
    }
}
