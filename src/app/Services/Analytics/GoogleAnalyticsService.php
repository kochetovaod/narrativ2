<?php

namespace App\Services\Analytics;

use App\Models\AnalyticsSettings;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class GoogleAnalyticsService
{
    private AnalyticsSettings $settings;

    private string $baseUrl = 'https://analyticsdata.googleapis.com/v1';

    public function __construct()
    {
        $this->settings = AnalyticsSettings::getSettings();
    }

    /**
     * Получить статистику за период
     */
    public function getStatistics(array $params = []): array
    {
        if (! $this->settings->isGoogleEnabled() || ! $this->settings->google_analytics_id) {
            return [];
        }

        $defaultParams = [
            'property' => $this->getPropertyId(),
            'dateRanges' => [
                [
                    'startDate' => now()->subDays(7)->format('Y-m-d'),
                    'endDate' => now()->format('Y-m-d'),
                ],
            ],
            'metrics' => [
                ['name' => 'activeUsers'],
                ['name' => 'sessions'],
                ['name' => 'screenPageViews'],
                ['name' => 'averageSessionDuration'],
                ['name' => 'bounceRate'],
            ],
            'dimensions' => [
                ['name' => 'date'],
            ],
        ];

        try {
            $response = Http::withHeaders([
                'Authorization' => 'Bearer '.$this->settings->google_analytics_api_key,
                'Content-Type' => 'application/json',
            ])->post($this->baseUrl.'/properties:'.$this->getPropertyId().':runReport', $defaultParams);

            if ($response->successful()) {
                return $response->json();
            }

            Log::warning('Google Analytics API error', [
                'status' => $response->status(),
                'body' => $response->body(),
            ]);

        } catch (\Exception $e) {
            Log::error('Google Analytics API exception', [
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
            'property' => $this->getPropertyId(),
            'dateRanges' => [
                [
                    'startDate' => now()->subDays(30)->format('Y-m-d'),
                    'endDate' => now()->format('Y-m-d'),
                ],
            ],
            'metrics' => [
                ['name' => 'activeUsers'],
                ['name' => 'sessions'],
            ],
            'dimensions' => [
                ['name' => 'sessionSource'],
            ],
            'limit' => 20,
        ];

        try {
            $response = Http::withHeaders([
                'Authorization' => 'Bearer '.$this->settings->google_analytics_api_key,
                'Content-Type' => 'application/json',
            ])->post($this->baseUrl.'/properties:'.$this->getPropertyId().':runReport', $defaultParams);

            if ($response->successful()) {
                return $response->json();
            }

        } catch (\Exception $e) {
            Log::error('Google Analytics traffic sources error', [
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
            'property' => $this->getPropertyId(),
            'dateRanges' => [
                [
                    'startDate' => now()->subDays(30)->format('Y-m-d'),
                    'endDate' => now()->format('Y-m-d'),
                ],
            ],
            'metrics' => [
                ['name' => 'screenPageViews'],
                ['name' => 'activeUsers'],
            ],
            'dimensions' => [
                ['name' => 'pageTitle'],
                ['name' => 'pagePath'],
            ],
            'limit' => 20,
            'orderBys' => [
                [
                    'metric' => ['metricName' => 'screenPageViews'],
                    'desc' => true,
                ],
            ],
        ];

        try {
            $response = Http::withHeaders([
                'Authorization' => 'Bearer '.$this->settings->google_analytics_api_key,
                'Content-Type' => 'application/json',
            ])->post($this->baseUrl.'/properties:'.$this->getPropertyId().':runReport', $defaultParams);

            if ($response->successful()) {
                return $response->json();
            }

        } catch (\Exception $e) {
            Log::error('Google Analytics popular pages error', [
                'message' => $e->getMessage(),
            ]);
        }

        return [];
    }

    /**
     * Получить конверсии и события
     */
    public function getConversions(array $params = []): array
    {
        $defaultParams = [
            'property' => $this->getPropertyId(),
            'dateRanges' => [
                [
                    'startDate' => now()->subDays(30)->format('Y-m-d'),
                    'endDate' => now()->format('Y-m-d'),
                ],
            ],
            'metrics' => [
                ['name' => 'eventCount'],
                ['name' => 'conversions'],
            ],
            'dimensions' => [
                ['name' => 'eventName'],
            ],
            'limit' => 20,
        ];

        try {
            $response = Http::withHeaders([
                'Authorization' => 'Bearer '.$this->settings->google_analytics_api_key,
                'Content-Type' => 'application/json',
            ])->post($this->baseUrl.'/properties:'.$this->getPropertyId().':runReport', $defaultParams);

            if ($response->successful()) {
                return $response->json();
            }

        } catch (\Exception $e) {
            Log::error('Google Analytics conversions error', [
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
        if (! $this->settings->isGoogleEnabled()) {
            return '';
        }

        $trackingId = $this->settings->google_analytics_id;
        $gtmId = $this->settings->google_tag_manager_id;

        $scripts = [];

        // Google Tag Manager
        if ($this->settings->isTagManagerEnabled() && $gtmId) {
            $scripts[] = '<!-- Google Tag Manager -->';
            $scripts[] = "<script>(function(w,d,s,l,i){w[l]=w[l]||[];w[l].push({'gtm.start':";
            $scripts[] = "new Date().getTime(),event:'gtm.js'});var f=d.getElementsByTagName(s)[0],";
            $scripts[] = "j=d.createElement(s),dl=l!='dataLayer'?'&l='+l:'';j.async=true;j.src=";
            $scripts[] = "'https://www.googletagmanager.com/gtm.js?id='+i+dl;f.parentNode.insertBefore(j,f);";
            $scripts[] = "})(window,document,'script','dataLayer','{$gtmId}');</script>";
            $scripts[] = '<!-- End Google Tag Manager -->';
        }

        // Google Analytics 4
        if ($trackingId) {
            $scripts[] = '<!-- Google Analytics -->';
            $scripts[] = "<script async src='https://www.googletagmanager.com/gtag/js?id={$trackingId}'></script>";
            $scripts[] = '<script>';
            $scripts[] = 'window.dataLayer = window.dataLayer || [];';
            $scripts[] = 'function gtag(){dataLayer.push(arguments);}';
            $scripts[] = "gtag('js', new Date());";
            $scripts[] = "gtag('config', '{$trackingId}');";

            if ($this->settings->enhanced_ecommerce) {
                $scripts[] = "gtag('config', '{$trackingId}', {";
                $scripts[] = "  'enhanced_ecommerce': true,";
                $scripts[] = "  'custom_map': {'custom_parameter_1': 'dimension1'}";
                $scripts[] = '});';
            }

            $scripts[] = '</script>';

            if ($this->settings->isTagManagerEnabled() && $gtmId) {
                $scripts[] = '<!-- Google Tag Manager (noscript) -->';
                $scripts[] = "<noscript><iframe src=\"https://www.googletagmanager.com/ns.html?id={$gtmId}\"";
                $scripts[] = 'height="0" width="0" style="display:none;visibility:hidden"></iframe></noscript>';
                $scripts[] = '<!-- End Google Tag Manager (noscript) -->';
            }
        }

        // Custom event tracking
        if ($this->settings->custom_events_enabled) {
            $scripts[] = '';
            $scripts[] = '<!-- Custom event tracking -->';
            $scripts[] = '<script>';
            $scripts[] = '// Отслеживание отправки форм';
            $scripts[] = "document.addEventListener('form:submit', function(e) {";
            $scripts[] = "    gtag('event', 'form_submit', {";
            $scripts[] = "        'event_category': 'engagement',";
            $scripts[] = "        'event_label': e.detail.formType";
            $scripts[] = '    });';
            $scripts[] = '});';
            $scripts[] = '';
            $scripts[] = '// Отслеживание кликов';
            $scripts[] = "document.addEventListener('click', function(e) {";
            $scripts[] = "    if (e.target.matches('a[href^=\"tel:\"]')) {";
            $scripts[] = "        gtag('event', 'click', {";
            $scripts[] = "            'event_category': 'engagement',";
            $scripts[] = "            'event_label': 'phone_click'";
            $scripts[] = '        });';
            $scripts[] = '    }';
            $scripts[] = "    if (e.target.matches('a[href*=\"telegram\"]')) {";
            $scripts[] = "        gtag('event', 'click', {";
            $scripts[] = "            'event_category': 'engagement',";
            $scripts[] = "            'event_label': 'telegram_click'";
            $scripts[] = '        });';
            $scripts[] = '    }';
            $scripts[] = "    if (e.target.matches('a[href*=\"whatsapp\"]')) {";
            $scripts[] = "        gtag('event', 'click', {";
            $scripts[] = "            'event_category': 'engagement',";
            $scripts[] = "            'event_label': 'whatsapp_click'";
            $scripts[] = '        });';
            $scripts[] = '    }';
            $scripts[] = '});';
            $scripts[] = '</script>';
        }

        return implode(PHP_EOL, $scripts);
    }

    /**
     * Отправить событие в Google Analytics
     */
    public function trackEvent(string $eventName, array $params = []): void
    {
        if (! $this->settings->isGoogleEnabled() || ! $this->settings->custom_events_enabled) {
            return;
        }

        // В реальном проекте здесь можно добавить отправку событий через Measurement Protocol
        Log::info('Google Analytics event tracked', [
            'event' => $eventName,
            'params' => $params,
        ]);
    }

    /**
     * Получить Property ID из tracking ID
     */
    private function getPropertyId(): string
    {
        // Извлекаем Property ID из G-XXXXXXXXXX
        $trackingId = $this->settings->google_analytics_id;
        if (preg_match('/^G-(\w+)$/', $trackingId, $matches)) {
            return $matches[1];
        }

        return '';
    }
}
