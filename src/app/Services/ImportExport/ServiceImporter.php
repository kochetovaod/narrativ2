<?php

namespace App\Services\ImportExport;

use App\Models\PortfolioCase;
use App\Models\Service;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class ServiceImporter extends ImportExportService
{
    protected string $model = Service::class;

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

            // Поиск существующей записи
            $existingService = $this->findExistingService($data, $operationType);

            if ($existingService) {
                // Обновление существующей записи
                if (in_array($operationType, ['update', 'upsert'])) {
                    $existingService->update($data);
                    $service = $existingService;
                    $this->addWarning("Обновлена услуга: {$data['title']} (ID: {$service->id})");
                } else {
                    $this->addError("Строка {$rowNumber}: Услуга с slug '{$data['slug']}' уже существует");
                    DB::rollBack();

                    return;
                }
            } else {
                // Создание новой записи
                if (in_array($operationType, ['create', 'upsert'])) {
                    $data['preview_token'] = Str::random(32);
                    $service = Service::create($data);
                    $this->addWarning("Создана услуга: {$data['title']} (ID: {$service->id})");
                } else {
                    $this->addError("Строка {$rowNumber}: Услуга с slug '{$data['slug']}' не найдена для обновления");
                    DB::rollBack();

                    return;
                }
            }

            // Обработка связей
            $this->processRelations($service, $data);

            DB::commit();

        } catch (\Exception $e) {
            DB::rollBack();
            $this->addError("Строка {$rowNumber}: ".$e->getMessage());
        }
    }

    protected function getEntityType(): string
    {
        return 'services';
    }

    protected function getRequiredHeaders(): array
    {
        return [
            'title',
            'slug',
            'content',
            'status',
        ];
    }

    protected function getExportHeaders(): array
    {
        return [
            'id',
            'title',
            'slug',
            'content',
            'show_cases',
            'portfolio_cases',
            'status',
            'seo_title',
            'seo_description',
            'seo_h1',
            'published_at',
            'created_at',
        ];
    }

    protected function getDataForExport(array $filters): array
    {
        $query = Service::with(['portfolioCases']);

        // Применяем фильтры
        if (isset($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if (isset($filters['date_from'])) {
            $query->whereDate('created_at', '>=', $filters['date_from']);
        }

        if (isset($filters['date_to'])) {
            $query->whereDate('created_at', '<=', $filters['date_to']);
        }

        $services = $query->orderBy('id')->get();

        $data = [];
        foreach ($services as $service) {
            $data[] = [
                $service->id,
                $service->title,
                $service->slug,
                json_encode($service->content, JSON_UNESCAPED_UNICODE),
                $service->show_cases ? '1' : '0',
                $service->portfolioCases->pluck('id')->implode(','),
                $service->status,
                $service->seo['title'] ?? '',
                $service->seo['description'] ?? '',
                $service->seo['h1'] ?? '',
                $service->published_at?->format('Y-m-d H:i:s'),
                $service->created_at->format('Y-m-d H:i:s'),
            ];
        }

        return $data;
    }

    protected function getSampleData(): array
    {
        return [
            [
                'title' => 'Пример услуги 1',
                'slug' => 'service-1',
                'content' => '{"blocks":[{"type":"text","content":"Текст услуги"}]}',
                'show_cases' => '1',
                'portfolio_cases' => '1,2,3',
                'status' => 'published',
                'seo_title' => 'SEO заголовок',
                'seo_description' => 'SEO описание',
                'seo_h1' => 'H1 заголовок',
                'published_at' => '2025-01-01',
            ],
        ];
    }

    protected function getFieldDescriptions(): array
    {
        return [
            'title' => 'Название услуги (обязательное)',
            'slug' => 'URL-идентификатор (обязательное, уникальное)',
            'content' => 'Контент в формате JSON',
            'show_cases' => 'Показывать кейсы (1 или 0)',
            'portfolio_cases' => 'ID кейсов портфолио через запятую',
            'status' => 'Статус: draft, published, archived',
            'seo_title' => 'SEO заголовок',
            'seo_description' => 'SEO описание',
            'seo_h1' => 'H1 заголовок',
            'published_at' => 'Дата публикации (YYYY-MM-DD)',
        ];
    }

    public function getRelatedModels(): array
    {
        return [
            'portfolio_cases' => PortfolioCase::class,
        ];
    }

    /**
     * Подготовка данных для сохранения
     */
    protected function prepareData(array $row): array
    {
        $data = [
            'title' => $this->csvProcessor->normalizeString($row['title'] ?? ''),
            'slug' => $this->csvProcessor->normalizeString($row['slug'] ?? ''),
            'status' => $this->csvProcessor->normalizeString($row['status'] ?? 'draft'),
            'show_cases' => $this->csvProcessor->parseBoolean($row['show_cases'] ?? '0'),
            'published_at' => $this->csvProcessor->parseDate($row['published_at'] ?? null),
        ];

        // Обработка контента
        $content = $this->csvProcessor->parseJson($row['content'] ?? null, []);
        $data['content'] = $content;

        // Обработка SEO
        $seo = [];
        if (! empty($row['seo_title'])) {
            $seo['title'] = $this->csvProcessor->normalizeString($row['seo_title']);
        }
        if (! empty($row['seo_description'])) {
            $seo['description'] = $this->csvProcessor->normalizeString($row['seo_description']);
        }
        if (! empty($row['seo_h1'])) {
            $seo['h1'] = $this->csvProcessor->normalizeString($row['seo_h1']);
        }
        $data['seo'] = $seo;

        // Генерация slug если не указан
        if (empty($data['slug'])) {
            $data['slug'] = Str::slug($data['title']);
        }

        return $data;
    }

    /**
     * Валидация строки данных
     */
    protected function validateRow(array $data, int $rowNumber): array
    {
        $errors = [];

        // Обязательные поля
        if (empty($data['title'])) {
            $errors[] = "Строка {$rowNumber}: Название услуги обязательно для заполнения";
        }

        if (empty($data['slug'])) {
            $errors[] = "Строка {$rowNumber}: Slug обязателен для заполнения";
        }

        if (empty($data['content'])) {
            $errors[] = "Строка {$rowNumber}: Контент услуги обязателен для заполнения";
        }

        // Валидация статуса
        if (! in_array($data['status'], ['draft', 'published', 'archived'])) {
            $errors[] = "Строка {$rowNumber}: Недопустимый статус. Допустимые значения: draft, published, archived";
        }

        // Валидация уникальности slug
        if (! empty($data['slug'])) {
            $existingService = Service::where('slug', $data['slug'])->first();
            if ($existingService) {
                $errors[] = "Строка {$rowNumber}: Услуга с slug '{$data['slug']}' уже существует (ID: {$existingService->id})";
            }
        }

        return $errors;
    }

    /**
     * Поиск существующей услуги
     */
    protected function findExistingService(array $data, string $operationType): ?Service
    {
        if (! in_array($operationType, ['update', 'upsert'])) {
            return null;
        }

        // Поиск по slug
        if (! empty($data['slug'])) {
            return Service::where('slug', $data['slug'])->first();
        }

        // Поиск по ID если указан
        if (! empty($data['id'])) {
            return Service::find($data['id']);
        }

        return null;
    }

    /**
     * Обработка связей
     */
    protected function processRelations(Service $service, array $data): void
    {
        // Связь с кейсами портфолио
        if (isset($data['portfolio_cases'])) {
            $caseIds = $this->csvProcessor->parseIdArray($data['portfolio_cases']);
            if (! empty($caseIds)) {
                // Проверяем существование кейсов
                $existingCases = PortfolioCase::whereIn('id', $caseIds)->pluck('id')->toArray();
                if (count($existingCases) !== count($caseIds)) {
                    $missingCases = array_diff($caseIds, $existingCases);
                    $this->addWarning("Услуга ID {$service->id}: Не найдены кейсы с ID: ".implode(', ', $missingCases));
                }
                $service->portfolioCases()->sync($existingCases);
            }
        }
    }
}
