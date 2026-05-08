<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    protected $connection = 'nutmor';

    public function up(): void
    {
        Schema::connection('nutmor')->table('doctors', function (Blueprint $table): void {
            $table->unsignedInteger('review_count')->default(0)->after('rating');
        });

        Schema::connection('nutmor')->table('clinics', function (Blueprint $table): void {
            $table->unsignedInteger('review_count')->default(0)->after('rating');
        });
    }

    public function down(): void
    {
        Schema::connection('nutmor')->table('doctors', function (Blueprint $table): void {
            $table->dropColumn('review_count');
        });

        Schema::connection('nutmor')->table('clinics', function (Blueprint $table): void {
            $table->dropColumn('review_count');
        });
    }
};
