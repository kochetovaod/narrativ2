<?php

declare(strict_types=1);

namespace App\Orchid\Screens\ProductCategory;

use App\Models\ProductCategory;
use App\Orchid\Filters\StatusFilter;
use Orchid\Screen\Actions\Link;
use Orchid\Screen\Screen;
use Orchid\Screen\TD;
use Orchid\Support\Facades\Layout;

class ProductCategoryListScreen extends Screen
{
    /**
     * @var string|array<int, string>
     */
    public $permission = 'platform.systems.product_categories';

    public function query(): iterable
    {
        return [
            'categories' => ProductCategory::with('products')
                ->filters([SearchFilter::class, StatusFilter::class])
                ->defaultSort('id', 'desc')
                ->paginate(),
        ];
    }

    /**
     * @return array<int, array<int, Filter>>
     */
    public function filters(): array
    {
        return [
            SearchFilter::class,
            StatusFilter::class,
        ];
    }

    public function name(): ?string
    {
        return __('Категории продукции');
    }

    public function description(): ?string
    {
        return __('Управление категориями товаров');
    }

    public function commandBar(): iterable
    {
        return [
            Link::make(__('Добавить категорию'))
                ->icon('plus')
                ->route('platform.systems.product_categories.create'),
        ];
    }

    public function layout(): iterable
    {
        return [
            Layout::table('categories', [
                TD::make('title', __('Название'))
                    ->render(fn (ProductCategory $category) => Link::make($category->title)
                        ->route('platform.systems.product_categories.edit', $category))
                    ->width('40%'),
                TD::make('slug', __('URL'))
                    ->render(fn (ProductCategory $category) => $category->slug)
                    ->width('20%'),
                TD::make('products_count', __('Товаров'))
                    ->render(fn (ProductCategory $category) => $category->products->count())
                    ->width('10%'),
                TD::make('status', __('Статус'))
                    ->render(fn (ProductCategory $category) => $category->status === 'published'
                        ? __('Опубликовано')
                        : __('Черновик'))
                    ->width('15%')
                    ->filter(
                        TD::FILTER_SELECT,
                        [
                            'draft' => __('Черновик'),
                            'published' => __('Опубликовано'),
                        ]
                    ),
                TD::make('updated_at', __('Обновлено'))
                    ->render(fn (ProductCategory $category) => $category->updated_at?->toDateTimeString())
                    ->sort()
                    ->width('15%'),
            ]),
        ];
    }
}

