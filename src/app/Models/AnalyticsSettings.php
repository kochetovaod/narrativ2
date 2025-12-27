<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AnalyticsSettings extends Model
{
    use HasFactory;

    protected $fillable = [
        'yandex_metrica_id',
        'yandex_metrica_api_key',
        'google_analytics_id',
        'google_analytics_api_key',
        'google_tag_manager_id',
        'is_yandex_enabled',
        'is_google_enabled',
        'is_tag_manager_enabled',
        'enhanced_ecommerce',
        'adwords_conversion_id',
        'adwords_conversion_label',
        'custom_events_enabled',
        'tracking_ip_exclusions',
        'data_retention_days',
    ];

    protected $casts = [
        'is_yandex_enabled' => 'boolean',
        'is_google_enabled' => 'boolean',
        'is_tag_manager_enabled' => 'boolean',
        'enhanced_ecommerce' => 'boolean',
        'custom_events_enabled' => 'boolean',
        'tracking_ip_exclusions' => 'array',
    ];

    /**
     * Получить настройки аналитики (синглтон)
     */
    public static function getSettings(): self
    {
        return static::firstOrCreate([], [
            'yandex_metrica_id' => '',
            'google_analytics_id' => '',
            'is_yandex_enabled' => false,
            'is_google_enabled' => false,
            'is_tag_manager_enabled' => false,
            'enhanced_ecommerce' => true,
            'custom_events_enabled' => true,
            'data_retention_days' => 90,
        ]);
    }

    /**
     * Получить статус включения аналитики
     */
    public function isAnyEnabled(): bool
    {
        return $this->is_yandex_enabled || $this->is_google_enabled || $this->is_tag_manager_enabled;
    }

    /**
     * Проверить, включена ли Яндекс.Метрика
     */
    public function isYandexEnabled(): bool
    {
        return $this->is_yandex_enabled && ! empty($this->yandex_metrica_id);
    }

    /**
     * Проверить, включен ли Google Analytics
     */
    public function isGoogleEnabled(): bool
    {
        return $this->is_google_enabled && ! empty($this->google_analytics_id);
    }

    /**
     * Проверить, включен ли Google Tag Manager
     */
    public function isTagManagerEnabled(): bool
    {
        return $this->is_tag_manager_enabled && ! empty($this->google_tag_manager_id);
    }

    /**
     * Проверить IP на исключение из трекинга
     */
    public function isIpExcluded(string $ip): bool
    {
        if (empty($this->tracking_ip_exclusions)) {
            return false;
        }

        return in_array($ip, $this->tracking_ip_exclusions, true);
    }
}
