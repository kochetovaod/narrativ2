<?php

declare(strict_types=1);

namespace App\Orchid\Screens\Service;

use App\Models\Service;
use App\Orchid\Filters\SearchFilter;
use App\Orchid\Filters\StatusFilter;
use Orchid\Screen\Actions\Link;
use Orchid\Screen\Screen;
use Orchid\Screen\TD;
use Orchid\Support\Facades\Layout;

class ServiceListScreen extends Screen
{
    /**
     * @var string|array<int, string>
     */
    public $permission = 'platform.systems.services';

    public function query(): iterable
    {
        return [
            'services' => Service::with('portfolioCases')
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
        return __('Услуги');
    }

    public function description(): ?string
    {
        return __('Управление услугами');
    }

    public function commandBar(): iterable
    {
        return [
            Link::make(__('Добавить услугу'))
                ->icon('plus')
                ->route('platform.systems.services.create'),
        ];
    }

    public function layout(): iterable
    {
        return [
            Layout::table('services', [
                TD::make('title', __('Название'))
                    ->render(fn (Service $service) => Link::make($service->title)
                        ->route('platform.systems.services.edit', $service))
                    ->width('40%'),
                TD::make('slug', __('URL'))
                    ->render(fn (Service $service) => $service->slug)
                    ->width('20%'),
                TD::make('cases_count', __('Кейсов'))
                    ->render(fn (Service $service) => $service->portfolioCases->count())
                    ->width('10%'),
                TD::make('show_cases', __('Показывать кейсы'))
                    ->render(fn (Service $service) => $service->show_cases ? __('Да') : __('Нет'))
                    ->width('10%'),
                TD::make('status', __('Статус'))
                    ->render(fn (Service $service) => $service->status === 'published'
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
                    ->render(fn (Service $service) => $service->updated_at?->toDateTimeString())
                    ->sort()
                    ->width('10%'),
            ]),
        ];
    }
}
