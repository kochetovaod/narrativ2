<?php

namespace App\Services\ImportExport;

use App\Models\ImportLog;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

abstract class ImportExportService implements ImportExportInterface
{
    protected CSVProcessor $csvProcessor;

    protected ImportLog $importLog;

    protected array $errors = [];

    protected array $warnings = [];

    protected int $processedCount = 0;

    protected int $errorCount = 0;

    public function __construct()
    {
        $this->csvProcessor = new CSVProcessor;
    }

    /**
     * Импорт данных из CSV файла
     */
    public function import(UploadedFile $file, string $operationType = 'upsert'): array
    {
        // Создаем запись в логе импорта
        $this->importLog = $this->createImportLog($operationType);

        try {
            // Обновляем статус на "выполняется"
            $this->updateImportLogStatus('processing');

            // Валидация файла
            $validationErrors = $this->validateFile($file);
            if (! empty($validationErrors)) {
                $this->addErrors($validationErrors);
                $this->updateImportLogStatus('failed');

                return $this->getResult();
            }

            // Сохранение файла
            $filePath = $this->csvProcessor->saveUploadedFile($file);
            $this->importLog->update(['file_path' => $filePath]);

            // Подсчет строк
            $totalRows = $this->csvProcessor->countRows($filePath);
            $this->importLog->update(['total_records' => $totalRows]);

            // Обработка данных
            $this->processData($filePath, $operationType);

            // Завершение импорта
            $this->updateImportLogStatus('completed');

        } catch (\Exception $e) {
            Log::error('Import error: '.$e->getMessage(), [
                'entity_type' => $this->getEntityType(),
                'operation_type' => $operationType,
                'file' => $file->getClientOriginalName(),
                'user_id' => Auth::id(),
            ]);

            $this->addError('Произошла ошибка при импорте: '.$e->getMessage());
            $this->updateImportLogStatus('failed');
        }

        return $this->getResult();
    }

    /**
     * Экспорт данных в CSV файл
     */
    public function export(array $filters = []): string
    {
        try {
            // Создаем запись в логе
            $importLog = $this->createImportLog('export');

            // Получаем данные для экспорта
            $data = $this->getDataForExport($filters);

            // Получаем заголовки
            $headers = $this->getExportHeaders();

            // Создаем CSV файл
            $filePath = $this->csvProcessor->writeCsv($data, $headers);

            // Обновляем лог
            $importLog->update([
                'total_records' => count($data),
                'processed_records' => count($data),
                'file_path' => $filePath,
                'status' => 'completed',
                'finished_at' => now(),
            ]);

            return $filePath;

        } catch (\Exception $e) {
            Log::error('Export error: '.$e->getMessage(), [
                'entity_type' => $this->getEntityType(),
                'filters' => $filters,
                'user_id' => Auth::id(),
            ]);

            throw $e;
        }
    }

    /**
     * Валидация CSV файла
     */
    public function validateFile(UploadedFile $file): array
    {
        $errors = [];

        // Проверка типа файла
        if ($file->getClientMimeType() !== 'text/csv' &&
            ! Str::endsWith($file->getClientOriginalName(), '.csv')) {
            $errors[] = 'Файл должен быть в формате CSV';
        }

        // Проверка размера (максимум 10MB)
        if ($file->getSize() > 10 * 1024 * 1024) {
            $errors[] = 'Размер файла не должен превышать 10MB';
        }

        // Проверка структуры CSV
        $requiredHeaders = $this->getRequiredHeaders();
        if (! empty($requiredHeaders)) {
            try {
                $tempPath = $this->csvProcessor->saveUploadedFile($file);
                $validationErrors = $this->csvProcessor->validateStructure($tempPath, $requiredHeaders);
                if (! empty($validationErrors)) {
                    $errors = array_merge($errors, $validationErrors);
                }
                // Удаляем временный файл
                unlink(storage_path('app/'.$tempPath));
            } catch (\Exception $e) {
                $errors[] = 'Ошибка при чтении CSV файла: '.$e->getMessage();
            }
        }

        return $errors;
    }

