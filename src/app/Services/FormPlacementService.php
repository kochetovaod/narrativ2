<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\FormPlacement;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;

class FormPlacementService
{
    /**
     * Получить включенные размещения форм для сущности.
     */
    public function forEntity(Model|string $entity, ?int $entityId = null): Collection
    {
        $type = is_string($entity) ? $entity : class_basename($entity);
        $id = $entityId ?? ($entity instanceof Model ? $entity->getKey() : null);

        if ($id === null) {
            return collect();
        }

        return FormPlacement::query()
            ->with('form')
            ->where('entity_type', $type)
            ->where('entity_id', $id)
            ->where('is_enabled', true)
            ->whereHas('form', fn ($query) => $query->where('is_active', true))
            ->orderBy('id')
            ->get();
    }
}
