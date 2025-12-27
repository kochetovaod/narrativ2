<?php

namespace App\Services\ImportExport;

use App\Models\Lead;
use App\Models\PortfolioCase;
use App\Models\Product;
use App\Models\ProductCategory;
use App\Models\Service;
use Illuminate\Support\Facades\Storage;

class CSVExporter
{
    protected CSVProcessor $csvProcessor;

    protected FileStorageService $fileStorage;

    public function __construct()
    {
        $this->csvProcessor = new CSVProcessor;
        $this->fileStorage = new FileStorageService;
    }

    /**
     * Экспорт товаров
     */
    public function exportProducts(array $filters = []): string
    {
        $importer = new ProductImporter;

        return $importer->export($filters);
    }

    /**
     * Экспорт услуг
     */
    public function exportServices(array $filters = []): string
    {
        $importer = new ServiceImporter;

        return $importer->export($filters);
    }

    /**
     * Экспорт кейсов портфолио
     */
    public function exportPortfolioCases(array $filters = []): string
    {
        $importer = new PortfolioCaseImporter;

        return $importer->export($filters);
    }

    /**
     * Экспорт заявок с расширенными фильтрами
     */
    public function exportLeads(array $filters = []): string
    {
        $importer = new LeadImporter;

        // Применяем специальные фильтры для заявок
        $processedFilters = $this->processLeadFilters($filters);

        return $importer->export($processedFilters);
    }

    /**
     * Экспорт категорий товаров
     */
    public function exportProductCategories(array $filters = []): string
    {
        $importer = new ProductCategoryImporter;

        return $importer->export($filters);
    }

    /**
     * Массовый экспорт всех сущностей
     */
    public function exportAll(array $filters = []): array
    {
        $results = [];

        try {
            // Экспорт товаров
            $results['products'] = $this->exportProducts($filters);

            // Экспорт услуг
            $results['services'] = $this->exportServices($filters);

            // Экспорт кейсов
            $results['portfolio_cases'] = $this->exportPortfolioCases($filters);

            // Экспорт категорий
            $results['product_categories'] = $this->exportProductCategories($filters);

            // Экспорт заявок (с специальными фильтрами)
            $results['leads'] = $this->exportLeads($filters);

        } catch (\Exception $e) {
            throw new \Exception('Ошибка при массовом экспорте: '.$e->getMessage());
        }

        return $results;
    }

    /**
     * Создание архива с экспортированными файлами
     */
    public function createExportArchive(array $filters = []): string
    {
        $exportResults = $this->exportAll($filters);
        $archiveName = 'full_export_'.date('Y-m-d_H-i-s').'.zip';
        $archivePath = 'export/'.$archiveName;

        $zip = new \ZipArchive;
        $zipFullPath = Storage::disk('local')->path($archivePath);

        // Создаем директорию для экспорта
        $this->fileStorage->makeDirectory('export');

        if ($zip->open($zipFullPath, \ZipArchive::CREATE) === true) {
            foreach ($exportResults as $entityType => $filePath) {
                if ($this->fileStorage->fileExists($filePath)) {
                    $zip->addFile(
                        $this->fileStorage->getFilePath($filePath),
                        $entityType.'/'.basename($filePath)
                    );
                }
            }

            // Добавляем информационный файл
            $infoContent = $this->generateExportInfo($exportResults, $filters);
            $zip->addFromString('export_info.txt', $infoContent);

            $zip->close();

            return $archivePath;
        }

        throw new \Exception('Не удалось создать архив экспорта');
    }

    /**
     * Получение статистики экспорта
     */
    public function getExportStatistics(array $filters = []): array
    {
        return [
            'products' => $this->getEntityCount(Product::class, $filters),
            'services' => $this->getEntityCount(Service::class, $filters),
            'portfolio_cases' => $this->getEntityCount(PortfolioCase::class, $filters),
            'product_categories' => $this->getEntityCount(ProductCategory::class, $filters),
            'leads' => $this->getLeadCount($filters),
        ];
    }

