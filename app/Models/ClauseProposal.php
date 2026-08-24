<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ClauseProposal extends Model
{
    use HasFactory;

    public const STATUSES = ['pending', 'approved', 'rejected'];

    protected $fillable = [
        'contract_id', 'contract_version_id', 'clause_key', 'clause_title',
        'original_text', 'proposed_text', 'proposed_by', 'reason', 'status',
    ];

    public function contract(): BelongsTo
    {
        return $this->belongsTo(Contract::class);
    }

    public function version(): BelongsTo
    {
        return $this->belongsTo(ContractVersion::class, 'contract_version_id');
    }
}
