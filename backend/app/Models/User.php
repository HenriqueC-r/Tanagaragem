<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

#[Fillable([
    'name', 'email', 'password',
    'type', 'cpf', 'phone', 'city', 'state',
    'company_name', 'cnpj',
])]
#[Hidden(['password', 'remember_token', 'cpf'])]
class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    public const TYPE_PERSON = 'person';
    public const TYPE_STORE = 'store';

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'plan_expires_at' => 'datetime',
            'cnpj_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function plan(): BelongsTo
    {
        return $this->belongsTo(Plan::class);
    }

    public function vehicles(): HasMany
    {
        return $this->hasMany(Vehicle::class);
    }

    /**
     * Plano que vale agora:
     * - plano contratado e dentro da validade;
     * - senão, pessoa física cai no plano Grátis;
     * - senão, loja COM CNPJ aprovado cai no plano Loja Grátis;
     * - senão (loja pendente de aprovação), nenhum.
     */
    public function activePlan(): ?Plan
    {
        $plan = $this->plan;

        if ($plan && ($this->plan_expires_at === null || $this->plan_expires_at->isFuture())) {
            return $plan;
        }

        return match (true) {
            $this->type === self::TYPE_PERSON
                => Plan::where('slug', 'gratis')->first(),
            $this->type === self::TYPE_STORE && $this->cnpj_verified_at !== null
                => Plan::where('slug', 'loja-gratis')->first(),
            default => null,
        };
    }

    public function vehicleLimit(): int
    {
        return $this->activePlan()?->vehicle_limit ?? 0;
    }

    public function activeVehiclesCount(): int
    {
        return $this->vehicles()->where('status', Vehicle::STATUS_ACTIVE)->count();
    }

    public function canPublishVehicle(): bool
    {
        return $this->activeVehiclesCount() < $this->vehicleLimit();
    }
}
