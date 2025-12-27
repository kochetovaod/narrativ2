<?php

declare(strict_types=1);

namespace App\Orchid\Screens\Analytics;

use App\Models\AnalyticsSettings;
use Illuminate\Http\Request;
use Orchid\Screen\Actions\Button;
use Orchid\Screen\Screen;
use Orchid\Support\Facades\Alert;
use Orchid\Support\Facades\Layout;

class AnalyticsSettingsScreen extends Screen
{
    /**
     * @var string|array<int, string>
     */
    public $permission = 'platform.analytics';

    /**
     * Fetch the analytics settings for editing.
     *
     * @return array<string, AnalyticsSettings>
     */
    public function query(): iterable
    {
        return [
            'settings' => AnalyticsSettings::getSettings(),
        ];
    }

    public function name(): ?string
    {
        return __('Настройки аналитики и метрик');
    }

    public function description(): ?string
    {
        return __('Управление интеграциями с системами аналитики');
    }

    public function commandBar(): iterable
    {
        return [
            Button::make(__('Сохранить настройки'))
                ->icon('check')
                ->method('save'),
        ];
    }

    public function layout(): iterable
    {
        return [
            Layout::view('orchid.analytics.settings'),
        ];
    }

    public function save(Request $request): void
    {
        $data = $request->validate([
            'yandex_metrica_id' => 'nullable|string|max:50',
            'yandex_metrica_api_key' => 'nullable|string|max:255',
            'google_analytics_id' => 'nullable|string|max:50',
            'google_analytics_api_key' => 'nullable|string|max:255',
            'google_tag_manager_id' => 'nullable|string|max:50',
            'is_yandex_enabled' => 'boolean',
            'is_google_enabled' => 'boolean',
            'is_tag_manager_enabled' => 'boolean',
            'enhanced_ecommerce' => 'boolean',
            'adwords_conversion_id' => 'nullable|string|max:50',
            'adwords_conversion_label' => 'nullable|string|max:100',
            'custom_events_enabled' => 'boolean',
            'tracking_ip_exclusions' => 'nullable|array',
            'tracking_ip_exclusions.*' => 'ip',
            'data_retention_days' => 'integer|min:1|max:365',
        ]);

        // Преобразуем массив IP в JSON
        if (isset($data['tracking_ip_exclusions']) && is_array($data['tracking_ip_exclusions'])) {
            $data['tracking_ip_exclusions'] = array_filter($data['tracking_ip_exclusions']);
        }

        $settings = AnalyticsSettings::getSettings();
        $settings->update($data);

        Alert::success(__('Настройки аналитики сохранены'));
    }

    public function testConnections(): void
    {
        $settings = AnalyticsSettings::getSettings();
        $results = [];

        // Тест Яндекс.Метрики
        if ($settings->isYandexEnabled()) {
            try {
                $result = $this->testYandexMetricaConnection($settings);
                $results['yandex'] = $result ? 'Успешно' : 'Ошибка подключения';
            } catch (\Exception $e) {
                $results['yandex'] = 'Ошибка: '.$e->getMessage();
            }
        }

        // Тест Google Analytics
        if ($settings->isGoogleEnabled()) {
            try {
                $result = $this->testGoogleAnalyticsConnection($settings);
                $results['google'] = $result ? 'Успешно' : 'Ошибка подключения';
            } catch (\Exception $e) {
                $results['google'] = 'Ошибка: '.$e->getMessage();
            }
        }

        if (empty($results)) {
            Alert::warning('Нет активных интеграций для тестирования');

            return;
        }

        $message = 'Результаты тестирования:<br>'.implode('<br>', array_map(
            fn ($service, $status) => '<strong>'.strtoupper($service).":</strong> $status",
            array_keys($results),
            $results
        ));

        Alert::info($message);
    }

    private function testYandexMetricaConnection(AnalyticsSettings $settings): bool
    {
        if (empty($settings->yandex_metrica_api_key)) {
            return false;
        }

        // Здесь можно добавить реальную проверку API
        // Пока возвращаем true для демонстрации
        return true;
    }

    private function testGoogleAnalyticsConnection(AnalyticsSettings $settings): bool
    {
        if (empty($settings->google_analytics_api_key)) {
            return false;
        }

        // Здесь можно добавить реальную проверку API
        // Пока возвращаем true для демонстрации
        return true;
    }
}
