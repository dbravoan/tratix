<?php

namespace App\Mail;

use App\Models\Contract;
use App\Models\ContractComment;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class ContractCommentMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public Contract $contract,
        public ContractComment $comment,
        public string $actionUrl,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: '💬 Nuevo comentario en el contrato · '.$this->contract->reference,
        );
    }

    public function content(): Content
    {
        return new Content(
            markdown: 'mail.contract-comment',
            with: [
                'contract' => $this->contract,
                'comment' => $this->comment,
                'actionUrl' => $this->actionUrl,
            ],
        );
    }
}
