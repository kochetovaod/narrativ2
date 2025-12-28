<?php

namespace App\Services\ImportExport;

use App\Models\Lead;
use App\Models\LeadDedupIndex;
use Illuminate\Support\Facades\DB;

class LeadImporter extends ImportExportService
{
    protected string $model = Lead::class;

    protected function processRow(array $row, string $operationType, int $rowNumber): void
    {
        // Подготовка данных
        $data = $this->prepareData($row);

        // Валидация данных
        $validationErrors = $this->validateRow($data, $rowNumber);
        if (! empty($validationErrors)) {
            foreach ($validationErrors as $error) {
                $this->addError($error);
            }

            return;
        }

        try {
            DB::beginTransaction();

            // Проверка дедупликации
            if ($this->shouldSkipDueToDedup($data)) {
                $this->addWarning("Строка {$rowNumber}: Заявка пропущена из-за дедупликации (email: {$data['email']}, phone: {$data['phone']})");
                DB::rollBack();

                return;
            }

            // Поиск существующей записи
            $existingLead = $this->findExistingLead($data, $operationType);

            if ($existingLead) {
                // Обновление существующей записи
                if (in_array($operationType, ['update', 'upsert'])) {
                    $existingLead->update($data);
                    $lead = $existingLead;
                    $this->addWarning("Обновлена заявка: {$data['email']} (ID: {$lead->id})");
                } else {
                    $this->addError("Строка {$rowNumber}: Заявка с email '{$data['email']}' уже существует");
                    DB::rollBack();

                    return;
                }
            } else {
                // Создание новой записи
                if (in_array($operationType, ['create', 'upsert'])) {
                    $lead = Lead::create($data);
                    $this->addWarning("Создана заявка: {$data['email']} (ID: {$lead->id})");
                } else {
                    $this->addError("Строка {$rowNumber}: Заявка с email '{$data['email']}' не найдена для обновления");
                    DB::rollBack();

                    return;
                }
            }

            // Создание записи дедупликации
            $this->createDedupIndex($lead, $data);

            DB::commit();

        } catch (\Exception $e) {
            DB::rollBack();
            $this->addError("Строка {$rowNumber}: ".$e->getMessage());
        }
    }

    protected function getEntityType(): string
    {
        return 'leads';
    }

    protected function getRequiredHeaders(): array
    {
        return [
            'form_code',
            'email',
            'phone',
        ];
    }

    protected function getExportHeaders(): array
    {
        return [
            'id',
            'form_code',
            'status',
            'name',
            'email',
            'phone',
            'payload',
            'source_url',
            'page_title',
            'utm_source',
            'utm_medium',
            'utm_campaign',
            'utm_term',
            'utm_content',
            'consent_given',
            'consent_doc_url',
            'manager_comment',
            'created_at',
        ];
    }

