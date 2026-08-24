<?php

namespace Tests\Feature;

use App\Mail\ContractSignedMail;
use App\Mail\OtpMail;
use App\Mail\ReviewInviteMail;
use App\Mail\SignatureInviteMail;
use App\Models\Contract;
use App\Services\ContractService;
use App\Services\ContractWorkflowService;
use App\Services\SignatureService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class MailableTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('local');
    }

    private function makeContract(): Contract
    {
        return app(ContractService::class)->create([
            'contract_type' => 'bienes_muebles', 'title' => 'Correos', 'object_type' => 'Mueble',
            'object_description' => 'Libro.', 'quantity' => 1, 'price_amount' => 50,
            'currency' => 'EUR', 'tax_amount' => 0, 'city' => 'Madrid', 'signing_date' => now()->toDateString(),
        ], [
            'role' => 'vendedor', 'party_type' => 'particular', 'full_name' => 'Ana García',
            'tax_id' => '12345678Z', 'tax_id_country' => 'ES', 'country' => 'ES',
            'address' => 'a', 'postal_code' => '28001', 'city' => 'Madrid',
        ], [
            'role' => 'comprador', 'party_type' => 'particular', 'full_name' => 'Luis Pérez',
            'tax_id' => '87654321X', 'tax_id_country' => 'ES', 'country' => 'ES',
            'address' => 'b', 'postal_code' => '28002', 'city' => 'Madrid',
        ]);
    }

    public function test_all_notification_mailables_render(): void
    {
        $contract = $this->makeContract();
        $workflow = app(ContractWorkflowService::class);
        $contract = $workflow->transition($contract, 'lista_para_firma', 'creator');
        $contract = $workflow->transition($contract, 'en_firma', 'creator');
        $token = app(SignatureService::class)->ensureToken($contract)->access_token;

        $mailables = [
            new ReviewInviteMail($contract, route('review.show', $token), 'comprador'),
            new SignatureInviteMail($contract, route('sign.show', $token)),
            new ContractSignedMail($contract, route('contracts.show', $contract)),
            new OtpMail($contract, '123456'),
        ];

        foreach ($mailables as $mailable) {
            $mailable->render();
            $this->assertStringContainsString($contract->reference, $mailable->render());
        }

        $this->assertTrue(true);
    }
}
