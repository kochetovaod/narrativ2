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
        Schema::create('analytics_settings', function (Blueprint $table) {
            $table->id();
            $table->string('yandex_metrica_id')->nullable()->comment('ID счетчика Яндекс.Метрики');
            $table->string('yandex_metrica_api_key')->nullable()->comment('API ключ Яндекс.Метрики');
            $table->string('google_analytics_id')->nullable()->comment('ID Google Analytics');
            $table->string('google_analytics_api_key')->nullable()->comment('API ключ Google Analytics');
            $table->string('google_tag_manager_id')->nullable()->comment('ID Google Tag Manager');
            $table->boolean('is_yandex_enabled')->default(false)->comment('Включена ли Яндекс.Метрика');
            $table->boolean('is_google_enabled')->default(false)->comment('Включен ли Google Analytics');
            $table->boolean('is_tag_manager_enabled')->default(false)->comment('Включен ли Google Tag Manager');
            $table->boolean('enhanced_ecommerce')->default(true)->comment('Включен ли Enhanced Ecommerce');
            $table->string('adwords_conversion_id')->nullable()->comment('ID конверсии AdWords');
            $table->string('adwords_conversion_label')->nullable()->comment('Метка конверсии AdWords');
            $table->boolean('custom_events_enabled')->default(true)->comment('Включен ли трекинг пользовательских событий');
            $table->json('tracking_ip_exclusions')->nullable()->comment('IP адреса для исключения из трекинга');
            $table->integer('data_retention_days')->default(90)->comment('Количество дней хранения данных');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('analytics_settings');
    }
};
