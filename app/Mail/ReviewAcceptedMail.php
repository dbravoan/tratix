<?php

namespace App\Mail;

use App\Models\Contract;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class ReviewAcceptedMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public Contract $contract,
        public string $acceptorName,
        public string $acceptorRole,
        public string $actionUrl,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: '✅ Borrador aceptado por '.$this->acceptorName.' · '.$this->contract->reference,
        );
    }

    public function content(): Content
    {
        return new Content(
            markdown: 'mail.review-accepted',
            with: [
                'contract' => $this->contract,
                'acceptorName' => $this->acceptorName,
                'acceptorRole' => $this->acceptorRole,
                'actionUrl' => $this->actionUrl,
            ],
        );
    }
}
