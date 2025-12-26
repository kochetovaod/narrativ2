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
        Schema::create('portfolio_cases', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->longText('description')->nullable();
            $table->string('client_name')->nullable();
            $table->boolean('is_nda')->default(false);
            $table->string('status')->default('draft');
            $table->date('date')->nullable();
            $table->timestamp('published_at')->nullable();
            $table->timestamps();

            $table->index('is_nda');
            $table->index('status');
            $table->index('date');
            $table->index('published_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('portfolio_cases');
    }
};
