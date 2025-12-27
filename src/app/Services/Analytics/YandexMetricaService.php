<?php

namespace App\Services\Analytics;

use App\Models\AnalyticsSettings;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class YandexMetricaService
{
    private AnalyticsSettings $settings;

    private string $baseUrl = 'https://api-metrica.yandex.net';

    public function __construct()
    {
        $this->settings = AnalyticsSettings::getSettings();
    }

    /**
     * Получить статистику за период
     */
    public function getStatistics(array $params = []): array
    {
        if (! $this->settings->isYandexEnabled() || ! $this->settings->yandex_metrica_id) {
            return [];
        }

        $defaultParams = [
            'ids' => $this->settings->yandex_metrica_id,
            'date1' => now()->subDays(7)->format('Y-m-d'),
            'date2' => now()->format('Y-m-d'),
            'metrics' => 'ym:s:visits,ym:s:users,ym:s:pageviews,ym:s:bounceRate,ym:s:sessionDuration',
            'dimensions' => 'ym:s:date',
            'sort' => 'ym:s:date',
        ];

        $queryParams = array_merge($defaultParams, $params);

        try {
            $response = Http::withHeaders([
                'Authorization' => 'OAuth '.$this->settings->yandex_metrica_api_key,
                'Content-Type' => 'application/json',
            ])->get($this->baseUrl.'/stat/v1/data', $queryParams);

            if ($response->successful()) {
                return $response->json();
            }

            Log::warning('Yandex Metrica API error', [
                'status' => $response->status(),
                'body' => $response->body(),
            ]);

        } catch (\Exception $e) {
            Log::error('Yandex Metrica API exception', [
                'message' => $e->getMessage(),
            ]);
        }

        return [];
    }

    /**
     * Получить источники трафика
     */
    public function getTrafficSources(array $params = []): array
    {
        $defaultParams = [
            'ids' => $this->settings->yandex_metrica_id,
            'date1' => now()->subDays(30)->format('Y-m-d'),
            'date2' => now()->format('Y-m-d'),
            'metrics' => 'ym:s:visits,ym:s:users',
            'dimensions' => 'ym:s:lastTrafficSource',
            'limit' => 20,
            'sort' => '-ym:s:visits',
        ];

        $queryParams = array_merge($defaultParams, $params);

        try {
            $response = Http::withHeaders([
                'Authorization' => 'OAuth '.$this->settings->yandex_metrica_api_key,
                'Content-Type' => 'application/json',
            ])->get($this->baseUrl.'/stat/v1/data', $queryParams);

            if ($response->successful()) {
                return $response->json();
            }

        } catch (\Exception $e) {
            Log::error('Yandex Metrica traffic sources error', [
                'message' => $e->getMessage(),
            ]);
        }

        return [];
    }

    /**
     * Получить популярные страницы
     */
    public function getPopularPages(array $params = []): array
    {
        $defaultParams = [
            'ids' => $this->settings->yandex_metrica_id,
            'date1' => now()->subDays(30)->format('Y-m-d'),
            'date2' => now()->format('Y-m-d'),
            'metrics' => 'ym:s:pageviews,ym:s:visits',
            'dimensions' => 'ym:s:pageURL',
            'limit' => 20,
            'sort' => '-ym:s:pageviews',
        ];

        $queryParams = array_merge($defaultParams, $params);

        try {
            $response = Http::withHeaders([
                'Authorization' => 'OAuth '.$this->settings->yandex_metrica_api_key,
                'Content-Type' => 'application/json',
            ])->get($this->baseUrl.'/stat/v1/data', $queryParams);

            if ($response->successful()) {
                return $response->json();
            }

        } catch (\Exception $e) {
            Log::error('Yandex Metrica popular pages error', [
                'message' => $e->getMessage(),
            ]);
        }

        return [];
    }

    /**
     * Получить цели и конверсии
     */
    public function getGoals(array $params = []): array
    {
        $defaultParams = [
            'ids' => $this->settings->yandex_metrica_id,
            'date1' => now()->subDays(30)->format('Y-m-d'),
            'date2' => now()->format('Y-m-d'),
            'metrics' => 'ym:s:goalConversions,ym:s:goalConversionRate',
            'dimensions' => 'ym:s:goalName',
        ];

        $queryParams = array_merge($defaultParams, $params);

        try {
            $response = Http::withHeaders([
                'Authorization' => 'OAuth '.$this->settings->yandex_metrica_api_key,
                'Content-Type' => 'application/json',
            ])->get($this->baseUrl.'/stat/v1/data', $queryParams);

            if ($response->successful()) {
                return $response->json();
            }

        } catch (\Exception $e) {
            Log::error('Yandex Metrica goals error', [
                'message' => $e->getMessage(),
            ]);
        }

        return [];
    }

    /**
     * Сгенерировать скрипт для вставки на сайт
     */
    public function generateTrackingScript(): string
    {
        if (! $this->settings->isYandexEnabled()) {
            return '';
        }

        $counterId = $this->settings->yandex_metrica_id;

        $script = '<!-- Yandex.Metrica counter -->'.PHP_EOL;
        $script .= '<script type="text/javascript">'.PHP_EOL;
        $script .= '(function(m,e,t,r,i,k,a){m[i]=m[i]||function(){(m[i].a=m[i].a||[]).push(arguments)};'.PHP_EOL;
        $script .= 'm[i].l=1*new Date();k=e.createElement(t),a=e.getElementsByTagName(t)[0],'.PHP_EOL;
        $script .= 'k.async=1,k.src=r,a.parentNode.insertBefore(k,a)})'.PHP_EOL;
        $script .= '(window, document, "yandex_metrica_callbacks");'.PHP_EOL;
        $script .= "ya({$counterId}, {debug: false});".PHP_EOL;
        $script .= '</script>'.PHP_EOL;
        $script .= "<noscript><div><img src=\"https://mc.yandex.ru/watch/{$counterId}\" style=\"position:absolute; left:-9999px;\" alt=\"\" /></div></noscript>".PHP_EOL;

        // Добавляем отслеживание целей если настроено
        if ($this->settings->custom_events_enabled) {
            $script .= PHP_EOL.'<!-- Custom event tracking -->'.PHP_EOL;
            $script .= '<script>'.PHP_EOL;
            $script .= '// Отслеживание отправки форм'.PHP_EOL;
            $script .= "document.addEventListener('form:submit', function(e) {".PHP_EOL;
            $script .= "    ya({$counterId}, 'reachGoal', 'FORM_SUBMIT', {form_type: e.detail.formType});".PHP_EOL;
            $script .= '});'.PHP_EOL;
            $script .= PHP_EOL;
            $script .= '// Отслеживание кликов по телефону'.PHP_EOL;
            $script .= "document.addEventListener('click', function(e) {".PHP_EOL;
            $script .= "    if (e.target.matches('a[href^=\"tel:\"]')) {".PHP_EOL;
            $script .= "        ya({$counterId}, 'reachGoal', 'CLICK_TEL');".PHP_EOL;
            $script .= '    }'.PHP_EOL;
            $script .= "    if (e.target.matches('a[href*=\"telegram\"]')) {".PHP_EOL;
            $script .= "        ya({$counterId}, 'reachGoal', 'CLICK_TELEGRAM');".PHP_EOL;
            $script .= '    }'.PHP_EOL;
            $script .= "    if (e.target.matches('a[href*=\"whatsapp\"]')) {".PHP_EOL;
            $script .= "        ya({$counterId}, 'reachGoal', 'CLICK_WHATSAPP');".PHP_EOL;
            $script .= '    }'.PHP_EOL;
            $script .= '});'.PHP_EOL;
            $script .= '</script>'.PHP_EOL;
        }

        return $script;
    }

    /**
     * Отправить событие в Яндекс.Метрику
     */
    public function trackEvent(string $eventName, array $params = []): void
    {
        if (! $this->settings->isYandexEnabled() || ! $this->settings->custom_events_enabled) {
            return;
        }

        // В реальном проекте здесь можно добавить отправку событий через JavaScript
        // или через API метрики, если доступен соответствующий endpoint

        Log::info('Yandex Metrica event tracked', [
            'event' => $eventName,
            'params' => $params,
        ]);
    }
}
