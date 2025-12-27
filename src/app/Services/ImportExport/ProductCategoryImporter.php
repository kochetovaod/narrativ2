<?php

namespace App\Services\ImportExport;

use App\Models\ProductCategory;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class ProductCategoryImporter extends ImportExportService
{
    protected string $model = ProductCategory::class;

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
            $existingCategory = $this->findExistingCategory($data, $operationType);

            if ($existingCategory) {
                // Обновление существующей записи
                if (in_array($operationType, ['update', 'upsert'])) {
                    $existingCategory->update($data);
                    $category = $existingCategory;
                    $this->addWarning("Обновлена категория: {$data['title']} (ID: {$category->id})");
                } else {
                    $this->addError("Строка {$rowNumber}: Категория с slug '{$data['slug']}' уже существует");
                    DB::rollBack();

                    return;
                }
            } else {
                // Создание новой записи
                if (in_array($operationType, ['create', 'upsert'])) {
                    $data['preview_token'] = Str::random(32);
                    $category = ProductCategory::create($data);
                    $this->addWarning("Создана категория: {$data['title']} (ID: {$category->id})");
                } else {
                    $this->addError("Строка {$rowNumber}: Категория с slug '{$data['slug']}' не найдена для обновления");
                    DB::rollBack();

                    return;
                }
            }

            // Обработка связей (не требуется для категорий)
            $this->processRelations($category, $data);

            DB::commit();

        } catch (\Exception $e) {
            DB::rollBack();
            $this->addError("Строка {$rowNumber}: ".$e->getMessage());
        }
    }

    protected function getEntityType(): string
    {
        return 'product_categories';
    }

    protected function getRequiredHeaders(): array
    {
        return [
            'title',
            'slug',
            'status',
        ];
    }

    protected function getExportHeaders(): array
    {
        return [
            'id',
            'title',
            'slug',
            'intro_text',
            'body',
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
        $query = ProductCategory::query();

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

        $categories = $query->orderBy('id')->get();

        $data = [];
        foreach ($categories as $category) {
            $data[] = [
                $category->id,
                $category->title,
                $category->slug,
                $category->intro_text,
                json_encode($category->body, JSON_UNESCAPED_UNICODE),
                $category->status,
                $category->seo['title'] ?? '',
                $category->seo['description'] ?? '',
                $category->seo['h1'] ?? '',
                $category->published_at?->format('Y-m-d H:i:s'),
                $category->created_at->format('Y-m-d H:i:s'),
            ];
        }

        return $data;
    }

    protected function getSampleData(): array
    {
        return [
            [
                'title' => 'Пример категории 1',
                'slug' => 'category-1',
                'intro_text' => 'Вводный текст',
                'body' => '{"blocks":[{"type":"text","content":"Текст категории"}]}',
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
            'title' => 'Название категории (обязательное)',
            'slug' => 'URL-идентификатор (обязательное, уникальное)',
            'intro_text' => 'Вводный текст',
            'body' => 'Основной контент в формате JSON',
            'status' => 'Статус: draft, published, archived',
            'seo_title' => 'SEO заголовок',
            'seo_description' => 'SEO описание',
            'seo_h1' => 'H1 заголовок',
            'published_at' => 'Дата публикации (YYYY-MM-DD)',
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
            'title' => $this->csvProcessor->normalizeString($row['title'] ?? ''),
            'slug' => $this->csvProcessor->normalizeString($row['slug'] ?? ''),
            'intro_text' => $this->csvProcessor->normalizeString($row['intro_text'] ?? ''),
            'status' => $this->csvProcessor->normalizeString($row['status'] ?? 'draft'),
            'published_at' => $this->csvProcessor->parseDate($row['published_at'] ?? null),
        ];

        // Обработка контента
        $body = $this->csvProcessor->parseJson($row['body'] ?? null, []);
        $data['body'] = $body;

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
            $errors[] = "Строка {$rowNumber}: Название категории обязательно для заполнения";
        }

        if (empty($data['slug'])) {
            $errors[] = "Строка {$rowNumber}: Slug обязателен для заполнения";
        }

        // Валидация статуса
        if (! in_array($data['status'], ['draft', 'published', 'archived'])) {
            $errors[] = "Строка {$rowNumber}: Недопустимый статус. Допустимые значения: draft, published, archived";
        }

        // Валидация уникальности slug
        if (! empty($data['slug'])) {
            $existingCategory = ProductCategory::where('slug', $data['slug'])->first();
            if ($existingCategory) {
                $errors[] = "Строка {$rowNumber}: Категория с slug '{$data['slug']}' уже существует (ID: {$existingCategory->id})";
            }
        }

        return $errors;
    }

    /**
     * Поиск существующей категории
     */
    protected function findExistingCategory(array $data, string $operationType): ?ProductCategory
    {
        if (! in_array($operationType, ['update', 'upsert'])) {
            return null;
        }

        // Поиск по slug
        if (! empty($data['slug'])) {
            return ProductCategory::where('slug', $data['slug'])->first();
        }

        // Поиск по ID если указан
        if (! empty($data['id'])) {
            return ProductCategory::find($data['id']);
        }

        return null;
    }

    /**
     * Обработка связей (не требуется для категорий)
     */
    protected function processRelations(ProductCategory $category, array $data): void
    {
        // Ничего не делаем, связи не требуются для категорий
    }
}
