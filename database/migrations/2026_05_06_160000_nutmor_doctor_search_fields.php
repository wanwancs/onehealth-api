<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    protected $connection = 'nutmor';

    public function up(): void
    {
        Schema::connection('nutmor')->table('clinic_branches', function (Blueprint $table): void {
            $table->string('phone')->nullable()->after('address');
            $table->time('opens_at')->nullable()->after('phone');
            $table->time('closes_at')->nullable()->after('opens_at');
        });

        Schema::connection('nutmor')->table('doctors', function (Blueprint $table): void {
            $table->boolean('is_verified')->default(false)->after('review_count');
            $table->string('license_number')->nullable()->after('is_verified');
            $table->unsignedTinyInteger('years_experience')->nullable()->after('license_number');
            $table->text('bio')->nullable()->after('years_experience');
            $table->json('tags')->nullable()->after('bio');
            $table->unsignedInteger('followers_count')->default(0)->after('tags');
            $table->string('response_time_label')->nullable()->after('followers_count');
            $table->unsignedTinyInteger('appointment_slot_minutes')->default(15)->after('response_time_label');
        });
    }

    public function down(): void
    {
        Schema::connection('nutmor')->table('clinic_branches', function (Blueprint $table): void {
            $table->dropColumn(['phone', 'opens_at', 'closes_at']);
        });

        Schema::connection('nutmor')->table('doctors', function (Blueprint $table): void {
            $table->dropColumn([
                'is_verified',
                'license_number',
                'years_experience',
                'bio',
                'tags',
                'followers_count',
                'response_time_label',
                'appointment_slot_minutes',
            ]);
        });
    }
};
