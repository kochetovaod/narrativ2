<?php

namespace App\Services\ImportExport;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class FileStorageService
{
    protected string $disk = 'local';

    protected string $importPath = 'import';

    protected string $exportPath = 'export';

    protected string $examplesPath = 'examples';

    /**
     * Сохранение загруженного файла
     */
    public function saveUploadedFile(UploadedFile $file, string $subfolder = ''): string
    {
        $fileName = time().'_'.Str::slug($file->getClientOriginalName());
        $filePath = trim($subfolder, '/').'/'.$fileName;

        return $file->storeAs($this->importPath.'/'.$filePath, $this->disk);
    }

    /**
     * Получение полного пути к файлу
     */
    public function getFilePath(string $relativePath): string
    {
        return Storage::disk($this->disk)->path($relativePath);
    }

    /**
     * Получение URL для скачивания файла
     */
    public function getDownloadUrl(string $relativePath): string
    {
        return Storage::disk($this->disk)->url($relativePath);
    }

    /**
     * Проверка существования файла
     */
    public function fileExists(string $relativePath): bool
    {
        return Storage::disk($this->disk)->exists($relativePath);
    }

    /**
     * Удаление файла
     */
    public function deleteFile(string $relativePath): bool
    {
        return Storage::disk($this->disk)->delete($relativePath);
    }

    /**
     * Создание директории
     */
    public function makeDirectory(string $path): bool
    {
        return Storage::disk($this->disk)->makeDirectory($path);
    }

    /**
     * Получение списка файлов в директории
     */
    public function getFiles(string $directory, bool $recursive = false): array
    {
        $files = Storage::disk($this->disk)->allFiles($directory);

        return $recursive ? $files : array_filter($files, fn ($file) => ! str_contains($file, '/'));
    }

    /**
     * Создание примера CSV файла
     */
    public function createExampleFile(string $entityType, array $headers, array $sampleData): string
    {
        $fileName = $entityType.'_example.csv';
        $filePath = $this->examplesPath.'/'.$fileName;

        // Создаем директорию для примеров
        $this->makeDirectory($this->examplesPath);

        $csvProcessor = new CSVProcessor;
        $fullFilePath = $csvProcessor->writeCsv($sampleData, $headers);

        return $fullFilePath;
    }

    /**
     * Валидация CSV файла
     */
    public function validateCsvFile(UploadedFile $file): array
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

        // Проверка возможности чтения файла
        try {
            $tempPath = $file->storeAs('temp', 'temp_'.time().'.csv');
            $reader = Reader::createFromPath(storage_path('app/'.$tempPath));
            $reader->setHeaderOffset(0);

            // Проверяем первую строку
            $firstRow = $reader->fetchOne();
            if (empty($firstRow)) {
                $errors[] = 'CSV файл пуст или содержит некорректные данные';
            }

            // Удаляем временный файл
            Storage::disk('local')->delete($tempPath);

        } catch (\Exception $e) {
            $errors[] = 'Ошибка при чтении CSV файла: '.$e->getMessage();
        }

        return $errors;
    }

    /**
     * Очистка старых файлов импорта
     */
    public function cleanupOldFiles(int $daysOld = 30): int
    {
        $deletedCount = 0;

        // Очистка старых файлов импорта
        $importFiles = $this->getFiles($this->importPath, true);
        foreach ($importFiles as $file) {
            $fileTime = Storage::disk($this->disk)->lastModified($file);
            if (time() - $fileTime > $daysOld * 24 * 60 * 60) {
                $this->deleteFile($file);
                $deletedCount++;
            }
        }

        // Очистка старых файлов экспорта (но сохраняем примеры)
        $exportFiles = $this->getFiles($this->exportPath, true);
        foreach ($exportFiles as $file) {
            $fileTime = Storage::disk($this->disk)->lastModified($file);
            if (time() - $fileTime > $daysOld * 24 * 60 * 60) {
                $this->deleteFile($file);
                $deletedCount++;
            }
        }

        return $deletedCount;
    }

    /**
     * Получение информации о файле
     */
    public function getFileInfo(string $relativePath): array
    {
        $fileSize = Storage::disk($this->disk)->size($relativePath);
        $lastModified = Storage::disk($this->disk)->lastModified($relativePath);
        $mimeType = Storage::disk($this->disk)->mimeType($relativePath);

        return [
            'path' => $relativePath,
            'size' => $fileSize,
            'size_formatted' => $this->formatFileSize($fileSize),
            'last_modified' => $lastModified,
            'last_modified_formatted' => date('Y-m-d H:i:s', $lastModified),
            'mime_type' => $mimeType,
            'exists' => true,
        ];
    }

    /**
     * Форматирование размера файла
     */
    protected function formatFileSize(int $bytes): string
    {
        $units = ['B', 'KB', 'MB', 'GB'];
        $bytes = max($bytes, 0);
        $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
        $pow = min($pow, count($units) - 1);

        $bytes /= (1 << (10 * $pow));

        return round($bytes, 2).' '.$units[$pow];
    }

    /**
     * Создание резервной копии файла перед импортом
     */
    public function backupFile(string $relativePath): ?string
    {
        if (! $this->fileExists($relativePath)) {
            return null;
        }

        $backupPath = $relativePath.'.backup_'.date('Y-m-d_H-i-s');

        $content = Storage::disk($this->disk)->get($relativePath);
        Storage::disk($this->disk)->put($backupPath, $content);

        return $backupPath;
    }

    /**
     * Архивирование файлов импорта
     */
    public function archiveImportFiles(?string $archiveName = null): string
    {
        $archiveName = $archiveName ?: 'import_archive_'.date('Y-m-d_H-i-s').'.zip';
        $archivePath = $this->importPath.'/'.$archiveName;

        $zip = new \ZipArchive;
        $zipPath = storage_path('app/'.$archivePath);

        if ($zip->open($zipPath, \ZipArchive::CREATE) === true) {
            $files = $this->getFiles($this->importPath, true);

            foreach ($files as $file) {
                $zip->addFile(storage_path('app/'.$file), basename($file));
            }

            $zip->close();

            return $archivePath;
        }

        throw new \Exception('Не удалось создать архив');
    }
}
