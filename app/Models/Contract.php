<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Contract extends Model
{
    use HasFactory;

    public const TYPES = [
        'bienes_muebles', 'inmuebles', 'vehiculos', 'servicios', 'internacional',
        'alquiler', 'prestamo', 'cesion_derechos', 'nda', 'arras',
    ];

    public const TRANSACTIONS = ['b2b', 'b2c', 'c2c', 'c2b'];

    public const JURISDICTIONS = ['nacional', 'intracomunitario', 'internacional'];

    public const STATUSES = [
        'borrador',
        'en_revision',
        'lista_para_firma',
        'en_firma',
        'firmado',
        'cancelado',
    ];

    public const LAW_COUNTRIES = [
        'ES' => 'España',
        'AR' => 'Argentina',
        'MX' => 'México',
        'CO' => 'Colombia',
        'CL' => 'Chile',
        'PE' => 'Perú',
        'UY' => 'Uruguay',
    ];

    protected $fillable = [
        'user_id', 'creator_role', 'reference', 'contract_type', 'transaction_type', 'jurisdiction',
        'applicable_law', 'title', 'object_type', 'object_description', 'quantity', 'price_amount',
        'currency', 'tax_amount', 'total_amount', 'city', 'signing_date', 'effective_date',
        'delivery_terms', 'payment_terms', 'warranties', 'special_clauses', 'clauses',
        'status', 'language', 'legal_notes',
        'access_token', 'final_pdf_path', 'final_hash', 'sealed_at', 'signed_version',
        'review_deadline', 'invited_email', 'access_token_expires_at',
    ];

    protected $casts = [
        'quantity' => 'integer',
        'price_amount' => 'decimal:2',
        'tax_amount' => 'decimal:2',
        'total_amount' => 'decimal:2',
        'signing_date' => 'date',
        'effective_date' => 'date',
        'clauses' => 'array',
        'sealed_at' => 'datetime',
        'review_deadline' => 'datetime',
        'access_token_expires_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function parties(): HasMany
    {
        return $this->hasMany(Party::class);
    }

    public function versions(): HasMany
    {
        return $this->hasMany(ContractVersion::class);
    }

    public function proposals(): HasMany
    {
        return $this->hasMany(ClauseProposal::class);
    }

    public function signatures(): HasMany
    {
        return $this->hasMany(Signature::class);
    }

    public function consents(): HasMany
    {
        return $this->hasMany(Consent::class);
    }

    public function auditEvents(): HasMany
    {
        return $this->hasMany(AuditEvent::class);
    }

    public function documents(): HasMany
    {
        return $this->hasMany(ContractDocument::class);
    }

    public function comments(): HasMany
    {
        return $this->hasMany(ContractComment::class)->latest();
    }

    public function seller(): ?Party
    {
        return $this->parties->firstWhere('role', 'vendedor');
    }

    public function buyer(): ?Party
    {
        return $this->parties->firstWhere('role', 'comprador');
    }

    public function latestVersion(): ?ContractVersion
    {
        return $this->versions()->latest('version')->first();
    }

    public function isOwnedBy(?User $user): bool
    {
        return $user !== null && $this->user_id === $user->id;
    }

    public function isSigned(): bool
    {
        return $this->status === 'firmado';
    }

    /**
     * Whether the sharing/signature token is still valid (not expired).
     */
    public function tokenIsValid(): bool
    {
        return $this->access_token !== null
            && ($this->access_token_expires_at === null || $this->access_token_expires_at->isFuture());
    }

    /**
     * The party that is NOT the account owner who created the contract.
     * Used to prefill sharing targets (email/WhatsApp) for the counterparty.
     */
    public function counterparty(): ?Party
    {
        $seller = $this->seller();
        $buyer = $this->buyer();

        if ($this->creator_role === 'vendedor') {
            return $buyer;
        }

        if ($this->creator_role === 'comprador') {
            return $seller;
        }

        // Fallback for older contracts without creator_role: the role not
        // carrying the creator's email (or the buyer by default).
        if ($seller && $buyer) {
            $creatorParty = collect([$seller, $buyer])
                ->first(fn ($p) => $p->email && $p->email === optional($this->user)->email);

            if ($creatorParty) {
                return $creatorParty->role === 'vendedor' ? $buyer : $seller;
            }
        }

        return $buyer ?? $seller;
    }

    /**
     * The signature registered by the counterparty (the role not owned by the
     * creator). Useful to email the signed PDF to the counterparty.
     */
    public function counterpartySignature(): ?Signature
    {
        $role = $this->counterparty()?->role;

        if (! $role) {
            return null;
        }

        return $this->signatures->firstWhere('party_role', $role);
    }

    /**
     * Check if both vendor and buyer have signed.
     */
    public function allPartiesSigned(): bool
    {
        $roles = $this->signatures->pluck('party_role')->all();

        return in_array('vendedor', $roles, true) && in_array('comprador', $roles, true);
    }

    /**
     * Check if a specific role has signed.
     */
    public function partyHasSigned(string $role): bool
    {
        return $this->signatures->contains('party_role', $role);
    }

    /**
     * The jurisdiction whose law governs the contract (ISO 3166-1 alpha-2).
     */
    public function applicableLaw(): string
    {
        return strtoupper($this->applicable_law ?? 'ES');
    }
}
