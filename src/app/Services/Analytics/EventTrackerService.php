<?php

namespace App\Services\Analytics;

use App\Models\AnalyticsSettings;
use App\Models\TrackingEvent;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class EventTrackerService
{
    private AnalyticsSettings $settings;

    public function __construct()
    {
        $this->settings = AnalyticsSettings::getSettings();
    }

    /**
     * Записать событие
     */
    public function trackEvent(string $eventType, string $eventName, array $data = [], ?Request $request = null): void
    {
        // Проверяем, включен ли трекинг
        if (! $this->settings->custom_events_enabled) {
            return;
        }

        // Проверяем IP на исключение
        $ip = $request ? $request->ip() : request()->ip();
        if ($this->settings->isIpExcluded($ip)) {
            return;
        }

        try {
            TrackingEvent::create([
                'event_type' => $eventType,
                'event_name' => $eventName,
                'data' => $data,
                'ip_address' => $ip,
                'user_agent' => $request ? $request->userAgent() : request()->userAgent(),
                'session_id' => $request ? $request->session()->getId() : session()->getId(),
                'page_url' => $request ? $request->fullUrl() : request()->fullUrl(),
                'referer' => $request ? $request->header('referer') : request()->header('referer'),
                'utm' => $this->extractUtmParameters($request),
                'created_at' => now(),
            ]);

            // Отправляем в внешние системы аналитики
            $this->sendToExternalServices($eventName, $data);

        } catch (\Exception $e) {
            Log::error('Event tracking error', [
                'event_type' => $eventType,
                'event_name' => $eventName,
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Отследить отправку формы
     */
    public function trackFormSubmit(string $formType, array $formData = [], ?Request $request = null): void
    {
        $eventData = [
            'form_type' => $formType,
            'fields_count' => count($formData),
            'has_email' => ! empty($formData['email']),
            'has_phone' => ! empty($formData['phone']),
            'timestamp' => now()->timestamp,
        ];

        $this->trackEvent('form_submit', 'form_submit', $eventData, $request);

        // Специальные события для разных типов форм
        match ($formType) {
            'callback' => $this->trackEvent('conversion', 'form_callback', $eventData, $request),
            'calc' => $this->trackEvent('conversion', 'form_calc', $eventData, $request),
            'question' => $this->trackEvent('conversion', 'form_question', $eventData, $request),
            default => null,
        };
    }

    /**
     * Отследить клик по телефону
     */
    public function trackPhoneClick(string $phoneNumber, ?Request $request = null): void
    {
        $eventData = [
            'phone_number' => $phoneNumber,
            'click_timestamp' => now()->timestamp,
        ];

        $this->trackEvent('click', 'click_tel', $eventData, $request);
    }

    /**
     * Отследить клик по Telegram
     */
    public function trackTelegramClick(string $telegramHandle, ?Request $request = null): void
    {
        $eventData = [
            'telegram_handle' => $telegramHandle,
            'click_timestamp' => now()->timestamp,
        ];

        $this->trackEvent('click', 'click_telegram', $eventData, $request);
    }

    /**
     * Отследить клик по WhatsApp
     */
    public function trackWhatsAppClick(string $phoneNumber, ?Request $request = null): void
    {
        $eventData = [
            'phone_number' => $phoneNumber,
            'click_timestamp' => now()->timestamp,
        ];

        $this->trackEvent('click', 'click_whatsapp', $eventData, $request);
    }

    /**
     * Отследить просмотр страницы
     */
    public function trackPageView(string $pageTitle, string $pageUrl, ?Request $request = null): void
    {
        $eventData = [
            'page_title' => $pageTitle,
            'page_url' => $pageUrl,
            'view_timestamp' => now()->timestamp,
        ];

        $this->trackEvent('page_view', 'page_view', $eventData, $request);
    }

    /**
     * Отследить начало заполнения формы
     */
    public function trackFormStart(string $formType, ?Request $request = null): void
    {
        $eventData = [
            'form_type' => $formType,
            'start_timestamp' => now()->timestamp,
        ];

        $this->trackEvent('form_interaction', 'form_start', $eventData, $request);
    }

    /**
     * Отследить бросание формы
     */
    public function trackFormAbandon(string $formType, int $fieldsFilled, ?Request $request = null): void
    {
        $eventData = [
            'form_type' => $formType,
            'fields_filled' => $fieldsFilled,
            'abandon_timestamp' => now()->timestamp,
        ];

        $this->trackEvent('form_interaction', 'form_abandon', $eventData, $request);
    }

    /**
     * Отследить глубину скролла
     */
    public function trackScrollDepth(int $scrollPercentage, ?Request $request = null): void
    {
        $eventData = [
            'scroll_percentage' => $scrollPercentage,
            'scroll_timestamp' => now()->timestamp,
        ];

        $this->trackEvent('engagement', 'scroll_depth', $eventData, $request);
    }

    /**
     * Отследить время на странице
     */
    public function trackTimeOnPage(int $secondsSpent, ?Request $request = null): void
    {
        $eventData = [
            'seconds_spent' => $secondsSpent,
            'time_timestamp' => now()->timestamp,
        ];

        $this->trackEvent('engagement', 'time_on_page', $eventData, $request);
    }

    /**
     * Получить статистику событий за период
     */
    public function getEventStats(\DateTime $startDate, \DateTime $endDate, ?string $eventType = null): array
    {
        $query = TrackingEvent::whereBetween('created_at', [$startDate, $endDate]);

        if ($eventType) {
            $query->where('event_type', $eventType);
        }

        return [
            'total_events' => $query->count(),
            'events_by_type' => $query->selectRaw('event_type, count(*) as count')
                ->groupBy('event_type')
                ->pluck('count', 'event_type')
                ->toArray(),
            'events_by_name' => $query->selectRaw('event_name, count(*) as count')
                ->groupBy('event_name')
                ->orderByDesc('count')
                ->limit(10)
                ->pluck('count', 'event_name')
                ->toArray(),
            'unique_visitors' => $query->distinct('session_id')->count('session_id'),
            'conversion_rate' => $this->calculateConversionRate($startDate, $endDate),
        ];
    }

    /**
     * Получить статистику форм
     */
    public function getFormStats(\DateTime $startDate, \DateTime $endDate): array
    {
        $formEvents = TrackingEvent::whereBetween('created_at', [$startDate, $endDate])
            ->whereIn('event_name', ['form_submit', 'form_start', 'form_abandon'])
            ->get();

        $stats = [
            'form_submits' => 0,
            'form_starts' => 0,
            'form_abandons' => 0,
            'forms_by_type' => [],
            'completion_rate' => 0,
        ];

        foreach ($formEvents as $event) {
            $formType = $event->data['form_type'] ?? 'unknown';

            switch ($event->event_name) {
                case 'form_submit':
                    $stats['form_submits']++;
                    break;
                case 'form_start':
                    $stats['form_starts']++;
                    break;
                case 'form_abandon':
                    $stats['form_abandons']++;
                    break;
            }

            if (! isset($stats['forms_by_type'][$formType])) {
                $stats['forms_by_type'][$formType] = ['submits' => 0, 'starts' => 0, 'abandons' => 0];
            }

            $stats['forms_by_type'][$formType][$event->event_name === 'form_submit' ? 'submits' : ($event->event_name === 'form_start' ? 'starts' : 'abandons')]++;
        }

        // Рассчитываем коэффициент завершения
        if ($stats['form_starts'] > 0) {
            $stats['completion_rate'] = round(($stats['form_submits'] / $stats['form_starts']) * 100, 2);
        }

        return $stats;
    }

    /**
     * Получить источники трафика
     */
    public function getTrafficSources(\DateTime $startDate, \DateTime $endDate): array
    {
        $events = TrackingEvent::whereBetween('created_at', [$startDate, $endDate])
            ->where('event_type', 'page_view')
            ->selectRaw('referer, count(*) as visits')
            ->whereNotNull('referer')
            ->groupBy('referer')
            ->orderByDesc('visits')
            ->limit(20)
            ->get();

        return $events->map(function ($event) {
            $domain = parse_url($event->referer, PHP_URL_HOST);

            return [
                'source' => $domain,
                'visits' => $event->visits,
            ];
        })->toArray();
    }

    /**
     * Отправить событие в внешние системы
     */
    private function sendToExternalServices(string $eventName, array $data): void
    {
        try {
            // Яндекс.Метрика
            if ($this->settings->isYandexEnabled()) {
                $yandexService = new YandexMetricaService;
                $yandexService->trackEvent($eventName, $data);
            }

            // Google Analytics
            if ($this->settings->isGoogleEnabled()) {
                $googleService = new GoogleAnalyticsService;
                $googleService->trackEvent($eventName, $data);
            }

        } catch (\Exception $e) {
            Log::error('External analytics service error', [
                'event' => $eventName,
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Извлечь UTM параметры из запроса
     */
    private function extractUtmParameters(?Request $request): array
    {
        $utm = [];

        if ($request) {
            $utmKeys = ['utm_source', 'utm_medium', 'utm_campaign', 'utm_term', 'utm_content'];

            foreach ($utmKeys as $key) {
                $value = $request->query($key) ?: $request->input($key);
                if ($value) {
                    $utm[$key] = $value;
                }
            }
        }

        return $utm;
    }

    /**
     * Рассчитать коэффициент конверсии
     */
    private function calculateConversionRate(\DateTime $startDate, \DateTime $endDate): float
    {
        $visitors = TrackingEvent::whereBetween('created_at', [$startDate, $endDate])
            ->where('event_type', 'page_view')
            ->distinct('session_id')
            ->count('session_id');

        $conversions = TrackingEvent::whereBetween('created_at', [$startDate, $endDate])
            ->where('event_type', 'conversion')
            ->count();

        if ($visitors > 0) {
            return round(($conversions / $visitors) * 100, 2);
        }

        return 0;
    }

    /**
     * Очистить старые события
     */
    public function cleanupOldEvents(): int
    {
        $cutoffDate = now()->subDays($this->settings->data_retention_days);

        return TrackingEvent::where('created_at', '<', $cutoffDate)->delete();
    }
}
