<?php

namespace App\Mail;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class MonthlySummaryMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public User $user,
        public int $totalContracts,
        public int $signedThisMonth,
        public int $pending,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Tu resumen mensual de contratos · Tratix',
        );
    }

    public function content(): Content
    {
        return new Content(
            markdown: 'mail.monthly-summary',
            with: [
                'user' => $this->user,
                'totalContracts' => $this->totalContracts,
                'signedThisMonth' => $this->signedThisMonth,
                'pending' => $this->pending,
            ],
        );
    }
}
