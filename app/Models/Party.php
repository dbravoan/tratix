<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Party extends Model
{
    use HasFactory;

    public const ROLES = ['vendedor', 'comprador'];

    public const TYPES = ['particular', 'autonomo', 'sociedad'];

    protected $fillable = [
        'contract_id', 'role', 'party_type', 'full_name', 'company_name', 'tax_id',
        'tax_id_country', 'country', 'address', 'postal_code', 'city', 'province',
        'email', 'phone', 'activity', 'representative_name', 'representative_tax_id',
        'eori', 'registered_vat', 'acting_in_own_name', 'signature_city', 'signature_date',
    ];

    protected $casts = [
        'registered_vat' => 'boolean',
        'acting_in_own_name' => 'boolean',
        'signature_date' => 'date',
    ];

    public function contract(): BelongsTo
    {
        return $this->belongsTo(Contract::class);
    }

    public function isProfessional(): bool
    {
        return in_array($this->party_type, ['autonomo', 'sociedad'], true);
    }

    public function isParticular(): bool
    {
        return $this->party_type === 'particular';
    }

    public function displayName(): string
    {
        return $this->party_type === 'particular'
            ? $this->full_name
            : $this->company_name;
    }
}
