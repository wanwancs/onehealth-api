<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    protected $connection = 'nutmor';

    public function up(): void
    {
        Schema::connection('nutmor')->create('doctors', function (Blueprint $table) {
            $table->id();
            $table->foreignId('clinic_branch_id')->constrained('clinic_branches')->cascadeOnDelete();
            $table->string('display_name');
            $table->unsignedBigInteger('onehealth_user_id')->nullable()->index();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::connection('nutmor')->dropIfExists('doctors');
    }
};
