<?php

namespace App\Models;

use App\Models\Concerns\RecordsAdminAudit;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ImportLog extends Model
{
    use HasFactory;
    use RecordsAdminAudit;

    protected $fillable = [
        'entity_type',
        'operation_type',
        'status',
        'total_records',
        'processed_records',
        'error_records',
        'file_path',
        'error_log',
        'started_at',
        'finished_at',
        'user_id',
    ];

    protected $casts = [
        'started_at' => 'datetime',
        'finished_at' => 'datetime',
        'error_log' => 'array',
        'total_records' => 'integer',
        'processed_records' => 'integer',
        'error_records' => 'integer',
    ];

    /**
     * Возвращает процент выполнения импорта
     */
    public function getProgressPercentageAttribute(): int
    {
        if ($this->total_records === 0) {
            return 0;
        }

        return (int) round(($this->processed_records / $this->total_records) * 100);
    }

    /**
     * Возвращает статус в читаемом виде
     */
    public function getStatusLabelAttribute(): string
    {
        switch ($this->status) {
            case 'pending':
                return 'Ожидает';
            case 'processing':
                return 'Выполняется';
            case 'completed':
                return 'Завершен';
            case 'failed':
                return 'Ошибка';
            case 'cancelled':
                return 'Отменен';
            default:
                return 'Неизвестно';
        }
    }

    /**
     * Возвращает тип операции в читаемом виде
     */
    public function getOperationLabelAttribute(): string
    {
        switch ($this->operation_type) {
            case 'create':
                return 'Создание';
            case 'update':
                return 'Обновление';
            case 'upsert':
                return 'Создание/Обновление';
            case 'export':
                return 'Экспорт';
            default:
                return 'Неизвестно';
        }
    }

    /**
     * Возвращает тип сущности в читаемом виде
     */
    public function getEntityLabelAttribute(): string
    {
        switch ($this->entity_type) {
            case 'products':
                return 'Товары';
            case 'services':
                return 'Услуги';
            case 'portfolio_cases':
                return 'Кейсы портфолио';
            case 'leads':
                return 'Заявки';
            case 'product_categories':
                return 'Категории товаров';
            default:
                return 'Неизвестно';
        }
    }
}
