<?php

namespace App\Domain\Medreco\Models;

use Illuminate\Database\Eloquent\Model;

class QueueEntry extends Model
{
    protected $connection = 'medreco';

    protected $fillable = [
        'nutmor_appointment_id',
        'nutmor_doctor_id',
        'queue_date',
        'position',
        'status',
        'patient_display_name',
        'doctor_display_name',
        'clinic_name',
        'branch_name',
    ];

    protected function casts(): array
    {
        return [
            'queue_date' => 'date',
        ];
    }
}
