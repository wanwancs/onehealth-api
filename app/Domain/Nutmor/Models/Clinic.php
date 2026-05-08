<?php

namespace App\Domain\Nutmor\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Clinic extends Model
{
    protected $connection = 'nutmor';

    protected $fillable = [
        'slug',
        'name',
        'description',
        'cover_image_url',
        'location_text',
        'rating',
        'review_count',
        'published_at',
    ];

    protected function casts(): array
    {
        return [
            'published_at' => 'datetime',
            'rating' => 'decimal:2',
            'review_count' => 'integer',
        ];
    }

    public function branches(): HasMany
    {
        return $this->hasMany(ClinicBranch::class);
    }

    public function scopePublished($query)
    {
        return $query->whereNotNull('published_at');
    }
}
