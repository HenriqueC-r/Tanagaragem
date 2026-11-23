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
     * - senão (loja sem plano ativo), nenhum.
     */
    public function activePlan(): ?Plan
    {
        $plan = $this->plan;

        if ($plan && ($this->plan_expires_at === null || $this->plan_expires_at->isFuture())) {
            return $plan;
        }

        if ($this->type === self::TYPE_PERSON) {
            return Plan::where('slug', 'gratis')->first();
        }

        return null;
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
