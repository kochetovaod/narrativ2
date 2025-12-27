<?php

declare(strict_types=1);

namespace App\Orchid\Filters;

use App\Models\ProductCategory;
use Illuminate\Database\Eloquent\Builder;
use Orchid\Filters\Filter;
use Orchid\Screen\Field;
use Orchid\Screen\Fields\Select;

class CategoryFilter extends Filter
{
    /**
     * @var string|array<int, string>
     */
    public $name = 'Категория';

    /**
     * @var array<int, string>
     */
    public $parameters = ['category_id'];

    /**
     * @return array<Field>
     */
    public function display(): array
    {
        $categories = ProductCategory::query()
            ->orderBy('title')
            ->pluck('title', 'id')
            ->toArray();

        return [
            Select::make('category_id')
                ->title('Категория')
                ->options(['' => 'Все категории'] + $categories)
                ->value($this->request->get('category_id'))
                ->empty()
                ->placeholder('Выберите категорию')
                ->help('Фильтр по категории товара'),
        ];
    }

    public function run(Builder $query): Builder
    {
        $categoryId = $this->request->get('category_id');

        if (! empty($categoryId)) {
            return $query->where('category_id', $categoryId);
        }

        return $query;
    }
}
