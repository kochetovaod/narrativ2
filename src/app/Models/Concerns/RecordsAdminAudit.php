<?php

namespace App\Models\Concerns;

use App\Models\AdminAudit;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Auth;

trait RecordsAdminAudit
{
    protected static function bootRecordsAdminAudit(): void
    {
        static::created(function (Model $model): void {
            $model->writeAudit('created');
        });

        static::updated(function (Model $model): void {
            $model->writeAudit('updated', $model->getAuditChanges());
        });

        static::deleted(function (Model $model): void {
            $model->writeAudit('deleted');
        });
    }

    /**
     * @param  array<string, mixed>  $changes
     */
    protected function writeAudit(string $action, array $changes = []): void
    {
        AdminAudit::query()->create([
            'user_id' => Auth::id(),
            'action' => $action,
            'auditable_type' => static::class,
            'auditable_id' => $this->getKey(),
            'changes' => $changes ?: null,
            'context' => $this->auditContext(),
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    protected function auditContext(): array
    {
        return [
            'label' => $this->getAttribute('title') ?? $this->getAttribute('slug'),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    protected function getAuditChanges(): array
    {
        $changes = $this->getChanges();

        return Arr::except($changes, ['updated_at']);
    }
}
