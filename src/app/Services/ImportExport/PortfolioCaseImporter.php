<?php

namespace App\Services\ImportExport;

use App\Models\PortfolioCase;
use App\Models\Product;
use App\Models\Service;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class PortfolioCaseImporter extends ImportExportService
{
    protected string $model = PortfolioCase::class;

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
            $existingCase = $this->findExistingCase($data, $operationType);

            if ($existingCase) {
                // Обновление существующей записи
                if (in_array($operationType, ['update', 'upsert'])) {
                    $existingCase->update($data);
                    $portfolioCase = $existingCase;
                    $this->addWarning("Обновлен кейс: {$data['title']} (ID: {$portfolioCase->id})");
                } else {
                    $this->addError("Строка {$rowNumber}: Кейс с slug '{$data['slug']}' уже существует");
                    DB::rollBack();

                    return;
                }
            } else {
                // Создание новой записи
                if (in_array($operationType, ['create', 'upsert'])) {
                    $data['preview_token'] = Str::random(32);
                    $portfolioCase = PortfolioCase::create($data);
                    $this->addWarning("Создан кейс: {$data['title']} (ID: {$portfolioCase->id})");
                } else {
                    $this->addError("Строка {$rowNumber}: Кейс с slug '{$data['slug']}' не найден для обновления");
                    DB::rollBack();

                    return;
                }
            }

            // Обработка связей
            $this->processRelations($portfolioCase, $data);

            DB::commit();

        } catch (\Exception $e) {
            DB::rollBack();
            $this->addError("Строка {$rowNumber}: ".$e->getMessage());
        }
    }

    protected function getEntityType(): string
    {
        return 'portfolio_cases';
    }

    protected function getRequiredHeaders(): array
    {
        return [
            'title',
            'slug',
            'description',
            'status',
        ];
    }

    protected function getExportHeaders(): array
    {
        return [
            'id',
            'title',
            'slug',
            'description',
            'client_name',
            'is_nda',
            'public_client_label',
            'date',
            'products',
            'services',
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
        $query = PortfolioCase::with(['products', 'services']);

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

        if (isset($filters['is_nda'])) {
            $query->where('is_nda', $filters['is_nda']);
        }

        $cases = $query->orderBy('id')->get();

        $data = [];
        foreach ($cases as $case) {
            $data[] = [
                $case->id,
                $case->title,
                $case->slug,
                $case->description,
                $case->client_name,
                $case->is_nda ? '1' : '0',
                $case->public_client_label,
                $case->date?->format('Y-m-d'),
                $case->products->pluck('id')->implode(','),
                $case->services->pluck('id')->implode(','),
                $case->status,
                $case->seo['title'] ?? '',
                $case->seo['description'] ?? '',
                $case->seo['h1'] ?? '',
                $case->published_at?->format('Y-m-d H:i:s'),
                $case->created_at->format('Y-m-d H:i:s'),
            ];
        }

        return $data;
    }

    protected function getSampleData(): array
    {
        return [
            [
                'title' => 'Пример кейса 1',
                'slug' => 'case-1',
                'description' => 'Описание кейса',
                'client_name' => 'ООО Рога',
                'is_nda' => '1',
                'public_client_label' => 'Крупная компания',
                'date' => '2025-01-01',
                'products' => '1,2,3',
                'services' => '1,2,3',
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
            'title' => 'Название кейса (обязательное)',
            'slug' => 'URL-идентификатор (обязательное, уникальное)',
            'description' => 'Описание кейса (обязательное)',
            'client_name' => 'Название клиента',
            'is_nda' => 'NDA флаг (1 или 0)',
            'public_client_label' => 'Публичное название клиента',
            'date' => 'Дата проекта (YYYY-MM-DD)',
            'products' => 'ID товаров через запятую',
            'services' => 'ID услуг через запятую',
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
            'products' => Product::class,
            'services' => Service::class,
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
            'description' => $this->csvProcessor->normalizeString($row['description'] ?? ''),
            'client_name' => $this->csvProcessor->normalizeString($row['client_name'] ?? ''),
            'public_client_label' => $this->csvProcessor->normalizeString($row['public_client_label'] ?? ''),
            'is_nda' => $this->csvProcessor->parseBoolean($row['is_nda'] ?? '0'),
            'status' => $this->csvProcessor->normalizeString($row['status'] ?? 'draft'),
            'published_at' => $this->csvProcessor->parseDate($row['published_at'] ?? null),
            'date' => $this->csvProcessor->parseDate($row['date'] ?? null),
        ];

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
            $errors[] = "Строка {$rowNumber}: Название кейса обязательно для заполнения";
        }

        if (empty($data['slug'])) {
            $errors[] = "Строка {$rowNumber}: Slug обязателен для заполнения";
        }

        if (empty($data['description'])) {
            $errors[] = "Строка {$rowNumber}: Описание кейса обязательно для заполнения";
        }

        // Валидация статуса
        if (! in_array($data['status'], ['draft', 'published', 'archived'])) {
            $errors[] = "Строка {$rowNumber}: Недопустимый статус. Допустимые значения: draft, published, archived";
        }

        // Валидация даты
        if (! empty($data['date']) && ! $data['date']) {
            $errors[] = "Строка {$rowNumber}: Некорректный формат даты проекта";
        }

        // Валидация уникальности slug
        if (! empty($data['slug'])) {
            $existingCase = PortfolioCase::where('slug', $data['slug'])->first();
            if ($existingCase) {
                $errors[] = "Строка {$rowNumber}: Кейс с slug '{$data['slug']}' уже существует (ID: {$existingCase->id})";
            }
        }

        return $errors;
    }

    /**
     * Поиск существующего кейса
     */
    protected function findExistingCase(array $data, string $operationType): ?PortfolioCase
    {
        if (! in_array($operationType, ['update', 'upsert'])) {
            return null;
        }

        // Поиск по slug
        if (! empty($data['slug'])) {
            return PortfolioCase::where('slug', $data['slug'])->first();
        }

        // Поиск по ID если указан
        if (! empty($data['id'])) {
            return PortfolioCase::find($data['id']);
        }

        return null;
    }

    /**
     * Обработка связей
     */
    protected function processRelations(PortfolioCase $portfolioCase, array $data): void
    {
        // Связь с товарами
        if (isset($data['products'])) {
            $productIds = $this->csvProcessor->parseIdArray($data['products']);
            if (! empty($productIds)) {
                // Проверяем существование товаров
                $existingProducts = Product::whereIn('id', $productIds)->pluck('id')->toArray();
                if (count($existingProducts) !== count($productIds)) {
                    $missingProducts = array_diff($productIds, $existingProducts);
                    $this->addWarning("Кейс ID {$portfolioCase->id}: Не найдены товары с ID: ".implode(', ', $missingProducts));
                }
                $portfolioCase->products()->sync($existingProducts);
            }
        }

        // Связь с услугами
        if (isset($data['services'])) {
            $serviceIds = $this->csvProcessor->parseIdArray($data['services']);
            if (! empty($serviceIds)) {
                // Проверяем существование услуг
                $existingServices = Service::whereIn('id', $serviceIds)->pluck('id')->toArray();
                if (count($existingServices) !== count($serviceIds)) {
                    $missingServices = array_diff($serviceIds, $existingServices);
                    $this->addWarning("Кейс ID {$portfolioCase->id}: Не найдены услуги с ID: ".implode(', ', $missingServices));
                }
                $portfolioCase->services()->sync($existingServices);
            }
        }
    }
}
