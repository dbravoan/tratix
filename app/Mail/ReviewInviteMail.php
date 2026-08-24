<?php

namespace App\Mail;

use App\Models\Contract;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class ReviewInviteMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public Contract $contract, public string $reviewUrl, public string $recipientRole) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Te invitan a revisar un contrato de compraventa · '.$this->contract->reference,
        );
    }

    public function content(): Content
    {
        return new Content(
            markdown: 'mail.review-invite',
            with: [
                'contract' => $this->contract,
                'reviewUrl' => $this->reviewUrl,
                'recipientRole' => $this->recipientRole === 'vendedor' ? 'vendedor' : 'comprador',
            ],
        );
    }
}
