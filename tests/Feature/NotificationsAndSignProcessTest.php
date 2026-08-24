<?php

namespace Tests\Feature;

use App\Mail\ContractCommentMail;
use App\Mail\OtpMail;
use App\Mail\ProposalCreatedMail;
use App\Mail\ProposalResolvedMail;
use App\Mail\ReviewAcceptedMail;
use App\Models\Contract;
use App\Models\Party;
use App\Models\User;
use App\Services\ContractWorkflowService;
use App\Services\NegotiationService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class NotificationsAndSignProcessTest extends TestCase
{
    use RefreshDatabase;

    private User $creator;

    private Contract $contract;

    private Party $seller;

    private Party $buyer;

    protected function setUp(): void
    {
        parent::setUp();
        Mail::fake();

        $this->creator = User::factory()->create(['email' => 'creador@example.com']);

        $this->contract = Contract::create([
            'user_id' => $this->creator->id,
            'reference' => 'CTR-2026-TEST',
            'contract_type' => 'vehiculos',
            'transaction_type' => 'c2c',
            'jurisdiction' => 'nacional',
            'applicable_law' => 'ES',
            'title' => 'Compraventa de Seat Ibiza',
            'object_type' => 'Turismo',
            'object_description' => 'Seat Ibiza 1.6 TDI Matricula 1234-XYZ',
            'quantity' => 1,
            'price_amount' => 8000,
            'currency' => 'EUR',
            'tax_amount' => 0,
            'total_amount' => 8000,
            'city' => 'Madrid',
            'signing_date' => now()->toDateString(),
            'status' => 'en_revision',
            'creator_role' => 'vendedor',
            'access_token' => 'test-token-sign-123',
            'access_token_expires_at' => now()->addDays(7),
            'clauses' => [
                ['key' => 'precio_pago', 'title' => 'Precio y pago', 'body' => 'El precio total es de 8.000 €.'],
                ['key' => 'entrega', 'title' => 'Entrega', 'body' => 'El vehículo se entrega en Madrid.'],
            ],
        ]);

        $this->seller = Party::create([
            'contract_id' => $this->contract->id,
            'role' => 'vendedor',
            'party_type' => 'particular',
            'full_name' => 'Ana Vendedora',
            'tax_id' => '12345678Z',
            'tax_id_country' => 'ES',
            'country' => 'ES',
            'email' => 'ana.vendedora@example.com',
            'address' => 'Calle Mayor 1',
            'postal_code' => '28013',
            'city' => 'Madrid',
        ]);

        $this->buyer = Party::create([
            'contract_id' => $this->contract->id,
            'role' => 'comprador',
            'party_type' => 'particular',
            'full_name' => 'Carlos Comprador',
            'tax_id' => '87654321A',
            'tax_id_country' => 'ES',
            'country' => 'ES',
            'email' => 'carlos.comprador@example.com',
            'address' => 'Calle Alcala 20',
            'postal_code' => '28014',
            'city' => 'Madrid',
        ]);
    }

    public function test_comment_by_counterparty_triggers_email_to_creator(): void
    {
        $res = $this->post(route('review.comments.store', $this->contract->access_token), [
            'author_name' => 'Carlos Comprador',
            'author_role' => 'comprador',
            'content' => '¿Podemos acordar entregar dos llaves?',
            'clause_key' => 'entrega',
        ]);

        $res->assertRedirect();

        Mail::assertQueued(ContractCommentMail::class, function ($mail) {
            return $mail->hasTo('creador@example.com');
        });
    }

    public function test_comment_by_creator_triggers_email_to_counterparty(): void
    {
        $res = $this->actingAs($this->creator)->post(route('contracts.comments.store', $this->contract), [
            'content' => 'Sí, entrego las dos llaves originales.',
            'clause_key' => 'entrega',
            'clause_title' => 'Entrega',
        ]);

        $res->assertRedirect();

        Mail::assertQueued(ContractCommentMail::class, function ($mail) {
            return $mail->hasTo('carlos.comprador@example.com');
        });
    }

    public function test_counterparty_accept_triggers_review_accepted_email_to_creator(): void
    {
        $res = $this->post(route('review.accept', $this->contract->access_token), [
            'role' => 'comprador',
            'acceptor_name' => 'Carlos Comprador',
        ]);

        $res->assertRedirect();

        Mail::assertQueued(ReviewAcceptedMail::class, function ($mail) {
            return $mail->hasTo('creador@example.com');
        });
    }

    public function test_counterparty_propose_triggers_proposal_created_email(): void
    {
        $res = $this->post(route('review.propose', $this->contract->access_token), [
            'role' => 'comprador',
            'clause_key' => 'precio_pago',
            'proposed_text' => 'El precio total es de 7.800 € en dos plazos.',
            'reason' => 'Ajuste acordado por neumáticos.',
        ]);

        $res->assertRedirect();

        Mail::assertQueued(ProposalCreatedMail::class, function ($mail) {
            return $mail->hasTo('creador@example.com');
        });
    }

    public function test_creator_approving_proposal_triggers_proposal_resolved_email(): void
    {
        $proposal = app(NegotiationService::class)->propose(
            $this->contract,
            'precio_pago',
            'El precio total es de 7.800 €.',
            'comprador'
        );

        $res = $this->actingAs($this->creator)->post(
            route('contracts.proposals.approve', ['contract' => $this->contract, 'proposal' => $proposal])
        );

        $res->assertRedirect();

        Mail::assertQueued(ProposalResolvedMail::class, function ($mail) {
            return $mail->hasTo('carlos.comprador@example.com');
        });
    }

    public function test_signing_page_auto_identifies_the_signer_without_asking(): void
    {
        $workflow = app(ContractWorkflowService::class);
        $this->contract = $workflow->transition($this->contract, 'lista_para_firma', 'creator', $this->creator);
        $this->contract = $workflow->transition($this->contract, 'en_firma', 'creator', $this->creator);

        // Buyer opens signing link with role=comprador
        $res = $this->get(route('sign.show', ['token' => $this->contract->access_token, 'role' => 'comprador']));
        $res->assertOk();

        // Check that identity is pre-recognized and pre-filled
        $res->assertSee('Identidad del Firmante Reconocida');
        $res->assertSee('Firmando como COMPRADOR:');
        $res->assertSee('Carlos Comprador');
        $res->assertSee('carlos.comprador@example.com');
        $res->assertSee('87654321A');
    }

    public function test_sending_otp_code_dispatches_otp_mail(): void
    {
        $workflow = app(ContractWorkflowService::class);
        $this->contract = $workflow->transition($this->contract, 'lista_para_firma', 'creator', $this->creator);
        $this->contract = $workflow->transition($this->contract, 'en_firma', 'creator', $this->creator);

        $res = $this->post(route('sign.otp', $this->contract->access_token), [
            'role' => 'comprador',
            'signer_email' => 'carlos.comprador@example.com',
        ]);

        $res->assertRedirect();

        Mail::assertQueued(OtpMail::class, function ($mail) {
            return $mail->hasTo('carlos.comprador@example.com');
        });
    }
}
