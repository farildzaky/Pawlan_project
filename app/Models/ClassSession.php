<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class ClassSession extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'class_sessions';

    protected $fillable = [
        'class_id', 'trainer_id', 'session_date', 'start_time', 'end_time',
        'location', 'capacity', 'booked_count', 'price', 'status', 'notes',
    ];

    protected $casts = [
        'session_date' => 'date',
        'price' => 'decimal:2',
        'capacity' => 'integer',
        'booked_count' => 'integer',
    ];

    protected $appends = ['available_slots'];

    public function getAvailableSlotsAttribute(): int
    {
        return max(0, (int) $this->capacity - (int) $this->booked_count);
    }

    public function gymClass(): BelongsTo
    {
        return $this->belongsTo(GymClass::class, 'class_id');
    }

    public function trainer(): BelongsTo
    {
        return $this->belongsTo(Trainer::class, 'trainer_id');
    }

    public function bookings(): HasMany
    {
        return $this->hasMany(Booking::class, 'session_id');
    }
}
