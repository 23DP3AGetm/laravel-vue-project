<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('sections', function (Blueprint $table) {
            $table->string('slug')->nullable()->after('title');
            $table->enum('status', ['active', 'hidden'])->default('active')->after('location');
        });

        $sections = DB::table('sections')->select(['id', 'title'])->get();

        foreach ($sections as $section) {
            $baseSlug = Str::slug($section->title ?: 'sekcija');
            $slug = $baseSlug !== '' ? $baseSlug : 'sekcija';
            $suffix = 1;

            while (DB::table('sections')->where('slug', $slug)->where('id', '!=', $section->id)->exists()) {
                $slug = "{$baseSlug}-{$suffix}";
                $suffix++;
            }

            DB::table('sections')->where('id', $section->id)->update(['slug' => $slug]);
        }

        Schema::table('sections', function (Blueprint $table) {
            $table->string('slug')->nullable(false)->change();
            $table->unique('slug');
        });
    }

    public function down(): void
    {
        Schema::table('sections', function (Blueprint $table) {
            $table->dropUnique(['slug']);
            $table->dropColumn(['slug', 'status']);
        });
    }
};
