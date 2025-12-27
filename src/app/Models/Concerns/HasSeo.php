<?php

namespace App\Models\Concerns;

use App\Services\Seo\SchemaOrgGenerator;
use App\Services\Seo\SeoMetaResolver;
use Illuminate\Database\Eloquent\Model;

/**
 * @mixin Model
 */
trait HasSeo
{
    /**
     * Построить SEO-данные на основе шаблона и перезаписей сущности.
     *
     * @param  array<string, mixed>  $variables
     * @return array<string, mixed>
     */
    public function seoMeta(array $variables = []): array
    {
        return app(SeoMetaResolver::class)->resolve($this, $variables);
    }

    /**
     * Получить Schema.org структуру с учетом пользовательских overrides.
     *
     * @return array<string, mixed>|null
     */
    public function schemaPayload(): ?array
    {
        return app(SchemaOrgGenerator::class)->forEntity($this);
    }
}
