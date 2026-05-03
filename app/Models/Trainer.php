<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

class Trainer extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name', 'email', 'phone', 'specialization', 'years_experience',
        'certification', 'bio', 'hourly_rate', 'is_active',
    ];

    protected $casts = [
        'hourly_rate' => 'decimal:2',
        'is_active' => 'boolean',
        'years_experience' => 'integer',
    ];

    public function classes(): BelongsToMany
    {
        return $this->belongsToMany(GymClass::class, 'class_trainer', 'trainer_id', 'class_id')->withTimestamps();
    }

    public function sessions(): HasMany
    {
        return $this->hasMany(ClassSession::class, 'trainer_id');
    }

    public function user(): HasOne
    {
        return $this->hasOne(User::class, 'trainer_id');
    }
}
