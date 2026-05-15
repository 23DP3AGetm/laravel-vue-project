<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $driver = Schema::getConnection()->getDriverName();

        Schema::table('sections', function (Blueprint $table) {
            $table->string('sport_type')->nullable()->after('title');
            $table->string('age_group')->nullable()->after('sport_type');
        });

        if ($driver === 'mysql') {
            DB::statement("ALTER TABLE section_applications MODIFY status ENUM('pending', 'confirmed') NOT NULL DEFAULT 'pending'");
        }

        Schema::create('section_schedules', function (Blueprint $table) {
            $table->id();
            $table->foreignId('section_id')->constrained('sections')->cascadeOnDelete();
            $table->string('day_of_week', 20);
            $table->boolean('is_day_off')->default(false);
            $table->time('start_time')->nullable();
            $table->time('end_time')->nullable();
            $table->timestamps();
        });

        Schema::create('section_reviews', function (Blueprint $table) {
            $table->id();
            $table->foreignId('section_id')->constrained('sections')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->unsignedTinyInteger('rating');
            $table->text('comment')->nullable();
            $table->timestamp('reviewed_at')->nullable();
            $table->timestamps();
            $table->unique(['section_id', 'user_id']);
        });

        Schema::create('section_addresses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('section_id')->constrained('sections')->cascadeOnDelete();
            $table->string('city')->nullable();
            $table->string('street')->nullable();
            $table->string('full_address')->nullable();
            $table->string('coordinates')->nullable();
            $table->timestamps();
            $table->unique('section_id');
        });
    }

    public function down(): void
    {
        $driver = Schema::getConnection()->getDriverName();

        Schema::dropIfExists('section_addresses');
        Schema::dropIfExists('section_reviews');
        Schema::dropIfExists('section_schedules');

        if ($driver === 'mysql') {
            DB::statement("ALTER TABLE section_applications MODIFY status ENUM('pending') NOT NULL DEFAULT 'pending'");
        }

        Schema::table('sections', function (Blueprint $table) {
            $table->dropColumn(['sport_type', 'age_group']);
        });
    }
};
