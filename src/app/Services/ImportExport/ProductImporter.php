<?php

namespace App\Services\ImportExport;

use App\Models\PortfolioCase;
use App\Models\Product;
use App\Models\ProductCategory;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class ProductImporter extends ImportExportService
{
    protected string $model = Product::class;

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
            $existingProduct = $this->findExistingProduct($data, $operationType);

            if ($existingProduct) {
                // Обновление существующей записи
                if (in_array($operationType, ['update', 'upsert'])) {
                    $existingProduct->update($data);
                    $product = $existingProduct;
                    $this->addWarning("Обновлен товар: {$data['title']} (ID: {$product->id})");
                } else {
                    $this->addError("Строка {$rowNumber}: Товар с slug '{$data['slug']}' уже существует");
                    DB::rollBack();

                    return;
                }
            } else {
                // Создание новой записи
                if (in_array($operationType, ['create', 'upsert'])) {
                    $data['preview_token'] = Str::random(32);
                    $product = Product::create($data);
                    $this->addWarning("Создан товар: {$data['title']} (ID: {$product->id})");
                } else {
                    $this->addError("Строка {$rowNumber}: Товар с slug '{$data['slug']}' не найден для обновления");
                    DB::rollBack();

                    return;
                }
            }

            // Обработка связей
            $this->processRelations($product, $data);

            DB::commit();

        } catch (\Exception $e) {
            DB::rollBack();
            $this->addError("Строка {$rowNumber}: ".$e->getMessage());
        }
    }

    protected function getEntityType(): string
    {
        return 'products';
    }

    protected function getRequiredHeaders(): array
    {
        return [
            'title',
            'slug',
            'description',
            'category_id',
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
            'specs',
            'category_id',
            'category_title',
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
        $query = Product::with(['category', 'portfolioCases']);

        // Применяем фильтры
        if (isset($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if (isset($filters['category_id'])) {
            $query->where('category_id', $filters['category_id']);
        }

        if (isset($filters['date_from'])) {
            $query->whereDate('created_at', '>=', $filters['date_from']);
        }

        if (isset($filters['date_to'])) {
            $query->whereDate('created_at', '<=', $filters['date_to']);
        }

        $products = $query->orderBy('id')->get();

        $data = [];
        foreach ($products as $product) {
            $data[] = [
                $product->id,
                $product->title,
                $product->slug,
                $product->description,
                json_encode($product->specs, JSON_UNESCAPED_UNICODE),
                $product->category_id,
                $product->category?->title ?? '',
                $product->portfolioCases->pluck('id')->implode(','),
                $product->status,
                $product->seo['title'] ?? '',
                $product->seo['description'] ?? '',
                $product->seo['h1'] ?? '',
                $product->published_at?->format('Y-m-d H:i:s'),
                $product->created_at->format('Y-m-d H:i:s'),
            ];
        }

        return $data;
    }

    protected function getSampleData(): array
    {
        return [
            [
                'title' => 'Пример товара 1',
                'slug' => 'product-1',
                'description' => 'Описание товара',
                'specs' => '{"weight": "10kg", "color": "red"}',
                'category_id' => '1',
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
            'title' => 'Название товара (обязательное)',
            'slug' => 'URL-идентификатор (обязательное, уникальное)',
            'description' => 'Описание товара (обязательное)',
            'specs' => 'Характеристики в формате JSON',
            'category_id' => 'ID категории товара (обязательное)',
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
            'category_id' => ProductCategory::class,
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
            'description' => $this->csvProcessor->normalizeString($row['description'] ?? ''),
            'category_id' => (int) ($row['category_id'] ?? 0),
            'status' => $this->csvProcessor->normalizeString($row['status'] ?? 'draft'),
            'published_at' => $this->csvProcessor->parseDate($row['published_at'] ?? null),
        ];

        // Обработка спецификаций
        $specs = $this->csvProcessor->parseJson($row['specs'] ?? null, []);
        $data['specs'] = $specs;

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
            $errors[] = "Строка {$rowNumber}: Название товара обязательно для заполнения";
        }

        if (empty($data['slug'])) {
            $errors[] = "Строка {$rowNumber}: Slug обязателен для заполнения";
        }

        if (empty($data['description'])) {
            $errors[] = "Строка {$rowNumber}: Описание товара обязательно для заполнения";
        }

        if (empty($data['category_id'])) {
            $errors[] = "Строка {$rowNumber}: ID категории обязателен для заполнения";
        }

        // Валидация статуса
        if (! in_array($data['status'], ['draft', 'published', 'archived'])) {
            $errors[] = "Строка {$rowNumber}: Недопустимый статус. Допустимые значения: draft, published, archived";
        }

        // Валидация категории
        if ($data['category_id'] > 0) {
            $category = ProductCategory::find($data['category_id']);
            if (! $category) {
                $errors[] = "Строка {$rowNumber}: Категория с ID {$data['category_id']} не найдена";
            }
        }

        // Валидация уникальности slug
        if (! empty($data['slug'])) {
            $existingProduct = Product::where('slug', $data['slug'])->first();
            if ($existingProduct) {
                $errors[] = "Строка {$rowNumber}: Товар с slug '{$data['slug']}' уже существует (ID: {$existingProduct->id})";
            }
        }

        return $errors;
    }

    /**
     * Поиск существующего товара
     */
    protected function findExistingProduct(array $data, string $operationType): ?Product
    {
        if (! in_array($operationType, ['update', 'upsert'])) {
            return null;
        }

        // Поиск по slug
        if (! empty($data['slug'])) {
            return Product::where('slug', $data['slug'])->first();
        }

        // Поиск по ID если указан
        if (! empty($data['id'])) {
            return Product::find($data['id']);
        }

        return null;
    }

    /**
     * Обработка связей
     */
    protected function processRelations(Product $product, array $data): void
    {
        // Связь с кейсами портфолио
        if (isset($data['portfolio_cases'])) {
            $caseIds = $this->csvProcessor->parseIdArray($data['portfolio_cases']);
            if (! empty($caseIds)) {
                // Проверяем существование кейсов
                $existingCases = PortfolioCase::whereIn('id', $caseIds)->pluck('id')->toArray();
                if (count($existingCases) !== count($caseIds)) {
                    $missingCases = array_diff($caseIds, $existingCases);
                    $this->addWarning("Товар ID {$product->id}: Не найдены кейсы с ID: ".implode(', ', $missingCases));
                }
                $product->portfolioCases()->sync($existingCases);
            }
        }
    }
}
