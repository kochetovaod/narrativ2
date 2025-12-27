<?php

declare(strict_types=1);

namespace App\Orchid\Screens\Page;

use App\Models\Page;
use App\Orchid\Filters\SearchFilter;
use App\Orchid\Filters\StatusFilter;
use Orchid\Screen\Actions\Link;
use Orchid\Screen\Screen;
use Orchid\Screen\TD;
use Orchid\Support\Facades\Layout;

class PageListScreen extends Screen
{
    /**
     * @var string|array<int, string>
     */
    public $permission = 'platform.systems.pages';

    public function query(): iterable
    {
        return [
            'pages' => Page::with('mediaLinks')
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
        return __('Страницы');
    }

    public function description(): ?string
    {
        return __('Управление статическими страницами');
    }

    public function commandBar(): iterable
    {
        return [
            Link::make(__('Добавить страницу'))
                ->icon('plus')
                ->route('platform.systems.pages.create'),
        ];
    }

    public function layout(): iterable
    {
        return [
            Layout::table('pages', [
                TD::make('title', __('Название'))
                    ->render(fn (Page $page) => Link::make($page->title)
                        ->route('platform.systems.pages.edit', $page))
                    ->width('35%'),
                TD::make('slug', __('URL'))
                    ->render(fn (Page $page) => $page->slug)
                    ->width('20%'),
                TD::make('content_blocks_count', __('Блоков'))
                    ->render(fn (Page $page) => count($page->content ?? []))
                    ->width('10%'),
                TD::make('status', __('Статус'))
                    ->render(fn (Page $page) => $page->status === 'published'
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
                    ->render(fn (Page $page) => $page->updated_at?->toDateTimeString())
                    ->sort()
                    ->width('20%'),
            ]),
        ];
    }
}
