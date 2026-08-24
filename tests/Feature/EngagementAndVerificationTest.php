<?php

namespace Tests\Feature;

use App\Mail\ReminderMail;
use App\Models\Contract;
use App\Models\User;
use App\Services\ContractService;
use App\Services\ContractWorkflowService;
use App\Services\SignatureService;
use App\Services\TsaService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Tests\Support\Fakes\FakeTsa;
use Tests\TestCase;

class EngagementAndVerificationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('local');
        $this->app->instance(TsaService::class, new FakeTsa);
        Mail::fake();
    }

    private function makeSignedContract(): Contract
    {
        $user = User::factory()->create(['email_verified_at' => now()]);
        $contract = app(ContractService::class)->create([
            'contract_type' => 'bienes_muebles', 'creator_role' => 'vendedor',
            'title' => 'Verificar', 'object_type' => 'Mueble', 'object_description' => 'Silla.',
            'quantity' => 1, 'price_amount' => 300, 'currency' => 'EUR', 'tax_amount' => 0,
            'city' => 'Madrid', 'signing_date' => now()->toDateString(),
        ], [
            'role' => 'vendedor', 'party_type' => 'particular', 'full_name' => 'Ana', 'tax_id' => '12345678Z',
            'tax_id_country' => 'ES', 'country' => 'ES', 'address' => 'a', 'postal_code' => '28001', 'city' => 'Madrid',
        ], [
            'role' => 'comprador', 'party_type' => 'particular', 'full_name' => 'Luis', 'tax_id' => '87654321X',
            'tax_id_country' => 'ES', 'country' => 'ES', 'address' => 'b', 'postal_code' => '28002', 'city' => 'Madrid',
        ], $user->id);

        $workflow = app(ContractWorkflowService::class);
        $contract = $workflow->transition($contract, 'lista_para_firma', 'creator');
        $contract = $workflow->transition($contract, 'en_firma', 'creator');

        $sigs = app(SignatureService::class);
        $sigs->sign($contract, 'comprador', 'Luis', 'luis@ejemplo.com', 'fes-click', null, 'consent', ['otp_verified' => true]);
        $sigs->sign($contract, 'vendedor', 'Ana', 'ana@ejemplo.com', 'fes-click', null, 'consent', ['otp_verified' => true]);

        return $contract->fresh();
    }

    public function test_public_verification_page_confirms_valid_integrity(): void
    {
        $contract = $this->makeSignedContract();

        $this->get(route('verify.public', $contract->reference))
            ->assertOk()
            ->assertSee('Integridad verificada')
            ->assertSee($contract->reference);
    }

    public function test_public_verification_page_unknown_reference(): void
    {
        $this->get(route('verify.public', 'C-0000-9999'))->assertOk()->assertSee('no encontrado');
    }

    public function test_counterparty_can_download_signed_pdf_publicly(): void
    {
        $contract = $this->makeSignedContract();
        $token = app(SignatureService::class)->ensureToken($contract)->access_token;

        $this->get(route('sign.download', $token))->assertOk();
    }

    public function test_review_download_returns_pdf(): void
    {
        $user = User::factory()->create(['email_verified_at' => now()]);
        $contract = app(ContractService::class)->create([
            'contract_type' => 'bienes_muebles', 'creator_role' => 'vendedor',
            'title' => 'Rev', 'object_type' => 'Mueble', 'object_description' => 'X.',
            'quantity' => 1, 'price_amount' => 100, 'currency' => 'EUR', 'tax_amount' => 0,
            'city' => 'Madrid', 'signing_date' => now()->toDateString(),
        ], [
            'role' => 'vendedor', 'party_type' => 'particular', 'full_name' => 'A', 'tax_id' => '12345678Z',
            'tax_id_country' => 'ES', 'country' => 'ES', 'address' => 'a', 'postal_code' => '28001', 'city' => 'Madrid',
        ], [
            'role' => 'comprador', 'party_type' => 'particular', 'full_name' => 'B', 'tax_id' => '87654321X',
            'tax_id_country' => 'ES', 'country' => 'ES', 'address' => 'b', 'postal_code' => '28002', 'city' => 'Madrid',
        ], $user->id);
        app(ContractWorkflowService::class)->transition($contract, 'en_revision', 'creator');
        $token = app(SignatureService::class)->ensureToken($contract)->access_token;

        $this->get(route('review.download', $token))->assertOk();
    }

    public function test_expired_token_is_rejected_on_sign_page(): void
    {
        $user = User::factory()->create(['email_verified_at' => now()]);
        $contract = app(ContractService::class)->create([
            'contract_type' => 'bienes_muebles', 'creator_role' => 'vendedor',
            'title' => 'Exp', 'object_type' => 'Mueble', 'object_description' => 'X.',
            'quantity' => 1, 'price_amount' => 100, 'currency' => 'EUR', 'tax_amount' => 0,
            'city' => 'Madrid', 'signing_date' => now()->toDateString(),
        ], [
            'role' => 'vendedor', 'party_type' => 'particular', 'full_name' => 'A', 'tax_id' => '12345678Z',
            'tax_id_country' => 'ES', 'country' => 'ES', 'address' => 'a', 'postal_code' => '28001', 'city' => 'Madrid',
        ], [
            'role' => 'comprador', 'party_type' => 'particular', 'full_name' => 'B', 'tax_id' => '87654321X',
            'tax_id_country' => 'ES', 'country' => 'ES', 'address' => 'b', 'postal_code' => '28002', 'city' => 'Madrid',
        ], $user->id);
        $workflow = app(ContractWorkflowService::class);
        $contract = $workflow->transition($contract, 'lista_para_firma', 'creator');
        $contract = $workflow->transition($contract, 'en_firma', 'creator');
        $contract = app(SignatureService::class)->ensureToken($contract);
        $contract->update(['access_token_expires_at' => now()->subHour()]);
        $token = $contract->access_token;

        $this->get(route('sign.show', $token))->assertForbidden();
    }

    public function test_reminder_command_sends_pending_signature_emails(): void
    {
        $user = User::factory()->create(['email_verified_at' => now()]);
        $contract = app(ContractService::class)->create([
            'contract_type' => 'bienes_muebles', 'creator_role' => 'vendedor',
            'title' => 'Rec', 'object_type' => 'Mueble', 'object_description' => 'X.',
            'quantity' => 1, 'price_amount' => 100, 'currency' => 'EUR', 'tax_amount' => 0,
            'city' => 'Madrid', 'signing_date' => now()->toDateString(),
        ], [
            'role' => 'vendedor', 'party_type' => 'particular', 'full_name' => 'A', 'tax_id' => '12345678Z',
            'tax_id_country' => 'ES', 'country' => 'ES', 'address' => 'a', 'postal_code' => '28001', 'city' => 'Madrid',
        ], [
            'role' => 'comprador', 'party_type' => 'particular', 'full_name' => 'B', 'tax_id' => '87654321X',
            'tax_id_country' => 'ES', 'country' => 'ES', 'address' => 'b', 'postal_code' => '28002', 'city' => 'Madrid',
        ], $user->id);
        $workflow = app(ContractWorkflowService::class);
        $contract = $workflow->transition($contract, 'lista_para_firma', 'creator');
        $workflow->transition($contract, 'en_firma', 'creator');
        \DB::table('contracts')->where('id', $contract->id)->update(['created_at' => now()->subDays(3)]);

        $this->artisan('contracts:reminders')->assertExitCode(0);

        Mail::assertQueued(ReminderMail::class, 1);
    }
}
