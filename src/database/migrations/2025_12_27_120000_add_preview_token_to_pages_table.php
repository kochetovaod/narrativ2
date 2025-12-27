<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (! Schema::hasColumn('pages', 'preview_token')) {
            Schema::table('pages', function (Blueprint $table) {
                $table->string('preview_token', 32)->nullable()->unique()->after('slug');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasColumn('pages', 'preview_token')) {
            Schema::table('pages', function (Blueprint $table) {
                $table->dropColumn('preview_token');
            });
        }
    }
};
