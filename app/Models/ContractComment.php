<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ContractComment extends Model
{
    use HasFactory;

    protected $fillable = [
        'contract_id',
        'user_id',
        'author_name',
        'author_role',
        'clause_key',
        'clause_title',
        'content',
    ];

    public function contract(): BelongsTo
    {
        return $this->belongsTo(Contract::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
