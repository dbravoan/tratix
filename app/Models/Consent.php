<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Consent extends Model
{
    protected $fillable = [
        'contract_id', 'signer_email', 'role', 'consent_type',
        'policy_version', 'accepted_at', 'ip', 'user_agent',
    ];

    protected $casts = [
        'accepted_at' => 'datetime',
    ];

    public function contract(): BelongsTo
    {
        return $this->belongsTo(Contract::class);
    }
}