    /**
     * Обработка специальных фильтров для заявок
     */
    protected function processLeadFilters(array $filters): array
    {
        $processed = $filters;

        // Преобразуем строковые значения UTM в массив
        if (isset($filters['utm_source']) && is_string($filters['utm_source'])) {
            $processed['utm_source'] = [$filters['utm_source']];
        }

        if (isset($filters['utm_medium']) && is_string($filters['utm_medium'])) {
            $processed['utm_medium'] = [$filters['utm_medium']];
        }

        if (isset($filters['utm_campaign']) && is_string($filters['utm_campaign'])) {
            $processed['utm_campaign'] = [$filters['utm_campaign']];
        }

        return $processed;
    }

    /**
     * Подсчет записей сущности
     */
    protected function getEntityCount(string $modelClass, array $filters): int
    {
        $query = $modelClass::query();

        // Применяем стандартные фильтры
        if (isset($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if (isset($filters['date_from'])) {
            $query->whereDate('created_at', '>=', $filters['date_from']);
        }

        if (isset($filters['date_to'])) {
            $query->whereDate('created_at', '<=', $filters['date_to']);
        }

        return $query->count();
    }

    /**
     * Подсчет заявок с расширенными фильтрами
     */
    protected function getLeadCount(array $filters): int
    {
        $query = Lead::query();

        // Стандартные фильтры
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

        // UTM фильтры
        if (isset($filters['utm_source'])) {
            $query->where('utm->source', $filters['utm_source']);
        }

        if (isset($filters['utm_medium'])) {
            $query->where('utm->medium', $filters['utm_medium']);
        }

        if (isset($filters['utm_campaign'])) {
            $query->where('utm->campaign', $filters['utm_campaign']);
        }

        if (isset($filters['utm_term'])) {
            $query->where('utm->term', $filters['utm_term']);
        }

        if (isset($filters['utm_content'])) {
            $query->where('utm->content', $filters['utm_content']);
        }

        // Фильтр по согласию на обработку ПДн
        if (isset($filters['consent_given'])) {
            $query->where('consent_given', $filters['consent_given']);
        }

        return $query->count();
    }

    /**
     * Генерация информационного файла для экспорта
     */
    protected function generateExportInfo(array $exportResults, array $filters): string
    {
        $info = "=== ИНФОРМАЦИЯ ОБ ЭКСПОРТЕ ===\n";
        $info .= 'Дата экспорта: '.date('Y-m-d H:i:s')."\n";
        $info .= 'Пользователь: '.auth()->user()->name.' (ID: '.auth()->id().")\n\n";

        $info .= "=== ПРИМЕНЕННЫЕ ФИЛЬТРЫ ===\n";
        foreach ($filters as $key => $value) {
            $info .= $key.': '.(is_array($value) ? implode(', ', $value) : $value)."\n";
        }
        $info .= "\n";

        $info .= "=== ЭКСПОРТИРОВАННЫЕ ФАЙЛЫ ===\n";
        foreach ($exportResults as $entityType => $filePath) {
            $fileInfo = $this->fileStorage->getFileInfo($filePath);
            $info .= $entityType.': '.$filePath.' ('.$fileInfo['size_formatted'].")\n";
        }
        $info .= "\n";

        $info .= "=== СТАТИСТИКА ===\n";
        $stats = $this->getExportStatistics($filters);
        foreach ($stats as $entityType => $count) {
            $info .= $entityType.': '.$count." записей\n";
        }

        return $info;
    }

    /**
     * Предпросмотр экспорта (первые 10 записей)
     */
    public function previewExport(string $entityType, array $filters = []): array
    {
        $importer = $this->getImporter($entityType);

        if (! $importer) {
            throw new \InvalidArgumentException("Неподдерживаемый тип сущности: {$entityType}");
        }

        // Получаем данные для экспорта
        $data = $importer->getDataForExport($filters);
        $headers = $importer->getExportHeaders();

        // Берем только первые 10 записей для предпросмотра
        $previewData = array_slice($data, 0, 10);

        return [
            'headers' => $headers,
            'data' => $previewData,
            'total_count' => count($data),
            'preview_count' => count($previewData),
            'entity_type' => $entityType,
        ];
    }

    /**
     * Получение импортера по типу сущности
     */
    protected function getImporter(string $entityType): ?ImportExportInterface
    {
        switch ($entityType) {
            case 'products':
                return new ProductImporter;
            case 'services':
                return new ServiceImporter;
            case 'portfolio_cases':
                return new PortfolioCaseImporter;
            case 'leads':
                return new LeadImporter;
            case 'product_categories':
                return new ProductCategoryImporter;
            default:
                return null;
        }
    }
}
