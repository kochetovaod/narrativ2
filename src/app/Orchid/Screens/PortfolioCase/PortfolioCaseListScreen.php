<?php

declare(strict_types=1);

namespace App\Orchid\Screens\PortfolioCase;

use App\Models\PortfolioCase;
use App\Orchid\Filters\SearchFilter;
use App\Orchid\Filters\StatusFilter;
use Orchid\Screen\Actions\Link;
use Orchid\Screen\Screen;
use Orchid\Screen\TD;
use Orchid\Support\Facades\Layout;

class PortfolioCaseListScreen extends Screen
{
    /**
     * @var string|array<int, string>
     */
    public $permission = 'platform.systems.portfolio_cases';

    public function query(): iterable
    {
        return [
            'cases' => PortfolioCase::with(['products', 'services'])
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
        return __('Портфолио (кейсы)');
    }

    public function description(): ?string
    {
        return __('Управление кейсами и проектами');
    }

    public function commandBar(): iterable
    {
        return [
            Link::make(__('Добавить кейс'))
                ->icon('plus')
                ->route('platform.systems.portfolio_cases.create'),
        ];
    }

    public function layout(): iterable
    {
        return [
            Layout::table('cases', [
                TD::make('title', __('Название'))
                    ->render(fn (PortfolioCase $case) => Link::make($case->title)
                        ->route('platform.systems.portfolio_cases.edit', $case))
                    ->width('35%'),
                TD::make('client_name', __('Клиент'))
                    ->render(function (PortfolioCase $case) {
                        if ($case->is_nda) {
                            return $case->public_client_label ?: __('NDA');
                        }

                        return $case->client_name ?: '—';
                    })
                    ->width('15%'),
                TD::make('products_count', __('Товаров'))
                    ->render(fn (PortfolioCase $case) => $case->products->count())
                    ->width('8%'),
                TD::make('services_count', __('Услуг'))
                    ->render(fn (PortfolioCase $case) => $case->services->count())
                    ->width('8%'),
                TD::make('date', __('Дата'))
                    ->render(fn (PortfolioCase $case) => $case->date?->format('d.m.Y'))
                    ->sort()
                    ->width('10%'),
                TD::make('is_nda', __('NDA'))
                    ->render(fn (PortfolioCase $case) => $case->is_nda ? __('Да') : __('Нет'))
                    ->width('8%'),
                TD::make('status', __('Статус'))
                    ->render(fn (PortfolioCase $case) => $case->status === 'published'
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
                    ->render(fn (PortfolioCase $case) => $case->updated_at?->toDateTimeString())
                    ->sort()
                    ->width('6%'),
            ]),
        ];
    }
}
