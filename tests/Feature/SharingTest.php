<?php

namespace Tests\Feature;

use App\Mail\ContractSignedMail;
use App\Models\Contract;
use App\Models\User;
use App\Services\ContractService;
use App\Services\ContractSharing;
use App\Services\ContractWorkflowService;
use App\Services\SignatureService;
use App\Services\TsaService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Tests\Support\Fakes\FakeTsa;
use Tests\TestCase;

class SharingTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('local');
        $this->app->instance(TsaService::class, new FakeTsa);
        Mail::fake();
    }

    private function makeContract(int $ownerId): Contract
    {
        return app(ContractService::class)->create([
            'contract_type' => 'bienes_muebles',
            'creator_role' => 'vendedor',
            'title' => 'Contrato para compartir',
            'object_type' => 'Mueble',
            'object_description' => 'Silla.',
            'quantity' => 1,
            'price_amount' => 300,
            'currency' => 'EUR',
            'tax_amount' => 0,
            'city' => 'Madrid',
            'signing_date' => now()->toDateString(),
        ], [
            'role' => 'vendedor', 'party_type' => 'particular', 'full_name' => 'Ana García',
            'tax_id' => '12345678Z', 'tax_id_country' => 'ES', 'country' => 'ES',
            'email' => 'ana@ejemplo.com', 'phone' => '+34 600 111 222',
            'address' => 'C/ Mayor 1', 'postal_code' => '28001', 'city' => 'Madrid',
        ], [
            'role' => 'comprador', 'party_type' => 'particular', 'full_name' => 'Luis Pérez',
            'tax_id' => '87654321X', 'tax_id_country' => 'ES', 'country' => 'ES',
            'email' => 'luis@ejemplo.com', 'phone' => '+34 600 333 444',
            'address' => 'Av. Paz 2', 'postal_code' => '28002', 'city' => 'Madrid',
        ], $ownerId);
    }

    public function test_counterparty_is_the_buyer_when_creator_is_seller(): void
    {
        $user = User::factory()->create(['email_verified_at' => now()]);
        $contract = $this->makeContract($user->id);

        $this->assertSame('comprador', $contract->counterparty()?->role);
        $this->assertSame('luis@ejemplo.com', $contract->counterparty()?->email);
    }

    public function test_draft_state_shares_review_link_for_counterparty_data_filling(): void
    {
        $user = User::factory()->create(['email_verified_at' => now()]);
        $contract = $this->makeContract($user->id);
        $token = app(SignatureService::class)->ensureToken($contract)->access_token;

        $sharing = app(ContractSharing::class);
        $this->assertSame(route('review.show', $token), $sharing->shareLink($contract));

        $mail = $sharing->mailToUrl($contract);
        $this->assertStringContainsString('mailto:luis@ejemplo.com', $mail);
        $this->assertStringContainsString(urlencode(route('review.show', $token)), $mail);

        $wa = $sharing->whatsAppUrl($contract);
        $this->assertStringContainsString('wa.me/34600333444', $wa);
        $this->assertStringContainsString(urlencode(route('review.show', $token)), $wa);
    }

    public function test_review_state_shares_review_link_via_mailto_and_whatsapp(): void
    {
        $user = User::factory()->create(['email_verified_at' => now()]);
        $contract = $this->makeContract($user->id);
        $contract = app(ContractWorkflowService::class)->transition($contract, 'en_revision', 'creator');
        $token = app(SignatureService::class)->ensureToken($contract)->access_token;

        $sharing = app(ContractSharing::class);
        $this->assertSame(route('review.show', $token), $sharing->shareLink($contract));

        $mail = $sharing->mailToUrl($contract);
        $this->assertStringContainsString('mailto:luis@ejemplo.com', $mail);
        $this->assertStringContainsString(urlencode(route('review.show', $token)), $mail);
        $this->assertStringContainsString('revise', $mail);

        $wa = $sharing->whatsAppUrl($contract);
        $this->assertStringContainsString('wa.me/34600333444', $wa);
        $this->assertStringContainsString(urlencode(route('review.show', $token)), $wa);
    }

    public function test_signing_state_shares_sign_link(): void
    {
        $user = User::factory()->create(['email_verified_at' => now()]);
        $contract = $this->makeContract($user->id);
        $workflow = app(ContractWorkflowService::class);
        $contract = $workflow->transition($contract, 'lista_para_firma', 'creator');
        $contract = $workflow->transition($contract, 'en_firma', 'creator');
        $token = app(SignatureService::class)->ensureToken($contract)->access_token;

        $sharing = app(ContractSharing::class);
        $this->assertSame(route('sign.show', $token), $sharing->shareLink($contract));
        $this->assertStringContainsString('firme', $sharing->message($contract));
    }

    public function test_whatsapp_is_generic_when_counterparty_has_no_phone(): void
    {
        $user = User::factory()->create(['email_verified_at' => now()]);
        $contract = $this->makeContract($user->id);
        $contract->buyer()->update(['phone' => null]);
        $workflow = app(ContractWorkflowService::class);
        $contract = $workflow->transition($contract, 'lista_para_firma', 'creator');
        $contract = $workflow->transition($contract, 'en_firma', 'creator');
        $token = app(SignatureService::class)->ensureToken($contract)->access_token;

        $sharing = app(ContractSharing::class);
        $this->assertSame('https://wa.me/?text='.rawurlencode($sharing->message($contract).route('sign.show', $token)), $sharing->whatsAppUrl($contract));
    }

    public function test_signed_contract_shares_download_link(): void
    {
        $user = User::factory()->create(['email_verified_at' => now()]);
        $contract = $this->makeContract($user->id);
        $workflow = app(ContractWorkflowService::class);
        $contract = $workflow->transition($contract, 'lista_para_firma', 'creator');
        $contract = $workflow->transition($contract, 'en_firma', 'creator');
        $token = app(SignatureService::class)->ensureToken($contract)->access_token;

        // Sign both parties.
        $signatures = app(SignatureService::class);
        $signatures->sign($contract, 'comprador', 'Luis Pérez', 'luis@ejemplo.com', 'fes-click', null, 'consent', ['otp_verified' => true]);
        $signatures->sign($contract, 'vendedor', 'Ana García', 'ana@ejemplo.com', 'fes-click', null, 'consent', ['otp_verified' => true]);

        $contract->refresh();
        $this->assertSame('firmado', $contract->status);

        $sharing = app(ContractSharing::class);
        $this->assertSame(route('sign.download', $token), $sharing->shareLink($contract));
    }

    public function test_both_creator_and_counterparty_receive_signed_pdf(): void
    {
        $user = User::factory()->create(['email_verified_at' => now()]);
        $contract = $this->makeContract($user->id);
        $workflow = app(ContractWorkflowService::class);
        $contract = $workflow->transition($contract, 'lista_para_firma', 'creator');
        $contract = $workflow->transition($contract, 'en_firma', 'creator');

        $signatures = app(SignatureService::class);
        $signatures->sign($contract, 'comprador', 'Luis Pérez', 'luis@ejemplo.com', 'fes-click', null, 'consent', ['otp_verified' => true]);
        $signatures->sign($contract, 'vendedor', 'Ana García', 'ana@ejemplo.com', 'fes-click', null, 'consent', ['otp_verified' => true]);

        Mail::assertQueued(ContractSignedMail::class, 2);

        Mail::assertQueued(ContractSignedMail::class, fn ($mail) => $mail->hasTo($user->email));
        Mail::assertQueued(ContractSignedMail::class, fn ($mail) => $mail->hasTo('luis@ejemplo.com'));
    }

    public function test_share_modal_renders_in_show_page(): void
    {
        $user = User::factory()->create(['email_verified_at' => now()]);
        $contract = $this->makeContract($user->id);
        $workflow = app(ContractWorkflowService::class);
        $contract = $workflow->transition($contract, 'lista_para_firma', 'creator');
        $contract = $workflow->transition($contract, 'en_firma', 'creator');
        app(SignatureService::class)->ensureToken($contract);

        $this->actingAs($user)->get(route('contracts.show', $contract))
            ->assertOk()
            ->assertSee('Compartir contrato')
            ->assertSee('WhatsApp')
            ->assertSee('Copiar enlace');
    }
}
