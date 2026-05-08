<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    protected $connection = 'medreco';

    public function up(): void
    {
        Schema::connection('medreco')->create('queue_entries', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('nutmor_appointment_id')->index();
            $table->unsignedBigInteger('nutmor_doctor_id')->index();
            $table->date('queue_date');
            $table->unsignedInteger('position')->default(0);
            $table->string('status')->default('waiting');
            $table->string('patient_display_name');
            $table->string('doctor_display_name');
            $table->string('clinic_name');
            $table->string('branch_name');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::connection('medreco')->dropIfExists('queue_entries');
    }
};
