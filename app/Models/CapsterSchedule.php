<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CapsterSchedule extends Model
{
    public const OPERATING_START = '10:00';

    public const OPERATING_END = '22:00';

    protected $fillable = [
        'capster_id',
        'work_date',
        'start_time',
        'end_time',
        'is_available',
    ];

    protected function casts(): array
    {
        return [
            'work_date' => 'date',
            'is_available' => 'boolean',
        ];
    }

    public function capster(): BelongsTo
    {
        return $this->belongsTo(Capster::class);
    }
}
