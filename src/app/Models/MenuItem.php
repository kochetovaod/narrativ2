<?php

namespace App\Models;

use App\Models\Page;
use App\Models\ProductCategory;
use App\Models\Service;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MenuItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'menu_id',
        'parent_id',
        'title',
        'url',
        'entity_type',
        'entity_id',
        'sort',
        'is_visible',
    ];

    protected $casts = [
        'is_visible' => 'boolean',
    ];

    public function scopeVisible($query)
    {
        return $query->where('is_visible', true);
    }

    public function menu()
    {
        return $this->belongsTo(Menu::class);
    }

    public function parent()
    {
        return $this->belongsTo(MenuItem::class, 'parent_id');
    }

    public function children()
    {
        return $this->hasMany(MenuItem::class, 'parent_id');
    }

    public function resolvedUrl(): string
    {
        static $pageCache = [];
        static $serviceCache = [];
        static $categoryCache = [];

        return match ($this->entity_type) {
            'page' => $this->resolvePageUrl($pageCache),
            'service' => $this->resolveServiceUrl($serviceCache),
            'product_category' => $this->resolveCategoryUrl($categoryCache),
            default => $this->url ?? '#',
        };
    }

    private function resolvePageUrl(array &$cache): string
    {
        if (empty($this->entity_id)) {
            return $this->url ?? '#';
        }

        if (! array_key_exists($this->entity_id, $cache)) {
            $cache[$this->entity_id] = Page::query()
                ->published()
                ->find($this->entity_id);
        }

        return $cache[$this->entity_id]
            ? route('pages.show', ['pageSlug' => $cache[$this->entity_id]->slug])
            : ($this->url ?? '#');
    }

    private function resolveServiceUrl(array &$cache): string
    {
        if (empty($this->entity_id)) {
            return $this->url ?? '#';
        }

        if (! array_key_exists($this->entity_id, $cache)) {
            $cache[$this->entity_id] = Service::query()
                ->published()
                ->find($this->entity_id);
        }

        return $cache[$this->entity_id]
            ? route('services.show', ['serviceSlug' => $cache[$this->entity_id]->slug])
            : ($this->url ?? '#');
    }

    private function resolveCategoryUrl(array &$cache): string
    {
        if (empty($this->entity_id)) {
            return $this->url ?? '#';
        }

        if (! array_key_exists($this->entity_id, $cache)) {
            $cache[$this->entity_id] = ProductCategory::query()
                ->published()
                ->find($this->entity_id);
        }

        return $cache[$this->entity_id]
            ? route('products.category', ['categorySlug' => $cache[$this->entity_id]->slug])
            : ($this->url ?? '#');
    }
}
