<?php

namespace Tests\Feature;

use App\Mail\ContractCancelledMail;
use App\Mail\ContractCommentMail;
use App\Mail\ContractSignedMail;
use App\Mail\MonthlySummaryMail;
use App\Mail\OtpMail;
use App\Mail\PartySignedMail;
use App\Mail\ProposalCreatedMail;
use App\Mail\ProposalResolvedMail;
use App\Mail\ReminderMail;
use App\Mail\ReviewAcceptedMail;
use App\Mail\ReviewInviteMail;
use App\Mail\SignatureInviteMail;
use App\Models\ClauseProposal;
use App\Models\Contract;
use App\Models\ContractComment;
use App\Models\User;
use App\Services\ContractService;
use App\Services\ContractWorkflowService;
use App\Services\SignatureService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ComprehensiveMailSystemTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    private Contract $contract;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('local');

        $this->user = User::factory()->create([
            'name' => 'Ana Creadora',
            'email' => 'ana@example.com',
            'notify_comments' => true,
            'notify_proposals' => true,
            'notify_signatures' => true,
            'notify_summary' => true,
        ]);

        $this->contract = app(ContractService::class)->create([
            'contract_type' => 'bienes_muebles',
            'title' => 'Venta de Muebles Antiguos',
            'object_type' => 'Mueble',
            'object_description' => 'Mesa de comedor de caoba maciza.',
            'quantity' => 1,
            'price_amount' => 1200,
            'currency' => 'EUR',
            'tax_amount' => 0,
            'city' => 'Madrid',
            'signing_date' => now()->toDateString(),
            'creator_role' => 'vendedor',
        ], [
            'role' => 'vendedor',
            'party_type' => 'particular',
            'full_name' => 'Ana Creadora',
            'email' => 'ana@example.com',
            'tax_id' => '12345678Z',
            'tax_id_country' => 'ES',
            'country' => 'ES',
            'address' => 'Calle Mayor 1',
            'postal_code' => '28001',
            'city' => 'Madrid',
        ], [
            'role' => 'comprador',
            'party_type' => 'particular',
            'full_name' => 'Carlos Comprador',
            'email' => 'carlos@example.com',
            'tax_id' => '87654321X',
            'tax_id_country' => 'ES',
            'country' => 'ES',
            'address' => 'Gran Vía 10',
            'postal_code' => '28013',
            'city' => 'Madrid',
        ], $this->user->id);
    }

    public function test_all_12_mailables_render_cleanly_with_markdown_layout(): void
    {
        $token = app(SignatureService::class)->ensureToken($this->contract)->access_token;

        $comment = ContractComment::create([
            'contract_id' => $this->contract->id,
            'author_name' => 'Carlos Comprador',
            'author_role' => 'comprador',
            'clause_key' => 'objeto',
            'clause_title' => 'Objeto del Contrato',
            'content' => '¿Incluye las 6 sillas a juego?',
        ]);

        $proposal = ClauseProposal::create([
            'contract_id' => $this->contract->id,
            'clause_key' => 'precio_pago',
            'clause_title' => 'Precio y Forma de Pago',
            'original_text' => 'El pago se realizará al contado.',
            'proposed_text' => 'El pago se realizará mediante transferencia bancaria.',
            'reason' => 'Mayor seguridad.',
            'status' => 'pending',
            'proposed_by' => 'Carlos Comprador',
            'proposed_by_role' => 'comprador',
        ]);

        $allMailables = [
            'ContractCommentMail' => new ContractCommentMail($this->contract, $comment, route('contracts.show', $this->contract)),
            'ProposalCreatedMail' => new ProposalCreatedMail($this->contract, $proposal, route('contracts.show', $this->contract)),
            'ProposalResolvedApproved' => new ProposalResolvedMail($this->contract, $proposal, route('review.show', $token)),
            'ReviewAcceptedMail' => new ReviewAcceptedMail($this->contract, 'Carlos Comprador', 'comprador', route('contracts.show', $this->contract)),
            'ReviewInviteMail' => new ReviewInviteMail($this->contract, route('review.show', $token), 'comprador'),
            'SignatureInviteMail' => new SignatureInviteMail($this->contract, route('sign.show', ['token' => $token, 'role' => 'comprador'])),
            'OtpMail' => new OtpMail($this->contract, '654321'),
            'PartySignedMailPending' => new PartySignedMail($this->contract, 'Ana Creadora', 'vendedor', route('sign.show', ['token' => $token, 'role' => 'comprador']), true),
            'PartySignedMailCreator' => new PartySignedMail($this->contract, 'Carlos Comprador', 'comprador', route('contracts.show', $this->contract), false),
            'ContractSignedMail' => new ContractSignedMail($this->contract, route('sign.download', $token)),
            'ContractCancelledMail' => new ContractCancelledMail($this->contract, 'Acuerdo modificado'),
            'ReminderMail' => new ReminderMail($this->contract, route('contracts.show', $this->contract), 'Recordatorio de firma pendiente.'),
            'MonthlySummaryMail' => new MonthlySummaryMail($this->user, 5, 3, 2),
        ];

        foreach ($allMailables as $name => $mailable) {
            $rendered = $mailable->render();
            $this->assertNotEmpty($rendered, "Failed rendering mailable: {$name}");
            $this->assertStringContainsString('Tratix', $rendered, "Tratix branding missing in: {$name}");
        }
    }

    public function test_contract_cancellation_notifies_invited_counterparty(): void
    {
        Mail::fake();

        $this->contract->update(['invited_email' => 'carlos@example.com']);

        $this->actingAs($this->user)
            ->post(route('contracts.cancel', $this->contract))
            ->assertRedirect();

        Mail::assertQueued(ContractCancelledMail::class, function ($mail) {
            return $mail->hasTo('carlos@example.com');
        });
    }

    public function test_user_notification_preferences_suppress_emails_when_disabled(): void
    {
        Mail::fake();

        // User disabled comments notifications
        $this->user->update(['notify_comments' => false]);
        $token = app(SignatureService::class)->ensureToken($this->contract)->access_token;

        $this->post(route('review.comments.store', $token), [
            'author_name' => 'Carlos Comprador',
            'author_role' => 'comprador',
            'content' => 'Un comentario cuando el usuario no quiere emails.',
        ])->assertRedirect();

        // Should NOT have sent email to creator
        Mail::assertNothingQueued();
    }

    public function test_partial_signature_dispatches_party_signed_mail_to_pending_party(): void
    {
        Mail::fake();

        $workflow = app(ContractWorkflowService::class);
        $this->contract = $workflow->transition($this->contract, 'lista_para_firma', 'creator');
        $this->contract = $workflow->transition($this->contract, 'en_firma', 'creator');
        $token = app(SignatureService::class)->ensureToken($this->contract)->access_token;

        Cache::put('sign_otp:'.$token.':ana@example.com', '123456');

        // Seller signs first
        $this->post(route('sign.store', $token), [
            'role' => 'vendedor',
            'signer_name' => 'Ana Creadora',
            'signer_email' => 'ana@example.com',
            'signature_type' => 'fes-click',
            'consent' => '1',
            'otp_code' => '123456',
        ])->assertRedirect();

        // PartySignedMail should be queued for buyer (carlos@example.com)
        // because buyer has not yet signed.
        Mail::assertQueued(PartySignedMail::class, function ($mail) {
            return $mail->hasTo('carlos@example.com');
        });
    }

    public function test_contract_signed_mail_includes_pdf_attachment_from_storage_and_fallback(): void
    {
        $token = app(SignatureService::class)->ensureToken($this->contract)->access_token;

        // 1. Stored PDF on disk
        $path = 'contracts/'.$this->contract->reference.'/final-v1.pdf';
        Storage::disk('local')->put($path, '%PDF-1.4 test sealed pdf bytes');
        $this->contract->update(['final_pdf_path' => $path]);

        $mailable = new ContractSignedMail($this->contract, route('sign.download', $token));
        $attachments = $mailable->attachments();

        $this->assertCount(1, $attachments);

        // 2. Fallback when final_pdf_path is not yet on disk
        $this->contract->update(['final_pdf_path' => null]);
        $mailableFallback = new ContractSignedMail($this->contract, route('sign.download', $token));
        $fallbackAttachments = $mailableFallback->attachments();

        $this->assertCount(1, $fallbackAttachments);
    }
}
