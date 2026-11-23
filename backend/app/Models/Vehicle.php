<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Vehicle extends Model
{
    public const STATUS_DRAFT = 'draft';
    public const STATUS_ACTIVE = 'active';
    public const STATUS_PAUSED = 'paused';
    public const STATUS_SOLD = 'sold';

    public const FUELS = ['gasoline', 'ethanol', 'flex', 'diesel', 'electric', 'hybrid', 'cng'];
    public const TRANSMISSIONS = ['manual', 'automatic', 'cvt', 'automated'];

    // status e featured_until ficam FORA do fillable de propósito:
    // só o nosso código muda o estado do anúncio, nunca o formulário.
    protected $fillable = [
        'brand', 'model', 'version', 'year_manufacture', 'year_model',
        'mileage', 'fuel', 'transmission', 'color', 'price_cents',
        'city', 'state', 'description',
    ];

    protected function casts(): array
    {
        return [
            'featured_until' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function photos(): HasMany
    {
        return $this->hasMany(VehiclePhoto::class)->orderBy('position');
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('status', self::STATUS_ACTIVE);
    }
}
