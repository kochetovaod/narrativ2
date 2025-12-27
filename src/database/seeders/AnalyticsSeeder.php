<?php

namespace Database\Seeders;

use App\Models\AnalyticsSettings;
use Illuminate\Database\Seeder;

class AnalyticsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Создаем настройки аналитики по умолчанию
        AnalyticsSettings::firstOrCreate(
            [],
            [
                'yandex_metrica_id' => '',
                'yandex_metrica_api_key' => '',
                'google_analytics_id' => '',
                'google_analytics_api_key' => '',
                'google_tag_manager_id' => '',
                'is_yandex_enabled' => false,
                'is_google_enabled' => false,
                'is_tag_manager_enabled' => false,
                'enhanced_ecommerce' => true,
                'adwords_conversion_id' => '',
                'adwords_conversion_label' => '',
                'custom_events_enabled' => true,
                'tracking_ip_exclusions' => ['127.0.0.1', '::1', 'localhost'],
                'data_retention_days' => 90,
            ]
        );
    }
}
