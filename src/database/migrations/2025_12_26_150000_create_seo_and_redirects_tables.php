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
        Schema::create('seo_templates', function (Blueprint $table) {
            $table->id();
            $table->string('entity_type');
            $table->text('title_tpl')->nullable();
            $table->text('description_tpl')->nullable();
            $table->text('h1_tpl')->nullable();
            $table->text('og_title_tpl')->nullable();
            $table->text('og_description_tpl')->nullable();
            $table->enum('og_image_mode', ['auto', 'manual']);
            $table->boolean('is_default')->default(false);
            $table->timestamps();

            $table->index('entity_type');
            $table->index('is_default');
        });

        Schema::create('redirects', function (Blueprint $table) {
            $table->id();
            $table->string('from_path')->unique();
            $table->string('to_path');
            $table->integer('code')->default(301);
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index('is_active');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('redirects');
        Schema::dropIfExists('seo_templates');
    }
};
