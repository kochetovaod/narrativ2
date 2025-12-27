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
        Schema::create('media_files', function (Blueprint $table) {
            $table->id();
            $table->enum('disk', ['local']);
            $table->string('path');
            $table->string('original_name');
            $table->string('mime');
            $table->integer('size');
            $table->integer('width')->nullable();
            $table->integer('height')->nullable();
            $table->timestamp('created_at')->useCurrent();
        });

        Schema::create('media_links', function (Blueprint $table) {
            $table->id();
            $table->string('entity_type');
            $table->unsignedBigInteger('entity_id');
            $table->foreignId('media_id')->constrained('media_files')->cascadeOnDelete();
            $table->string('role');
            $table->integer('sort')->default(0);
            $table->text('alt')->nullable();
            $table->timestamp('created_at')->useCurrent();

            $table->index(['entity_type', 'entity_id']);
            $table->index('media_id');
            $table->index('sort');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('media_links');
        Schema::dropIfExists('media_files');
    }
};
