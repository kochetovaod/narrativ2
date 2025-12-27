<?php

declare(strict_types=1);

namespace App\Orchid\Screens\Product;

use App\Models\Product;
use App\Orchid\Filters\CategoryFilter;
use App\Orchid\Filters\SearchFilter;
use App\Orchid\Filters\StatusFilter;
use Orchid\Screen\Actions\Link;
use Orchid\Screen\Screen;
use Orchid\Screen\TD;
use Orchid\Support\Facades\Layout;

class ProductListScreen extends Screen
{
    /**
     * @var string|array<int, string>
     */
    public $permission = 'platform.systems.products';

    public function query(): iterable
    {
        return [
            'products' => Product::with(['category', 'portfolioCases'])
                ->filters([SearchFilter::class, StatusFilter::class, CategoryFilter::class])
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
            CategoryFilter::class,
        ];
    }

    public function name(): ?string
    {
        return __('Товары');
    }

    public function description(): ?string
    {
        return __('Управление товарами');
    }

    public function commandBar(): iterable
    {
        return [
            Link::make(__('Добавить товар'))
                ->icon('plus')
                ->route('platform.systems.products.create'),
        ];
    }

    public function layout(): iterable
    {
        return [
            Layout::table('products', [
                TD::make('title', __('Название'))
                    ->render(fn (Product $product) => Link::make($product->title)
                        ->route('platform.systems.products.edit', $product))
                    ->width('35%'),
                TD::make('category.title', __('Категория'))
                    ->render(fn (Product $product) => $product->category?->title ?? '—')
                    ->width('20%'),
                TD::make('slug', __('URL'))
                    ->render(fn (Product $product) => $product->slug)
                    ->width('15%'),
                TD::make('cases_count', __('Кейсов'))
                    ->render(fn (Product $product) => $product->portfolioCases->count())
                    ->width('10%'),
                TD::make('status', __('Статус'))
                    ->render(fn (Product $product) => $product->status === 'published'
                        ? __('Опубликовано')
                        : __('Черновик'))
                    ->width('10%')
                    ->filter(
                        TD::FILTER_SELECT,
                        [
                            'draft' => __('Черновик'),
                            'published' => __('Опубликовано'),
                        ]
                    ),
                TD::make('updated_at', __('Обновлено'))
                    ->render(fn (Product $product) => $product->updated_at?->toDateTimeString())
                    ->sort()
                    ->width('10%'),
            ]),
        ];
    }
}
