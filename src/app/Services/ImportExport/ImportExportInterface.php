<?php

namespace App\Services\ImportExport;

use Illuminate\Http\UploadedFile;

interface ImportExportInterface
{
    /**
     * Импорт данных из CSV файла
     */
    public function import(UploadedFile $file, string $operationType = 'upsert'): array;

    /**
     * Экспорт данных в CSV файл
     */
    public function export(array $filters = []): string;

    /**
     * Валидация CSV файла
     */
    public function validateFile(UploadedFile $file): array;

    /**
     * Получение примера CSV структуры
     */
    public function getCsvExample(): array;

    /**
     * Получение доступных полей для импорта
     */
    public function getImportableFields(): array;

    /**
     * Получение связанных моделей для импорта
     */
    public function getRelatedModels(): array;
}
