<?php

namespace App\Domain\Nutmor\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Doctor extends Model
{
    protected $connection = 'nutmor';

    protected $fillable = [
        'clinic_branch_id',
        'display_name',
        'specialty',
        'avatar_url',
        'rating',
        'review_count',
        'is_verified',
        'license_number',
        'years_experience',
        'bio',
        'tags',
        'followers_count',
        'response_time_label',
        'appointment_slot_minutes',
        'onehealth_user_id',
    ];

    protected function casts(): array
    {
        return [
            'rating' => 'decimal:2',
            'review_count' => 'integer',
            'is_verified' => 'boolean',
            'years_experience' => 'integer',
            'tags' => 'array',
            'followers_count' => 'integer',
            'appointment_slot_minutes' => 'integer',
        ];
    }

    public function branch(): BelongsTo
    {
        return $this->belongsTo(ClinicBranch::class, 'clinic_branch_id');
    }

    public function appointments(): HasMany
    {
        return $this->hasMany(Appointment::class);
    }
}
