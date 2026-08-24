<?php

namespace App\Mail;

use App\Models\Contract;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class ReminderMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public Contract $contract,
        public string $actionUrl,
        public string $message,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Recordatorio · '.$this->contract->reference,
        );
    }

    public function content(): Content
    {
        return new Content(
            markdown: 'mail.reminder',
            with: [
                'contract' => $this->contract,
                'actionUrl' => $this->actionUrl,
                'reminderMessage' => $this->message,
                'messageText' => $this->message,
            ],
        );
    }
}
