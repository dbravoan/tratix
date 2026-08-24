<?php

namespace App\Mail;

use App\Models\Contract;
use App\Services\ContractPdfService;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Attachment;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Storage;

class ContractSignedMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public Contract $contract, public string $downloadUrl) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Contrato firmado y sellado · '.$this->contract->reference,
        );
    }

    public function content(): Content
    {
        return new Content(
            markdown: 'mail.contract-signed',
            with: [
                'contract' => $this->contract,
                'downloadUrl' => $this->downloadUrl,
            ],
        );
    }

    /**
     * Get the attachments for the message.
     *
     * @return array<int, Attachment>
     */
    public function attachments(): array
    {
        $disk = Storage::disk('local');
        $path = $this->contract->final_pdf_path;

        if ($path && $disk->exists($path)) {
            return [
                Attachment::fromStorageDisk('local', $path)
                    ->as($this->contract->reference.'-firmado.pdf')
                    ->withMime('application/pdf'),
            ];
        }

        try {
            $pdfContent = app(ContractPdfService::class)->render($this->contract)->output();

            return [
                Attachment::fromData(fn () => $pdfContent, $this->contract->reference.'-firmado.pdf')
                    ->withMime('application/pdf'),
            ];
        } catch (\Throwable) {
            return [];
        }
    }
}