    protected function getDataForExport(array $filters): array
    {
        $query = Lead::query();

        // Применяем фильтры
        if (isset($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if (isset($filters['form_code'])) {
            $query->where('form_code', $filters['form_code']);
        }

        if (isset($filters['date_from'])) {
            $query->whereDate('created_at', '>=', $filters['date_from']);
        }

        if (isset($filters['date_to'])) {
            $query->whereDate('created_at', '<=', $filters['date_to']);
        }

        if (isset($filters['utm_source'])) {
            $query->where('utm->source', $filters['utm_source']);
        }

        $leads = $query->orderBy('id')->get();

        $data = [];
        foreach ($leads as $lead) {
            $data[] = [
                $lead->id,
                $lead->form_code,
                $lead->status,
                $lead->payload['name'] ?? '',
                $lead->email,
                $lead->phone,
                json_encode($lead->payload, JSON_UNESCAPED_UNICODE),
                $lead->source_url,
                $lead->page_title,
                $lead->utm['source'] ?? '',
                $lead->utm['medium'] ?? '',
                $lead->utm['campaign'] ?? '',
                $lead->utm['term'] ?? '',
                $lead->utm['content'] ?? '',
                $lead->consent_given ? '1' : '0',
                $lead->consent_doc_url,
                $lead->manager_comment,
                $lead->created_at->format('Y-m-d H:i:s'),
            ];
        }

        return $data;
    }

    protected function getSampleData(): array
    {
        return [
            [
                'form_code' => 'contact_form',
                'status' => 'new',
                'name' => 'Иван Петров',
                'email' => 'test@example.com',
                'phone' => '+79001234567',
                'payload' => '{"name":"Иван Петров","message":"Привет"}',
                'source_url' => 'https://site.ru',
                'page_title' => 'Главная',
                'utm_source' => 'google',
                'utm_medium' => 'cpc',
                'utm_campaign' => 'campaign1',
                'utm_term' => 'keyword1',
                'utm_content' => 'content1',
                'consent_given' => '1',
                'consent_doc_url' => 'https://site.ru/privacy',
                'created_at' => '2025-01-01 12:00:00',
            ],
        ];
    }

    protected function getFieldDescriptions(): array
    {
        return [
            'form_code' => 'Код формы (обязательное)',
            'status' => 'Статус: new, in_progress, closed (обязательное)',
            'name' => 'Имя клиента',
            'email' => 'Email (обязательное, уникальное)',
            'phone' => 'Телефон (обязательное)',
            'payload' => 'Дополнительные данные в формате JSON',
            'source_url' => 'URL источника',
            'page_title' => 'Заголовок страницы',
            'utm_source' => 'UTM источник',
            'utm_medium' => 'UTM медиум',
            'utm_campaign' => 'UTM кампания',
            'utm_term' => 'UTM термин',
            'utm_content' => 'UTM контент',
            'consent_given' => 'Согласие на обработку ПДн (1 или 0)',
            'consent_doc_url' => 'URL документа согласия',
            'manager_comment' => 'Комментарий менеджера',
            'created_at' => 'Дата создания (YYYY-MM-DD HH:MM:SS)',
        ];
    }

    public function getRelatedModels(): array
    {
        return [];
    }

    /**
     * Подготовка данных для сохранения
     */
    protected function prepareData(array $row): array
    {
        $data = [
            'form_code' => $this->csvProcessor->normalizeString($row['form_code'] ?? ''),
            'status' => $this->csvProcessor->normalizeString($row['status'] ?? 'new'),
            'email' => $this->csvProcessor->normalizeString($row['email'] ?? ''),
            'phone' => $this->csvProcessor->normalizeString($row['phone'] ?? ''),
            'source_url' => $this->csvProcessor->normalizeString($row['source_url'] ?? ''),
            'page_title' => $this->csvProcessor->normalizeString($row['page_title'] ?? ''),
            'consent_given' => $this->csvProcessor->parseBoolean($row['consent_given'] ?? '0'),
            'consent_doc_url' => $this->csvProcessor->normalizeString($row['consent_doc_url'] ?? ''),
            'manager_comment' => $this->csvProcessor->normalizeString($row['manager_comment'] ?? ''),
            'consent_at' => $this->csvProcessor->parseBoolean($row['consent_given'] ?? '0') ? now() : null,
            'created_at' => $this->csvProcessor->parseDate($row['created_at'] ?? null),
        ];

        // Обработка payload
        $payload = $this->csvProcessor->parseJson($row['payload'] ?? null, []);
        if (! empty($row['name']) && ! isset($payload['name'])) {
            $payload['name'] = $this->csvProcessor->normalizeString($row['name']);
        }
        $data['payload'] = $payload;

        // Обработка UTM параметров
        $utm = [];
        if (! empty($row['utm_source'])) {
            $utm['source'] = $this->csvProcessor->normalizeString($row['utm_source']);
        }
        if (! empty($row['utm_medium'])) {
            $utm['medium'] = $this->csvProcessor->normalizeString($row['utm_medium']);
        }
        if (! empty($row['utm_campaign'])) {
            $utm['campaign'] = $this->csvProcessor->normalizeString($row['utm_campaign']);
        }
        if (! empty($row['utm_term'])) {
            $utm['term'] = $this->csvProcessor->normalizeString($row['utm_term']);
        }
        if (! empty($row['utm_content'])) {
            $utm['content'] = $this->csvProcessor->normalizeString($row['utm_content']);
        }
        $data['utm'] = $utm;

        return $data;
    }

    /**
     * Валидация строки данных
     */
    protected function validateRow(array $data, int $rowNumber): array
    {
        $errors = [];

        // Обязательные поля
        if (empty($data['form_code'])) {
            $errors[] = "Строка {$rowNumber}: Код формы обязателен для заполнения";
        }

        if (empty($data['email'])) {
            $errors[] = "Строка {$rowNumber}: Email обязателен для заполнения";
        }

        if (empty($data['phone'])) {
            $errors[] = "Строка {$rowNumber}: Телефон обязателен для заполнения";
        }

        // Валидация email
        if (! empty($data['email']) && ! filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
            $errors[] = "Строка {$rowNumber}: Некорректный формат email";
        }

        // Валидация статуса
        if (! in_array($data['status'], ['new', 'in_progress', 'closed'])) {
            $errors[] = "Строка {$rowNumber}: Недопустимый статус. Допустимые значения: new, in_progress, closed";
        }

        // Валидация уникальности email для новых записей
        if (! empty($data['email'])) {
            $existingLead = Lead::where('email', $data['email'])->first();
            if ($existingLead) {
                $errors[] = "Строка {$rowNumber}: Заявка с email '{$data['email']}' уже существует (ID: {$existingLead->id})";
            }
        }

        return $errors;
    }

    /**
     * Поиск существующей заявки
     */
    protected function findExistingLead(array $data, string $operationType): ?Lead
    {
        if (! in_array($operationType, ['update', 'upsert'])) {
            return null;
        }

        // Поиск по email
        if (! empty($data['email'])) {
            return Lead::where('email', $data['email'])->first();
        }

        // Поиск по ID если указан
        if (! empty($data['id'])) {
            return Lead::find($data['id']);
        }

        return null;
    }

    /**
     * Проверка дедупликации
     */
    protected function shouldSkipDueToDedup(array $data): bool
    {
        // Проверяем по email
        if (! empty($data['email'])) {
            $existingByEmail = Lead::where('email', $data['email'])->exists();
            if ($existingByEmail) {
                return true;
            }
        }

        // Проверяем по телефону
        if (! empty($data['phone'])) {
            $existingByPhone = Lead::where('phone', $data['phone'])->exists();
            if ($existingByPhone) {
                return true;
            }
        }

        return false;
    }

    /**
     * Создание записи дедупликации
     */
    protected function createDedupIndex(Lead $lead, array $data): void
    {
        $contactKey = !empty($data['email']) ? 'email:' . strtolower($data['email']) : 'phone:' . preg_replace('/\D+/', '', (string) $data['phone']);
        LeadDedupIndex::updateOrCreate(
            ['lead_id' => $lead->id],
            [
                'contact_key' => $contactKey,
                'created_date' => now()->toDateString(),
            ]
        );
    }

    /**
     * Обработка связей (не требуется для заявок)
     */
    protected function processRelations(Lead $lead, array $data): void
    {
        // Ничего не делаем, связи не требуются для заявок
    }
}
