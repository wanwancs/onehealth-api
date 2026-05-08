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
            $table->string('specialty')->nullable()->after('display_name');
            $table->string('avatar_url')->nullable()->after('specialty');
            $table->decimal('rating', 3, 2)->nullable()->after('avatar_url');
        });

        Schema::connection('nutmor')->table('clinics', function (Blueprint $table): void {
            $table->string('cover_image_url')->nullable()->after('description');
            $table->string('location_text')->nullable()->after('cover_image_url');
            $table->decimal('rating', 3, 2)->nullable()->after('location_text');
        });
    }

    public function down(): void
    {
        Schema::connection('nutmor')->table('doctors', function (Blueprint $table): void {
            $table->dropColumn(['specialty', 'avatar_url', 'rating']);
        });

        Schema::connection('nutmor')->table('clinics', function (Blueprint $table): void {
            $table->dropColumn(['cover_image_url', 'location_text', 'rating']);
        });
    }
};
