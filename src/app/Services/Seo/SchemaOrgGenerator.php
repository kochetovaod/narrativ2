<?php

namespace App\Services\Seo;

use App\Models\Product;
use App\Models\ProductCategory;
use App\Models\Service;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Arr;

class SchemaOrgGenerator
{
    /**
     * @return array<string, mixed>|null
     */
    public function forEntity(Model $model): ?array
    {
        if ($model instanceof Product) {
            return $this->withOverrides($this->productSchema($model), $model->schema_json ?? null);
        }

        if ($model instanceof Service) {
            return $this->withOverrides($this->serviceSchema($model), $model->schema_json ?? null);
        }

        if ($model instanceof ProductCategory) {
            return $this->withOverrides($this->categorySchema($model), $model->schema_json ?? null);
        }

        return null;
    }

    /**
     * @param  array<string, mixed>|null  $overrides
     * @return array<string, mixed>|null
     */
    private function withOverrides(?array $base, ?array $overrides): ?array
    {
        if ($base === null && $overrides === null) {
            return null;
        }

        return array_replace_recursive($base ?? [], $overrides ?? []);
    }

    /**
     * @return array<string, mixed>
     */
    private function productSchema(Product $product): array
    {
        return [
            '@context' => 'https://schema.org',
            '@type' => 'Product',
            'name' => $product->title,
            'description' => $product->short_text ?: $product->description,
            'category' => $product->category?->title,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function serviceSchema(Service $service): array
    {
        return [
            '@context' => 'https://schema.org',
            '@type' => 'Service',
            'name' => $service->title,
            'description' => Arr::get($service->content, 'intro') ?? Arr::get($service->content, 'body'),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function categorySchema(ProductCategory $category): array
    {
        return [
            '@context' => 'https://schema.org',
            '@type' => 'CollectionPage',
            'name' => $category->title,
            'description' => $category->intro_text,
        ];
    }
}
