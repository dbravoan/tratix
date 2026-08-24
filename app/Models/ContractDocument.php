<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ContractDocument extends Model
{
    use HasFactory;

    public const STATUSES = ['uploaded', 'validated'];

    protected $fillable = [
        'contract_id', 'requirement_key', 'filename', 'path', 'mime', 'size',
        'status', 'uploaded_by_user_id', 'uploaded_at', 'validated_at',
    ];

    protected $casts = [
        'uploaded_at' => 'datetime',
        'validated_at' => 'datetime',
    ];

    public function contract(): BelongsTo
    {
        return $this->belongsTo(Contract::class);
    }

    public function uploader(): BelongsTo
    {
        return $this->belongsTo(User::class, 'uploaded_by_user_id');
    }
}
