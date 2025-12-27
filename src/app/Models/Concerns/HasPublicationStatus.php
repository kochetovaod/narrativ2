<?php

namespace App\Models\Concerns;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

trait HasPublicationStatus
{
    public const STATUS_DRAFT = 'draft';

    public const STATUS_PUBLISHED = 'published';

    protected static function bootHasPublicationStatus(): void
    {
        static::creating(function (Model $model): void {
            $model->ensurePreviewToken();
        });

        static::saving(function (Model $model): void {
            $model->syncPublicationTimestamp();
        });
    }

    public function scopePublished(Builder $query): Builder
    {
        return $query
            ->where('status', self::STATUS_PUBLISHED)
            ->whereNotNull('published_at')
            ->where('published_at', '<=', now());
    }

    public function scopeDrafts(Builder $query): Builder
    {
        return $query->where('status', self::STATUS_DRAFT);
    }

    public function isPublished(): bool
    {
        return $this->status === self::STATUS_PUBLISHED;
    }

    public function isDraft(): bool
    {
        return $this->status === self::STATUS_DRAFT;
    }

    public function publish(?\DateTimeInterface $publishedAt = null): void
    {
        $this->status = self::STATUS_PUBLISHED;
        $this->published_at = $publishedAt ?? now();
    }

    public function setDraft(): void
    {
        $this->status = self::STATUS_DRAFT;
        $this->published_at = null;
    }

    public function canBePreviewed(?string $token): bool
    {
        if ($this->isPublished()) {
            return true;
        }

        return $token !== null && $token === $this->preview_token;
    }

    private function ensurePreviewToken(): void
    {
        if (! $this->usesPreviewToken()) {
            return;
        }

        if (blank($this->preview_token)) {
            $this->preview_token = (string) Str::uuid();
        }
    }

    private function usesPreviewToken(): bool
    {
        return array_key_exists('preview_token', $this->getAttributes())
            || in_array('preview_token', $this->getFillable(), true);
    }

    private function syncPublicationTimestamp(): void
    {
        if (! $this->isDirty('status')) {
            return;
        }

        if ($this->status === self::STATUS_PUBLISHED && $this->published_at === null) {
            $this->published_at = now();

            return;
        }

        if ($this->status === self::STATUS_DRAFT) {
            $this->published_at = null;
        }
    }
}
