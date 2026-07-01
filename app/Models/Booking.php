<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Booking extends Model
{
    /**
     * @var array<int, string>
     */
    public const FINISHED_STATUSES = [
        'COMPLETED',
        'REVIEWED',
    ];

    /**
     * @var array<int, string>
     */
    public const CHECK_IN_STATUSES = [
        'CONFIRMED',
    ];

    /**
     * @var array<int, string>
     */
    public const COMPLETE_STATUSES = [
        'CHECKED_IN',
        'PAID',
    ];

    /**
     * @var array<int, string>
     */
    public const ACCEPT_STATUSES = [
        'WAITING_CUSTOMER_CONFIRMATION',
    ];

    /**
     * @var array<int, string>
     */
    public const LATE_CANCELLABLE_STATUSES = [
        'PENDING',
        'CONFIRMED',
    ];

    /**
     * @var array<int, string>
     */
    public const RESCHEDULABLE_STATUSES = [
        'PENDING',
        'WAITING_CUSTOMER_CONFIRMATION',
        'CONFIRMED',
        'AUTO_CANCELLED',
        'LATE_CANCELLED',
    ];

    protected $fillable = [
        'booking_code',
        'user_id',
        'capster_id',
        'booking_start',
        'booking_end',
        'service_total',
        'capster_fee',
        'grand_total',
        'status',
        'admin_confirmed_at',
        'customer_response_deadline',
        'checked_in_at',
        'completed_at',
    ];

    protected function casts(): array
    {
        return [
            'booking_start' => 'datetime',
            'booking_end' => 'datetime',
            'admin_confirmed_at' => 'datetime',
            'customer_response_deadline' => 'datetime',
            'checked_in_at' => 'datetime',
            'completed_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function capster(): BelongsTo
    {
        return $this->belongsTo(Capster::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(BookingItem::class);
    }

    public function payment(): HasOne
    {
        return $this->hasOne(Payment::class);
    }

    public function review(): HasOne
    {
        return $this->hasOne(Review::class);
    }
}
