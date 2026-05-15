<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('section_schedules')) {
            return;
        }

        if (!Schema::hasColumn('section_schedules', 'is_day_off')) {
            Schema::table('section_schedules', function (Blueprint $table) {
                $table->boolean('is_day_off')->default(false)->after('day_of_week');
            });
        }

        $driver = Schema::getConnection()->getDriverName();

        if ($driver === 'mysql') {
            DB::statement('ALTER TABLE section_schedules MODIFY start_time TIME NULL');
            DB::statement('ALTER TABLE section_schedules MODIFY end_time TIME NULL');
        }
    }

    public function down(): void
    {
        if (!Schema::hasTable('section_schedules')) {
            return;
        }

        $driver = Schema::getConnection()->getDriverName();

        if ($driver === 'mysql') {
            DB::statement("UPDATE section_schedules SET start_time = COALESCE(start_time, '00:00:00'), end_time = COALESCE(end_time, '00:00:00') WHERE start_time IS NULL OR end_time IS NULL");
            DB::statement('ALTER TABLE section_schedules MODIFY start_time TIME NOT NULL');
            DB::statement('ALTER TABLE section_schedules MODIFY end_time TIME NOT NULL');
        }

        if (Schema::hasColumn('section_schedules', 'is_day_off')) {
            Schema::table('section_schedules', function (Blueprint $table) {
                $table->dropColumn('is_day_off');
            });
        }
    }
};
