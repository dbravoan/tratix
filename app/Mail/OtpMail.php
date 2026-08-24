<?php

namespace App\Mail;

use App\Models\Contract;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class OtpMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public Contract $contract, public string $code) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Tu código de verificación · '.$this->contract->reference,
        );
    }

    public function content(): Content
    {
        return new Content(
            markdown: 'mail.otp',
            with: [
                'contract' => $this->contract,
                'code' => $this->code,
            ],
        );
    }
}
