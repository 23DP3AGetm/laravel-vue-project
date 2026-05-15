<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('section_schedules', 'coach_name')) {
            return;
        }

        Schema::table('section_schedules', function (Blueprint $table) {
            $table->dropColumn('coach_name');
        });
    }

    public function down(): void
    {
        if (Schema::hasColumn('section_schedules', 'coach_name')) {
            return;
        }

        Schema::table('section_schedules', function (Blueprint $table) {
            $table->string('coach_name')->nullable()->after('end_time');
        });
    }
};
