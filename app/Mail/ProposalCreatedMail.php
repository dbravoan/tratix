<?php

namespace App\Mail;

use App\Models\ClauseProposal;
use App\Models\Contract;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class ProposalCreatedMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public Contract $contract,
        public ClauseProposal $proposal,
        public string $actionUrl,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: '✏️ Propuesta de modificación de cláusula · '.$this->contract->reference,
        );
    }

    public function content(): Content
    {
        return new Content(
            markdown: 'mail.proposal-created',
            with: [
                'contract' => $this->contract,
                'proposal' => $this->proposal,
                'actionUrl' => $this->actionUrl,
            ],
        );
    }
}
