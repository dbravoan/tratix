<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AuditEvent extends Model
{
    use HasFactory;

    protected $table = 'audit_trail';

    public const EVENTS = [
        'contract_created',
        'sent_for_review',
        'proposal_created',
        'proposal_approved',
        'proposal_rejected',
        'version_frozen',
        'sent_for_signature',
        'signature_link_viewed',
        'signed',
        'sealed',
        'cancelled',
    ];

    protected $fillable = [
        'contract_id', 'user_id', 'event', 'actor', 'detail', 'ip', 'user_agent', 'happened_at',
    ];

    protected $casts = [
        'happened_at' => 'datetime',
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
