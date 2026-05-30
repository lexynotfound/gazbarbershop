<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Capster extends Model
{
    protected $fillable = [
        'name',
        'photo',
        'rating',
        'service_fee',
        'is_active',
        'description',
    ];

    protected function casts(): array
    {
        return [
            'rating' => 'decimal:1',
            'is_active' => 'boolean',
        ];
    }

    public function schedules(): HasMany
    {
        return $this->hasMany(CapsterSchedule::class);
    }

    public function bookings(): HasMany
    {
        return $this->hasMany(Booking::class);
    }

    public function reviews(): HasMany
    {
        return $this->hasMany(Review::class);
    }
}
