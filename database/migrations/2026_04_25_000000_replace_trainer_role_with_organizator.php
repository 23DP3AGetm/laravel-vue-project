<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        if (DB::getDriverName() === 'mysql') {
            DB::statement("ALTER TABLE users MODIFY role ENUM('user', 'trainer', 'organizator', 'admin') NOT NULL DEFAULT 'user'");
        }

        DB::table('users')
            ->where('role', 'trainer')
            ->update(['role' => 'organizator']);

        if (DB::getDriverName() === 'mysql') {
            DB::statement("ALTER TABLE users MODIFY role ENUM('user', 'organizator', 'admin') NOT NULL DEFAULT 'user'");
        }
    }

    public function down(): void
    {
        if (DB::getDriverName() === 'mysql') {
            DB::statement("ALTER TABLE users MODIFY role ENUM('user', 'trainer', 'organizator', 'admin') NOT NULL DEFAULT 'user'");
        }

        DB::table('users')
            ->where('role', 'organizator')
            ->update(['role' => 'trainer']);

        if (DB::getDriverName() === 'mysql') {
            DB::statement("ALTER TABLE users MODIFY role ENUM('user', 'trainer', 'admin') NOT NULL DEFAULT 'user'");
        }
    }
};
