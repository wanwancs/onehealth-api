<?php

namespace App\Domain\Nutmor\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ClinicBranch extends Model
{
    protected $connection = 'nutmor';

    protected $fillable = [
        'clinic_id',
        'name',
        'address',
        'phone',
        'opens_at',
        'closes_at',
    ];

    public function clinic(): BelongsTo
    {
        return $this->belongsTo(Clinic::class);
    }

    public function doctors(): HasMany
    {
        return $this->hasMany(Doctor::class);
    }
}
