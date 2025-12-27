<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Form;
use App\Models\Lead;
use App\Models\LeadDedupIndex;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class LeadService
{
    /**
     * Создание новой заявки
     */
    public function createLead(array $data): Lead
    {
        // Извлекаем контактные данные для дедупликации
        $phone = $this->extractPhone($data['payload'] ?? []);
        $email = $this->extractEmail($data['payload'] ?? []);
        $consentGiven = (bool) ($data['consent_given'] ?? false);
        $consentAt = $data['consent_at'] ?? ($consentGiven ? now() : null);

        // Создаем контактный ключ для дедупликации
        $contactKey = $this->generateContactKey($phone, $email);

        // Создаем заявку
        $lead = Lead::create([
            'form_code' => $data['form_code'],
            'status' => 'new',
            'phone' => $phone,
            'email' => $email,
            'payload' => $data['payload'],
            'source_url' => $data['source_url'] ?? null,
            'page_title' => $data['page_title'] ?? null,
            'utm' => $data['utm'] ?? [],
            'consent_given' => $consentGiven,
            'consent_doc_url' => $data['consent_doc_url'] ?? null,
            'consent_at' => $consentAt,
        ]);

        // Создаем запись в индексе дедупликации
        $this->createDedupIndex($lead, $contactKey);

        // Логируем создание заявки
        Log::info('Lead created', [
            'lead_id' => $lead->id,
            'form_code' => $lead->form_code,
            'contact_key' => $contactKey,
            'source_url' => $lead->source_url,
        ]);

        return $lead;
    }

    /**
     * Отправка уведомлений о новой заявке
     */
    public function sendNotifications(Lead $lead): void
    {
        $form = Form::where('code', $lead->form_code)->first();

        if (! $form) {
            Log::warning('Form not found for notifications', [
                'lead_id' => $lead->id,
                'form_code' => $lead->form_code,
            ]);

            return;
        }

        // Отправка email уведомлений
        if (! empty($form->notification_email)) {
            foreach ($form->notification_email as $email) {
                if (filter_var($email, FILTER_VALIDATE_EMAIL)) {
                    $this->sendEmailNotification($lead, $email);
                }
            }
        }

        // Отправка Telegram уведомлений
        if (! empty($form->notification_telegram)) {
            foreach ($form->notification_telegram as $chatId) {
                $this->sendTelegramNotification($lead, $chatId);
            }
        }
    }

    /**
     * Создание индекса дедупликации
     */
    private function createDedupIndex(Lead $lead, string $contactKey): void
    {
        // Проверяем, существует ли уже запись с таким контактным ключом
        $existingIndex = LeadDedupIndex::where('contact_key', $contactKey)
            ->whereDate('created_date', today())
            ->first();

        if (! $existingIndex) {
            LeadDedupIndex::create([
                'lead_id' => $lead->id,
                'contact_key' => $contactKey,
                'created_date' => today(),
            ]);
        }
    }

    /**
     * Генерация контактного ключа для дедупликации
     */
    private function generateContactKey(?string $phone, ?string $email): string
    {
        // Если есть и телефон и email, используем оба
        if ($phone && $email) {
            return md5(strtolower($phone).'|'.strtolower($email));
        }

        // Если есть только телефон
        if ($phone) {
            return 'phone:'.$this->normalizePhone($phone);
        }

        // Если есть только email
        if ($email) {
            return 'email:'.strtolower($email);
        }

        // Если нет контактных данных, создаем уникальный ключ
        return 'unknown_'.uniqid();
    }

    /**
     * Извлечение телефона из данных формы
     */
    private function extractPhone(array $payload): ?string
    {
        // Ищем поле с ключом phone
        if (isset($payload['phone']) && $payload['phone']) {
            return $payload['phone'];
        }

        // Ищем поле с ключом tel
        if (isset($payload['tel']) && $payload['tel']) {
            return $payload['tel'];
        }

        return null;
    }

    /**
     * Извлечение email из данных формы
     */
    private function extractEmail(array $payload): ?string
    {
        if (isset($payload['email']) && $payload['email']) {
            return $payload['email'];
        }

        return null;
    }

    /**
     * Нормализация номера телефона
     */
    private function normalizePhone(string $phone): string
    {
        // Удаляем все кроме цифр и плюса
        $normalized = preg_replace('/[^0-9\+]/', '', $phone);

        // Если номер начинается с 8, заменяем на 7 (для российских номеров)
        if (substr($normalized, 0, 1) === '8') {
            $normalized = '7'.substr($normalized, 1);
        }

        // Если номер не начинается с +, добавляем
        if (substr($normalized, 0, 1) !== '+') {
            $normalized = '+'.$normalized;
        }

        return $normalized;
    }

    /**
     * Отправка email уведомления
     */
    private function sendEmailNotification(Lead $lead, string $to): void
    {
        try {
            $form = Form::where('code', $lead->form_code)->first();

            Mail::raw($this->formatEmailMessage($lead), function ($message) use ($to, $lead, $form) {
                $message->to($to);
                $formTitle = $form ? $form->title : $lead->form_code;
                $message->subject("Новая заявка: {$formTitle} #{$lead->id}");
                $message->from(config('mail.from.address'), config('mail.from.name'));
            });

            Log::info('Email notification sent', [
                'lead_id' => $lead->id,
                'to' => $to,
                'form_code' => $lead->form_code,
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to send email notification', [
                'lead_id' => $lead->id,
                'to' => $to,
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Отправка Telegram уведомления
     */
    private function sendTelegramNotification(Lead $lead, string $chatId): void
    {
        try {
            $message = $this->formatTelegramMessage($lead);

            // TODO: Интеграция с Telegram Bot API
            // Здесь должна быть отправка через Telegram Bot API

            Log::info('Telegram notification sent', [
                'lead_id' => $lead->id,
                'chat_id' => $chatId,
                'form_code' => $lead->form_code,
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to send Telegram notification', [
                'lead_id' => $lead->id,
                'chat_id' => $chatId,
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Форматирование сообщения для email
     */
    private function formatEmailMessage(Lead $lead): string
    {
        $message = "Новая заявка с сайта\n\n";
        $message .= "ID заявки: #{$lead->id}\n";
        $message .= "Форма: {$lead->form_code}\n";
        $message .= "Статус: {$lead->status}\n";
        $message .= "Дата: {$lead->created_at}\n\n";

        if ($lead->phone) {
            $message .= "Телефон: {$lead->phone}\n";
        }

        if ($lead->email) {
            $message .= "Email: {$lead->email}\n";
        }

        $message .= "\nДанные формы:\n";
        foreach ($lead->payload as $key => $value) {
            if (! str_starts_with($key, '_')) { // Исключаем служебные поля
                $message .= '• '.ucfirst($key).': '.(is_array($value) ? json_encode($value) : $value)."\n";
            }
        }

        if ($lead->utm) {
            $message .= "\nUTM параметры:\n";
            foreach ($lead->utm as $key => $value) {
                $message .= '• '.ucfirst($key).': '.$value."\n";
            }
        }

        if ($lead->source_url) {
            $message .= "\nИсточник: {$lead->source_url}\n";
        }

        if ($lead->consent_given) {
            $message .= "\nСогласие на обработку ПДн: Да\n";
        }

        if ($lead->consent_doc_url) {
            $message .= "Документ согласия: {$lead->consent_doc_url}\n";
        }

        return $message;
    }

    /**
     * Форматирование сообщения для Telegram
     */
    private function formatTelegramMessage(Lead $lead): string
    {
        $form = Form::where('code', $lead->form_code)->first();
        $formTitle = $form ? $form->title : $lead->form_code;

        $message = "🆕 Новая заявка\n\n";
        $message .= "<b>Форма:</b> {$formTitle}\n";
        $message .= "<b>ID:</b> #{$lead->id}\n";
        $message .= "<b>Статус:</b> {$lead->status}\n";

        if ($lead->phone) {
            $message .= "<b>Телефон:</b> {$lead->phone}\n";
        }

        if ($lead->email) {
            $message .= "<b>Email:</b> {$lead->email}\n";
        }

        $message .= "\n<b>Данные:</b>\n";
        foreach ($lead->payload as $key => $value) {
            if (! str_starts_with($key, '_')) {
                $displayValue = is_array($value) ? json_encode($value) : $value;
                $message .= "• {$key}: {$displayValue}\n";
            }
        }

        if ($lead->source_url) {
            $message .= "\n<b>Источник:</b> {$lead->source_url}\n";
        }

        if ($lead->consent_doc_url) {
            $message .= "\n<b>Документ согласия:</b> {$lead->consent_doc_url}\n";
        }

        return $message;
    }

    /**
     * Получение статистики заявок
     */
    public function getLeadStats(array $filters = []): array
    {
        $query = Lead::query();

        // Применяем фильтры
        if (! empty($filters['date_from'])) {
            $query->whereDate('created_at', '>=', $filters['date_from']);
        }

        if (! empty($filters['date_to'])) {
            $query->whereDate('created_at', '<=', $filters['date_to']);
        }

        if (! empty($filters['form_code'])) {
            $query->where('form_code', $filters['form_code']);
        }

        if (! empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        return [
            'total' => $query->count(),
            'new' => (clone $query)->where('status', 'new')->count(),
            'in_progress' => (clone $query)->where('status', 'in_progress')->count(),
            'closed' => (clone $query)->where('status', 'closed')->count(),
            'by_form' => (clone $query)->groupBy('form_code')->selectRaw('form_code, count(*) as count')->pluck('count', 'form_code')->toArray(),
        ];
    }

    /**
     * Экспорт заявок в CSV
     */
    public function exportToCsv(array $filters = []): string
    {
        $query = Lead::query();

        // Применяем фильтры аналогично getLeadStats
        if (! empty($filters['date_from'])) {
            $query->whereDate('created_at', '>=', $filters['date_from']);
        }

        if (! empty($filters['date_to'])) {
            $query->whereDate('created_at', '<=', $filters['date_to']);
        }

        if (! empty($filters['form_code'])) {
            $query->where('form_code', $filters['form_code']);
        }

        if (! empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        $leads = $query->orderBy('created_at', 'desc')->get();

        // TODO: Реализовать экспорт в CSV формат
        // Здесь должна быть генерация CSV файла

        return 'CSV export placeholder';
    }
}
