<?php

namespace Tests\Feature;

use App\Models\Contract;
use App\Models\Party;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ContractEditAndCommentsTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    private Contract $contract;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();

        $this->contract = Contract::create([
            'user_id' => $this->user->id,
            'reference' => 'C-2026-0001',
            'contract_type' => 'arras',
            'transaction_type' => 'c2c',
            'jurisdiction' => 'nacional',
            'applicable_law' => 'ES',
            'title' => 'Contrato de arras original',
            'object_type' => 'Vivienda',
            'object_description' => 'Piso en Calle Mayor 10, Madrid',
            'quantity' => 1,
            'price_amount' => 150000,
            'currency' => 'EUR',
            'tax_amount' => 0,
            'total_amount' => 150000,
            'city' => 'Madrid',
            'signing_date' => now()->toDateString(),
            'status' => 'borrador',
            'creator_role' => 'vendedor',
            'access_token' => 'test-token-12345',
        ]);

        Party::create([
            'contract_id' => $this->contract->id,
            'role' => 'vendedor',
            'party_type' => 'particular',
            'full_name' => 'Vendedor Original',
            'tax_id' => '12345678Z',
            'tax_id_country' => 'ES',
            'country' => 'ES',
            'address' => 'Calle Mayor 10',
            'postal_code' => '28013',
            'city' => 'Madrid',
        ]);

        Party::create([
            'contract_id' => $this->contract->id,
            'role' => 'comprador',
            'party_type' => 'particular',
            'full_name' => 'Comprador Original',
            'tax_id' => '71234567A',
            'tax_id_country' => 'ES',
            'country' => 'ES',
            'address' => 'Calle Sol 1',
            'postal_code' => '28013',
            'city' => 'Madrid',
        ]);
    }

    public function test_can_view_edit_contract_walkthrough(): void
    {
        $res = $this->actingAs($this->user)->get(route('contracts.edit', $this->contract));
        $res->assertOk();
        $res->assertSee('Contrato de arras original');
        $res->assertSee('Vendedor Original');
        $res->assertSee('Comprador Original');
    }

    public function test_can_update_contract_fields_and_parties(): void
    {
        $updateData = [
            'contract_type' => 'arras',
            'title' => 'Contrato de arras modificado',
            'object_type' => 'Chalet adosado',
            'object_description' => 'Chalet en Calle Nueva 20, Pozuelo',
            'quantity' => 1,
            'price_amount' => 280000,
            'currency' => 'EUR',
            'tax_amount' => 0,
            'city' => 'Pozuelo de Alarcón',
            'signing_date' => now()->addDays(2)->toDateString(),
            'creator_role' => 'vendedor',
            'payment_terms' => 'Arras de 28.000 € y resto a la firma.',
            'seller' => [
                'party_type' => 'particular',
                'full_name' => 'Vendedor Actualizado',
                'tax_id' => '12345678Z',
                'tax_id_country' => 'ES',
                'country' => 'ES',
                'address' => 'Calle Nueva 20',
                'postal_code' => '28223',
                'city' => 'Pozuelo',
            ],
            'buyer' => [
                'party_type' => 'particular',
                'full_name' => 'Comprador Actualizado',
                'tax_id' => '71234567A',
                'tax_id_country' => 'ES',
                'country' => 'ES',
                'address' => 'Avenida Europa 5',
                'postal_code' => '28224',
                'city' => 'Pozuelo',
            ],
        ];

        $res = $this->actingAs($this->user)->put(route('contracts.update', $this->contract), $updateData);
        $res->assertRedirect(route('contracts.show', $this->contract));

        $fresh = $this->contract->fresh();
        $this->assertEquals('Contrato de arras modificado', $fresh->title);
        $this->assertEquals(280000, $fresh->price_amount);
        $this->assertEquals('Chalet en Calle Nueva 20, Pozuelo', $fresh->object_description);
        $this->assertEquals('Vendedor Actualizado', $fresh->seller()->full_name);
        $this->assertEquals('Comprador Actualizado', $fresh->buyer()->full_name);

        $this->assertDatabaseHas('audit_trail', [
            'contract_id' => $this->contract->id,
            'event' => 'contract_updated',
        ]);
    }

    public function test_creator_can_post_comment_on_clause(): void
    {
        $res = $this->actingAs($this->user)->post(route('contracts.comments.store', $this->contract), [
            'content' => '¿Podemos pactar entrega de llaves el 15 de diciembre?',
            'clause_key' => 'entrega_posesion',
            'clause_title' => 'Plazo y entrega',
        ]);

        $res->assertRedirect();

        $this->assertDatabaseHas('contract_comments', [
            'contract_id' => $this->contract->id,
            'clause_key' => 'entrega_posesion',
            'content' => '¿Podemos pactar entrega de llaves el 15 de diciembre?',
        ]);

        $this->assertDatabaseHas('audit_trail', [
            'contract_id' => $this->contract->id,
            'event' => 'comment_added',
        ]);
    }

    public function test_counterparty_can_post_comment_via_public_review(): void
    {
        $res = $this->post(route('review.comments.store', $this->contract->access_token), [
            'author_name' => 'Comprador Carlos',
            'author_role' => 'comprador',
            'content' => 'De acuerdo con el precio, pero solicito ampliar el plazo notarial a 90 días.',
            'clause_key' => 'arras_penitenciales',
        ]);

        $res->assertRedirect();

        $this->assertDatabaseHas('contract_comments', [
            'contract_id' => $this->contract->id,
            'author_name' => 'Comprador Carlos',
            'author_role' => 'comprador',
            'content' => 'De acuerdo con el precio, pero solicito ampliar el plazo notarial a 90 días.',
        ]);
    }
}
