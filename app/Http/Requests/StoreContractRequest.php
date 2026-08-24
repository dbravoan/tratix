<?php

namespace App\Http\Requests;

use App\Models\Contract;
use App\Models\Party;
use App\Services\CountryLegalConfig;
use App\Services\EuVatValidator;
use App\Services\LatinAmericanTaxIdValidator;
use App\Services\SpanishTaxIdValidator;
use Illuminate\Foundation\Http\FormRequest;

class StoreContractRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $creatorRoleInput = $this->input('creator_role', 'vendedor');
        $creatorRole = $creatorRoleInput === 'vendedor' ? 'seller' : 'buyer';
        $counterpartyRole = $creatorRole === 'seller' ? 'buyer' : 'seller';
        $delegated = $this->boolean('invite_counterparty_to_fill') || empty($this->input("{$counterpartyRole}.tax_id"));

        $rules = [
            'contract_type' => ['required', 'string', 'in:'.implode(',', Contract::TYPES)],
            'title' => ['required', 'string', 'max:255'],
            'object_type' => ['nullable', 'string', 'max:255'],
            'object_description' => ['required', 'string'],
            'quantity' => ['nullable', 'integer', 'min:1'],
            'price_amount' => ['required', 'numeric', 'min:0.01'],
            'currency' => ['required', 'string', 'size:3'],
            'tax_amount' => ['nullable', 'numeric', 'min:0'],
            'city' => ['required', 'string', 'max:255'],
            'signing_date' => ['required', 'date'],
            'effective_date' => ['nullable', 'date'],
            'delivery_terms' => ['nullable', 'string'],
            'payment_terms' => ['nullable', 'string'],
            'warranties' => ['nullable', 'string'],
            'special_clauses' => ['nullable', 'string'],
            'status' => ['sometimes', 'string', 'in:'.implode(',', Contract::STATUSES)],
            'creator_role' => ['required', 'string', 'in:vendedor,comprador'],
            'invite_counterparty_to_fill' => ['nullable', 'boolean'],
        ];

        // Creator rules (must be provided)
        $rules["{$creatorRole}.party_type"] = ['required', 'string', 'in:'.implode(',', Party::TYPES)];
        $rules["{$creatorRole}.full_name"] = ["required_if:{$creatorRole}.party_type,particular", 'nullable', 'string', 'max:255'];
        $rules["{$creatorRole}.company_name"] = ["required_if:{$creatorRole}.party_type,autonomo", "required_if:{$creatorRole}.party_type,sociedad", 'nullable', 'string', 'max:255'];
        $rules["{$creatorRole}.tax_id"] = ['required', 'string', 'max:32'];
        $rules["{$creatorRole}.tax_id_country"] = ['required', 'string', 'size:2', 'alpha'];
        $rules["{$creatorRole}.country"] = ['required', 'string', 'size:2', 'alpha'];
        $rules["{$creatorRole}.address"] = ['required', 'string', 'max:255'];
        $rules["{$creatorRole}.postal_code"] = ['required', 'string', 'max:16'];
        $rules["{$creatorRole}.city"] = ['required', 'string', 'max:255'];
        $rules["{$creatorRole}.province"] = ['nullable', 'string', 'max:255'];
        $rules["{$creatorRole}.email"] = ['nullable', 'email'];
        $rules["{$creatorRole}.phone"] = ['nullable', 'string', 'max:30'];
        $rules["{$creatorRole}.id_card_token"] = ['nullable', 'string', 'max:100'];
        $rules["{$creatorRole}.id_card_front_token"] = ['nullable', 'string', 'max:100'];
        $rules["{$creatorRole}.id_card_back_token"] = ['nullable', 'string', 'max:100'];

        // Counterparty rules (flexible if creator delegates to counterparty)
        if ($delegated) {
            $rules["{$counterpartyRole}.party_type"] = ['nullable', 'string', 'in:'.implode(',', Party::TYPES)];
            $rules["{$counterpartyRole}.full_name"] = ['nullable', 'string', 'max:255'];
            $rules["{$counterpartyRole}.company_name"] = ['nullable', 'string', 'max:255'];
            $rules["{$counterpartyRole}.tax_id"] = ['nullable', 'string', 'max:32'];
            $rules["{$counterpartyRole}.tax_id_country"] = ['nullable', 'string', 'size:2', 'alpha'];
            $rules["{$counterpartyRole}.country"] = ['nullable', 'string', 'size:2', 'alpha'];
            $rules["{$counterpartyRole}.address"] = ['nullable', 'string', 'max:255'];
            $rules["{$counterpartyRole}.postal_code"] = ['nullable', 'string', 'max:16'];
            $rules["{$counterpartyRole}.city"] = ['nullable', 'string', 'max:255'];
            $rules["{$counterpartyRole}.province"] = ['nullable', 'string', 'max:255'];
            $rules["{$counterpartyRole}.email"] = ['nullable', 'email'];
            $rules["{$counterpartyRole}.phone"] = ['nullable', 'string', 'max:30'];
            $rules["{$counterpartyRole}.id_card_token"] = ['nullable', 'string', 'max:100'];
            $rules["{$counterpartyRole}.id_card_front_token"] = ['nullable', 'string', 'max:100'];
            $rules["{$counterpartyRole}.id_card_back_token"] = ['nullable', 'string', 'max:100'];
        } else {
            $rules["{$counterpartyRole}.party_type"] = ['required', 'string', 'in:'.implode(',', Party::TYPES)];
            $rules["{$counterpartyRole}.full_name"] = ["required_if:{$counterpartyRole}.party_type,particular", 'nullable', 'string', 'max:255'];
            $rules["{$counterpartyRole}.company_name"] = ["required_if:{$counterpartyRole}.party_type,autonomo", "required_if:{$counterpartyRole}.party_type,sociedad", 'nullable', 'string', 'max:255'];
            $rules["{$counterpartyRole}.tax_id"] = ['required', 'string', 'max:32'];
            $rules["{$counterpartyRole}.tax_id_country"] = ['required', 'string', 'size:2', 'alpha'];
            $rules["{$counterpartyRole}.country"] = ['required', 'string', 'size:2', 'alpha'];
            $rules["{$counterpartyRole}.address"] = ['required', 'string', 'max:255'];
            $rules["{$counterpartyRole}.postal_code"] = ['required', 'string', 'max:16'];
            $rules["{$counterpartyRole}.city"] = ['required', 'string', 'max:255'];
            $rules["{$counterpartyRole}.province"] = ['nullable', 'string', 'max:255'];
            $rules["{$counterpartyRole}.email"] = ['nullable', 'email'];
            $rules["{$counterpartyRole}.phone"] = ['nullable', 'string', 'max:30'];
            $rules["{$counterpartyRole}.id_card_token"] = ['nullable', 'string', 'max:100'];
            $rules["{$counterpartyRole}.id_card_front_token"] = ['nullable', 'string', 'max:100'];
            $rules["{$counterpartyRole}.id_card_back_token"] = ['nullable', 'string', 'max:100'];
        }

        return $rules;
    }

    public function after(): array
    {
        return [
            fn () => $this->validateTaxId('seller'),
            fn () => $this->validateTaxId('buyer'),
        ];
    }

    private function validateTaxId(string $role): \Closure
    {
        return function () use ($role) {
            $taxId = trim((string) $this->input("{$role}.tax_id"));

            // If empty or provisional placeholder, skip validation
            if ($taxId === '' || $taxId === 'PENDIENTE') {
                return;
            }

            $country = strtoupper((string) ($this->input("{$role}.tax_id_country") ?? 'ES'));

            if ($country === 'ES') {
                $validator = app(SpanishTaxIdValidator::class);
                if (! $validator->isValid($taxId)) {
                    $this->errors()->add("{$role}.tax_id", 'El NIF/CIF/NIE no es válido.');
                }

                return;
            }

            $vat = app(EuVatValidator::class);
            if ($vat->isValidCountry($country)) {
                if (! $vat->hasValidFormat($country, $taxId)) {
                    $this->errors()->add("{$role}.tax_id", "El número de IVA no tiene un formato válido para {$country}.");
                }

                return;
            }

            $countryConfig = app(CountryLegalConfig::class);
            if ($countryConfig->isSupported($country)) {
                if (! app(LatinAmericanTaxIdValidator::class)->isValid($country, $taxId)) {
                    $this->errors()->add("{$role}.tax_id", "El documento fiscal no tiene un formato válido para {$country}.");
                }

                return;
            }

            $this->errors()->add("{$role}.tax_id_country", "El país {$country} no está soportado. Soportados: ES, AR, MX, CO, CL, PE, UY.");
        };
    }
}
