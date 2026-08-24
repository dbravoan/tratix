<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

#[Fillable([
    'name',
    'email',
    'password',
    'plan',
    'is_admin',
    'credits',
    'referral_code',
    'tax_id',
    'company_name',
    'party_type',
    'phone',
    'address',
    'postal_code',
    'city',
    'country',
    'notify_comments',
    'notify_proposals',
    'notify_signatures',
    'notify_summary',
])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable
{
    /** @use HasFactory<User> */
    use HasFactory, Notifiable;

    public function contracts(): HasMany
    {
        return $this->hasMany(Contract::class);
    }

    public function isPro(): bool
    {
        return in_array($this->plan, ['pro', 'business'], true);
    }

    public function isBusiness(): bool
    {
        return $this->plan === 'business';
    }

    public function isFree(): bool
    {
        return $this->plan === 'free' || empty($this->plan);
    }

    public function planName(): string
    {
        return match ($this->plan) {
            'business' => 'Business',
            'pro' => 'Pro',
            default => 'Gratuito',
        };
    }

    /**
     * Check individual completion items for profile onboarding.
     *
     * @return array<string, array{label: string, done: bool, icon: string}>
     */
    public function profileChecklist(): array
    {
        return [
            'name' => [
                'label' => 'Nombre completo',
                'done' => ! empty(trim((string) $this->name)),
                'icon' => '👤',
            ],
            'tax_id' => [
                'label' => 'NIF / CIF fiscal',
                'done' => ! empty(trim((string) $this->tax_id)),
                'icon' => '🪪',
            ],
            'address' => [
                'label' => 'Domicilio fiscal y Ciudad',
                'done' => ! empty(trim((string) $this->address)) && ! empty(trim((string) $this->city)),
                'icon' => '📍',
            ],
            'phone' => [
                'label' => 'Teléfono de contacto',
                'done' => ! empty(trim((string) $this->phone)),
                'icon' => '📱',
            ],
            'email_verified' => [
                'label' => 'Email verificado',
                'done' => $this->email_verified_at !== null,
                'icon' => '✉️',
            ],
        ];
    }

    /**
     * Calculate profile completion percentage (0-100%).
     */
    public function profileCompletionPercentage(): int
    {
        $checklist = $this->profileChecklist();
        $total = count($checklist);
        if ($total === 0) {
            return 100;
        }

        $done = count(array_filter($checklist, fn ($item) => $item['done']));

        return (int) round(($done / $total) * 100);
    }

    public function isProfileComplete(): bool
    {
        return $this->profileCompletionPercentage() === 100;
    }

    /**
     * Default party details to prefill in contract creation wizards.
     *
     * @return array<string, mixed>
     */
    public function defaultPartyData(): array
    {
        return [
            'party_type' => $this->party_type ?? 'particular',
            'full_name' => $this->name,
            'company_name' => $this->company_name,
            'tax_id' => $this->tax_id,
            'tax_id_country' => $this->country ?? 'ES',
            'email' => $this->email,
            'phone' => $this->phone,
            'address' => $this->address,
            'postal_code' => $this->postal_code,
            'city' => $this->city,
            'country' => $this->country ?? 'ES',
        ];
    }

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_admin' => 'boolean',
            'notify_comments' => 'boolean',
            'notify_proposals' => 'boolean',
            'notify_signatures' => 'boolean',
            'notify_summary' => 'boolean',
        ];
    }
}
