<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Form;
use App\Services\LeadService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\RateLimiter;

class FormController extends Controller
{
    public function __construct(
        private LeadService $leadService
    ) {}

    /**
     * Обработка отправки формы
     */
    public function submit(Request $request, string $formCode): JsonResponse
    {
        try {
            // Проверка rate limiting
            $key = 'form_submit_'.$request->ip();
            if (RateLimiter::tooManyAttempts($key, 5)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Слишком много запросов. Попробуйте позже.',
                ], 429);
            }

            RateLimiter::hit($key, 60); // 5 попыток в минуту

            // Находим форму
            $form = Form::where('code', $formCode)
                ->where('is_active', true)
                ->with('fields')
                ->first();

            if (! $form) {
                return response()->json([
                    'success' => false,
                    'message' => 'Форма не найдена или неактивна.',
                ], 404);
            }

            // Валидация данных формы
            $validatedData = $this->validateFormData($request, $form);

            // Проверка капчи (если настроена)
            if (! $this->validateCaptcha($request, $form)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Ошибка проверки безопасности.',
                ], 400);
            }

            // Создание заявки
            $lead = $this->leadService->createLead([
                'form_code' => $formCode,
                'payload' => $validatedData,
                'source_url' => $request->input('source_url'),
                'page_title' => $request->input('page_title'),
                'utm' => $this->extractUtmParams($request),
                'consent_given' => $request->input('consent_given', false),
                'consent_doc_url' => $request->input('consent_doc_url'),
                'consent_at' => $request->input('consent_given') ? now() : null,
            ]);

            // Отправка уведомлений
            $this->leadService->sendNotifications($lead);

            return response()->json([
                'success' => true,
                'message' => 'Заявка успешно отправлена!',
                'lead_id' => $lead->id,
            ]);

        } catch (\Exception $e) {
            Log::error('Form submission error', [
                'form_code' => $formCode,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Произошла ошибка при отправке формы. Попробуйте позже.',
            ], 500);
        }
    }

    /**
     * Предварительный просмотр формы
     */
    public function preview(string $formCode): JsonResponse
    {
        $form = Form::where('code', $formCode)
            ->where('is_active', true)
            ->with('fields')
            ->first();

        if (! $form) {
            return response()->json([
                'success' => false,
                'message' => 'Форма не найдена.',
            ], 404);
        }

        $formData = [
            'id' => $form->id,
            'title' => $form->title,
            'code' => $form->code,
            'captcha_mode' => $form->captcha_mode,
            'fields' => $form->fields->sortBy('sort')->map(function ($field) {
                return [
                    'key' => $field->key,
                    'label' => $field->label,
                    'type' => $field->type,
                    'mask' => $field->mask,
                    'is_required' => $field->is_required,
                    'options' => $field->options,
                    'validation_rules' => $field->validation_rules,
                ];
            })->values(),
        ];

        return response()->json([
            'success' => true,
            'form' => $formData,
        ]);
    }

    /**
     * Валидация данных формы
     */
    private function validateFormData(Request $request, Form $form): array
    {
        $rules = [];
        $messages = [];
        $validatedData = [];

        foreach ($form->fields->sortBy('sort') as $field) {
            $fieldKey = $field->key;
            $fieldRules = [];

            // Обязательность поля
            if ($field->is_required) {
                $fieldRules[] = 'required';
            } else {
                $fieldRules[] = 'nullable';
            }

            // Дополнительные правила валидации
            if ($field->validation_rules) {
                $customRules = explode('|', $field->validation_rules);
                $fieldRules = array_merge($fieldRules, $customRules);
            }

            // Специфичная валидация по типу поля
            switch ($field->type) {
                case 'email':
                    $fieldRules[] = 'email';
                    break;
                case 'tel':
                    $fieldRules[] = 'regex:/^[\+]?[0-9\s\-\(\)]+$/';
                    break;
            }

            $rules[$fieldKey] = implode('|', $fieldRules);
            $messages["$fieldKey.required"] = "Поле '{$field->label}' обязательно для заполнения.";
            $messages["$fieldKey.email"] = "Поле '{$field->label}' должно быть корректным email адресом.";
        }

        // Валидация согласия на обработку ПДн
        if ($this->requiresConsent($form)) {
            $rules['consent_given'] = 'required|accepted';
            $messages['consent_given.required'] = 'Необходимо согласие на обработку персональных данных.';
            $messages['consent_given.accepted'] = 'Необходимо согласие на обработку персональных данных.';
        }

        // Дополнительные поля
        $rules['source_url'] = 'nullable|url';
        $rules['page_title'] = 'nullable|string|max:255';

        // Проверяем валидацию
        $validatedData = $request->validate($rules, $messages);

        // Извлекаем только данные полей формы (исключая служебные поля)
        $formData = [];
        foreach ($form->fields as $field) {
            if (array_key_exists($field->key, $validatedData)) {
                $formData[$field->key] = $validatedData[$field->key];
            }
        }

        // Добавляем служебные поля
        $formData['_form_title'] = $form->title;
        $formData['_submitted_at'] = now()->toISOString();

        return $formData;
    }

    /**
     * Проверка капчи
     */
    private function validateCaptcha(Request $request, Form $form): bool
    {
        switch ($form->captcha_mode) {
            case 'recaptcha':
                return $this->validateReCaptcha($request);
            case 'hcaptcha':
                return $this->validateHCaptcha($request);
            case 'none':
            default:
                return true;
        }
    }

    /**
     * Проверка Google reCAPTCHA
     */
    private function validateReCaptcha(Request $request): bool
    {
        $token = $request->input('g-recaptcha-response');

        if (! $token) {
            return false;
        }

        // TODO: Реализовать проверку через Google API
        // Здесь должна быть интеграция с Google reCAPTCHA API

        return true; // Заглушка
    }

    /**
     * Проверка hCaptcha
     */
    private function validateHCaptcha(Request $request): bool
    {
        $token = $request->input('h-captcha-response');

        if (! $token) {
            return false;
        }

        // TODO: Реализовать проверку через hCaptcha API

        return true; // Заглушка
    }

    /**
     * Проверка необходимости согласия на обработку ПДн
     */
    private function requiresConsent(Form $form): bool
    {
        // Если в форме есть поле типа checkbox с ключом consent_given
        return $form->fields->where('key', 'consent_given')->isNotEmpty();
    }

    /**
     * Извлечение UTM параметров
     */
    private function extractUtmParams(Request $request): array
    {
        $utmKeys = ['utm_source', 'utm_medium', 'utm_campaign', 'utm_term', 'utm_content'];
        $utmData = [];

        foreach ($utmKeys as $key) {
            $value = $request->input($key);
            if ($value) {
                $utmData[$key] = $value;
            }
        }

        return $utmData;
    }
}
