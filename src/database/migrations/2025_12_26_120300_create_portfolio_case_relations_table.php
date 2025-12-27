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
        Schema::create('portfolio_case_product', function (Blueprint $table) {
            $table->foreignId('case_id')->constrained('portfolio_cases')->cascadeOnDelete();
            $table->foreignId('product_id')->constrained('products')->cascadeOnDelete();
            $table->primary(['case_id', 'product_id']);
        });

        Schema::create('portfolio_case_service', function (Blueprint $table) {
            $table->foreignId('case_id')->constrained('portfolio_cases')->cascadeOnDelete();
            $table->foreignId('service_id')->constrained('services')->cascadeOnDelete();
            $table->primary(['case_id', 'service_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('portfolio_case_service');
        Schema::dropIfExists('portfolio_case_product');
    }
};
