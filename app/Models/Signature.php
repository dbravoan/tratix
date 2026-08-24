<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Signature extends Model
{
    use HasFactory;

    public const TYPES = ['fes-canvas', 'fes-click'];

    protected $fillable = [
        'contract_id', 'contract_version_id', 'party_id', 'party_role', 'signer_name',
        'signer_email', 'signature_type', 'signature_image_path', 'signed_at',
        'ip', 'user_agent', 'latitude', 'longitude', 'consent_text',
        'otp_verified', 'otp_verification_id',
    ];

    protected $casts = [
        'signed_at' => 'datetime',
        'latitude' => 'decimal:7',
        'longitude' => 'decimal:7',
        'otp_verified' => 'boolean',
    ];

    public function contract(): BelongsTo
    {
        return $this->belongsTo(Contract::class);
    }

    public function version(): BelongsTo
    {
        return $this->belongsTo(ContractVersion::class, 'contract_version_id');
    }

    public function party(): BelongsTo
    {
        return $this->belongsTo(Party::class);
    }
}
