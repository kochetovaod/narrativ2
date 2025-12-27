<?php

namespace App\Services\ImportExport;

use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use League\Csv\EscapeFormula;
use League\Csv\Reader;
use League\Csv\Statement;
use League\Csv\Writer;

class CSVProcessor
{
    protected string $storageDisk = 'local';

    protected string $uploadPath = 'import';

    protected string $exportPath = 'export';

    /**
     * Сохранение загруженного CSV файла
     */
    public function saveUploadedFile(\Illuminate\Http\UploadedFile $file): string
    {
        $fileName = time().'_'.Str::slug($file->getClientOriginalName());
        $filePath = $file->storeAs($this->uploadPath, $fileName, $this->storageDisk);

        return $filePath;
    }

    /**
     * Чтение CSV файла
     */
    public function readCsv(string $filePath): Reader
    {
        $fullPath = Storage::disk($this->storageDisk)->path($filePath);

        $reader = Reader::createFromPath($fullPath, 'r');
        $reader->setHeaderOffset(0);
        $reader->addStreamFilter('convert.utf8');
        $reader->setDelimiter(',');
        $reader->setEnclosure('"');
        $reader->setEscape('\\');

        // Защита от формул Excel
        $reader->addStreamFilter(EscapeFormula::class);

        return $reader;
    }

    /**
     * Запись CSV файла с BOM для Excel
     */
    public function writeCsv(array $data, array $headers): string
    {
        $fileName = 'export_'.date('Y-m-d_H-i-s').'.csv';
        $filePath = $this->exportPath.'/'.$fileName;

        $fullPath = Storage::disk($this->storageDisk)->path($filePath);

        // Создаем директорию если не существует
        $dir = dirname($fullPath);
        if (! is_dir($dir)) {
            mkdir($dir, 0755, true);
        }

        $writer = Writer::createFromPath($fullPath, 'w+');

        // Добавляем BOM для корректного отображения в Excel
        $writer->insertOne("\xEF\xBB\xBF");

        // Записываем заголовки
        $writer->insertOne($headers);

        // Записываем данные
        foreach ($data as $row) {
            $writer->insertOne($row);
        }

        return $filePath;
    }

    /**
     * Получение первых строк CSV для предпросмотра
     */
    public function getPreview(string $filePath, int $limit = 10): array
    {
        $reader = $this->readCsv($filePath);
        $statement = Statement::create()
            ->limit($limit);

        return $statement->process($reader)->toArray();
    }

    /**
     * Подсчет строк в CSV
     */
    public function countRows(string $filePath): int
    {
        $reader = $this->readCsv($filePath);

        return count($reader);
    }

    /**
     * Валидация структуры CSV
     */
    public function validateStructure(string $filePath, array $requiredHeaders): array
    {
        $errors = [];
        $reader = $this->readCsv($filePath);

        // Получаем заголовки
        $headers = $reader->getHeader();

        // Проверяем наличие всех обязательных заголовков
        foreach ($requiredHeaders as $requiredHeader) {
            if (! in_array($requiredHeader, $headers)) {
                $errors[] = "Отсутствует обязательный заголовок: {$requiredHeader}";
            }
        }

        // Проверяем наличие дублирующихся заголовков
        $uniqueHeaders = array_unique($headers);
        if (count($headers) !== count($uniqueHeaders)) {
            $errors[] = 'Обнаружены дублирующиеся заголовки в CSV файле';
        }

        return $errors;
    }

    /**
     * Очистка и нормализация строки для CSV
     */
    public function normalizeString(?string $value): string
    {
        if (empty($value)) {
            return '';
        }

        // Удаляем лишние пробелы
        $value = trim($value);

        // Заменяем переносы строк на пробелы
        $value = preg_replace('/\s+/', ' ', $value);

        return $value;
    }

    /**
     * Парсинг JSON строки
     */
    public function parseJson(?string $jsonString, $default = null)
    {
        if (empty($jsonString)) {
            return $default;
        }

        $decoded = json_decode($jsonString, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            return $default;
        }

        return $decoded;
    }

    /**
     * Парсинг массива ID из строки
     */
    public function parseIdArray(?string $idString): array
    {
        if (empty($idString)) {
            return [];
        }

        $ids = explode(',', $idString);
        $cleanIds = [];

        foreach ($ids as $id) {
            $cleanId = (int) trim($id);
            if ($cleanId > 0) {
                $cleanIds[] = $cleanId;
            }
        }

        return array_unique($cleanIds);
    }

    /**
     * Парсинг boolean значения
     */
    public function parseBoolean(?string $value): bool
    {
        if (empty($value)) {
            return false;
        }

        $value = strtolower(trim($value));

        return in_array($value, ['1', 'true', 'yes', 'да', 'да', 'on']);
    }

    /**
     * Парсинг даты
     */
    public function parseDate(?string $dateString): ?string
    {
        if (empty($dateString)) {
            return null;
        }

        try {
            $date = \Carbon\Carbon::parse($dateString);

            return $date->format('Y-m-d H:i:s');
        } catch (\Exception $e) {
            return null;
        }
    }
}