    /**
     * Получение примера CSV структуры
     */
    public function getCsvExample(): array
    {
        return [
            'headers' => $this->getRequiredHeaders(),
            'sample_data' => $this->getSampleData(),
            'description' => $this->getFieldDescriptions(),
        ];
    }

    /**
     * Получение доступных полей для импорта
     */
    public function getImportableFields(): array
    {
        return $this->getRequiredHeaders();
    }

    /**
     * Получение связанных моделей для импорта
     */
    public function getRelatedModels(): array
    {
        return [];
    }

    /**
     * Создание записи в логе импорта
     */
    protected function createImportLog(string $operationType): ImportLog
    {
        return ImportLog::create([
            'entity_type' => $this->getEntityType(),
            'operation_type' => $operationType,
            'status' => 'pending',
            'user_id' => Auth::id(),
            'started_at' => now(),
        ]);
    }

    /**
     * Обновление статуса лога импорта
     */
    protected function updateImportLogStatus(string $status): void
    {
        $updateData = ['status' => $status];

        if ($status === 'processing') {
            $updateData['processed_records'] = $this->processedCount;
            $updateData['error_records'] = $this->errorCount;
        } elseif (in_array($status, ['completed', 'failed'])) {
            $updateData['processed_records'] = $this->processedCount;
            $updateData['error_records'] = $this->errorCount;
            $updateData['error_log'] = $this->errors;
            $updateData['finished_at'] = now();
        }

        $this->importLog->update($updateData);
    }

    /**
     * Обработка данных из CSV файла
     */
    protected function processData(string $filePath, string $operationType): void
    {
        $reader = $this->csvProcessor->readCsv($filePath);

        foreach ($reader as $index => $row) {
            try {
                $this->processedCount++;

                // Обновляем прогресс в логе каждые 10 записей
                if ($this->processedCount % 10 === 0) {
                    $this->updateImportLogStatus('processing');
                }

                $this->processRow($row, $operationType, $index + 1);

            } catch (\Exception $e) {
                $this->errorCount++;
                $this->addError('Строка '.($index + 1).': '.$e->getMessage());

                // Логируем ошибку
                Log::warning('Import row error', [
                    'row' => $index + 1,
                    'entity_type' => $this->getEntityType(),
                    'error' => $e->getMessage(),
                    'row_data' => $row,
                ]);
            }
        }
    }

    /**
     * Обработка одной строки данных
     */
    abstract protected function processRow(array $row, string $operationType, int $rowNumber): void;

    /**
     * Получение типа сущности для импорта
     */
    abstract protected function getEntityType(): string;

    /**
     * Получение обязательных заголовков CSV
     */
    abstract protected function getRequiredHeaders(): array;

    /**
     * Получение заголовков для экспорта
     */
    abstract protected function getExportHeaders(): array;

    /**
     * Получение данных для экспорта
     */
    abstract protected function getDataForExport(array $filters): array;

    /**
     * Получение примера данных
     */
    abstract protected function getSampleData(): array;

    /**
     * Получение описаний полей
     */
    abstract protected function getFieldDescriptions(): array;

    /**
     * Добавление ошибки
     */
    protected function addError(string $error): void
    {
        $this->errors[] = $error;
    }

    /**
     * Добавление предупреждения
     */
    protected function addWarning(string $warning): void
    {
        $this->warnings[] = $warning;
    }

    /**
     * Добавление ошибок
     */
    protected function addErrors(array $errors): void
    {
        $this->errors = array_merge($this->errors, $errors);
    }

    /**
     * Получение результата импорта
     */
    protected function getResult(): array
    {
        return [
            'success' => $this->errorCount === 0,
            'processed_count' => $this->processedCount,
            'error_count' => $this->errorCount,
            'errors' => $this->errors,
            'warnings' => $this->warnings,
            'import_log_id' => $this->importLog->id,
        ];
    }
}
