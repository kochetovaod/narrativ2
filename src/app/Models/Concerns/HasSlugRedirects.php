<?php

namespace App\Models\Concerns;

use App\Models\Redirect;
use Illuminate\Database\Eloquent\Model;

trait HasSlugRedirects
{
    protected static function bootHasSlugRedirects(): void
    {
        static::updating(function (Model $model): void {
            if (! $model->shouldCreateRedirect()) {
                return;
            }

            $fromPath = $model->publicPathFromAttributes($model->getOriginal());
            $toPath = $model->publicPathFromAttributes($model->getAttributes());

            if ($fromPath === $toPath) {
                return;
            }

            Redirect::query()->updateOrCreate(
                ['from_path' => $fromPath],
                ['to_path' => $toPath, 'code' => 301, 'is_active' => true],
            );
        });
    }

    protected function shouldCreateRedirect(): bool
    {
        return $this->isDirty('slug') || $this->pathDependenciesChanged();
    }

    protected function pathDependenciesChanged(): bool
    {
        return false;
    }

    abstract protected function publicPathFromAttributes(array $attributes): string;
}
