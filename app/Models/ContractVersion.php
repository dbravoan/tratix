<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ContractVersion extends Model
{
    use HasFactory;

    protected $table = 'contract_versions';

    protected $fillable = [
        'contract_id', 'version', 'clauses', 'changes_summary', 'hash', 'pdf_path', 'frozen_at',
    ];

    protected $casts = [
        'clauses' => 'array',
        'frozen_at' => 'datetime',
    ];

    public function contract(): BelongsTo
    {
        return $this->belongsTo(Contract::class);
    }
}
