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

        if ($driver === 'mysql') {
            DB::statement("ALTER TABLE section_applications MODIFY status ENUM('pending', 'approved', 'rejected', 'confirmed') NOT NULL DEFAULT 'pending'");
            DB::table('section_applications')->where('status', 'confirmed')->update(['status' => 'approved']);
            DB::statement("ALTER TABLE section_applications MODIFY status ENUM('pending', 'approved', 'rejected') NOT NULL DEFAULT 'pending'");
        } else {
            Schema::create('section_applications_tmp', function (Blueprint $table) {
                $table->id();
                $table->foreignId('section_id')->constrained('sections')->cascadeOnDelete();
                $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
                $table->string('name');
                $table->string('email');
                $table->string('phone', 50)->nullable();
                $table->text('message')->nullable();
                $table->string('status', 20)->default('pending');
                $table->timestamps();
            });

            $rows = DB::table('section_applications')->get();

            foreach ($rows as $row) {
                DB::table('section_applications_tmp')->insert([
                    'id' => $row->id,
                    'section_id' => $row->section_id,
                    'user_id' => $row->user_id,
                    'name' => $row->name,
                    'email' => $row->email,
                    'phone' => $row->phone,
                    'message' => $row->message,
                    'status' => $row->status === 'confirmed' ? 'approved' : $row->status,
                    'created_at' => $row->created_at,
                    'updated_at' => $row->updated_at,
                ]);
            }

            Schema::drop('section_applications');
            Schema::rename('section_applications_tmp', 'section_applications');
        }

        Schema::create('user_notifications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->string('type', 50);
            $table->string('title');
            $table->text('message');
            $table->string('link')->nullable();
            $table->timestamp('read_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        $driver = Schema::getConnection()->getDriverName();

        Schema::dropIfExists('user_notifications');

        if ($driver === 'mysql') {
            DB::statement("ALTER TABLE section_applications MODIFY status ENUM('pending', 'approved', 'rejected', 'confirmed') NOT NULL DEFAULT 'pending'");
            DB::table('section_applications')->where('status', 'approved')->update(['status' => 'confirmed']);
            DB::table('section_applications')->where('status', 'rejected')->update(['status' => 'pending']);
            DB::statement("ALTER TABLE section_applications MODIFY status ENUM('pending', 'confirmed') NOT NULL DEFAULT 'pending'");
        } else {
            Schema::create('section_applications_tmp', function (Blueprint $table) {
                $table->id();
                $table->foreignId('section_id')->constrained('sections')->cascadeOnDelete();
                $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
                $table->string('name');
                $table->string('email');
                $table->string('phone', 50)->nullable();
                $table->text('message')->nullable();
                $table->string('status', 20)->default('pending');
                $table->timestamps();
            });

            $rows = DB::table('section_applications')->get();

            foreach ($rows as $row) {
                DB::table('section_applications_tmp')->insert([
                    'id' => $row->id,
                    'section_id' => $row->section_id,
                    'user_id' => $row->user_id,
                    'name' => $row->name,
                    'email' => $row->email,
                    'phone' => $row->phone,
                    'message' => $row->message,
                    'status' => $row->status === 'approved' ? 'confirmed' : ($row->status === 'rejected' ? 'pending' : $row->status),
                    'created_at' => $row->created_at,
                    'updated_at' => $row->updated_at,
                ]);
            }

            Schema::drop('section_applications');
            Schema::rename('section_applications_tmp', 'section_applications');
        }
    }
};
