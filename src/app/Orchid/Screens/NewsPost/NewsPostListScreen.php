<?php

declare(strict_types=1);

namespace App\Orchid\Screens\NewsPost;

use App\Models\NewsPost;
use App\Orchid\Filters\StatusFilter;
use App\Orchid\Filters\SearchFilter;
use Orchid\Screen\Actions\Link;
use Orchid\Screen\Screen;
use Orchid\Screen\TD;
use Orchid\Support\Facades\Layout;

class NewsPostListScreen extends Screen
{
    /**
     * @var string|array<int, string>
     */
    public $permission = 'platform.systems.news_posts';

    public function query(): iterable
    {
        return [
            'news' => NewsPost::with('mediaLinks')
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
        return __('Новости');
    }

    public function description(): ?string
    {
        return __('Управление новостями и пресс-релизами');
    }

    public function commandBar(): iterable
    {
        return [
            Link::make(__('Добавить новость'))
                ->icon('plus')
                ->route('platform.systems.news_posts.create'),
        ];
    }

    public function layout(): iterable
    {
        return [
            Layout::table('news', [
                TD::make('title', __('Заголовок'))
                    ->render(fn (NewsPost $news) => Link::make($news->title)
                        ->route('platform.systems.news_posts.edit', $news))
                    ->width('35%'),
                TD::make('excerpt', __('Анонс'))
                    ->render(fn (NewsPost $news) => \Str::limit($news->excerpt, 60))
                    ->width('30%'),
                TD::make('published_at', __('Дата публикации'))
                    ->render(fn (NewsPost $news) => $news->published_at?->format('d.m.Y H:i'))
                    ->sort()
                    ->width('15%'),
                TD::make('status', __('Статус'))
                    ->render(fn (NewsPost $news) => $news->status === 'published'
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
                    ->render(fn (NewsPost $news) => $news->updated_at?->toDateTimeString())
                    ->sort()
                    ->width('10%'),
            ]),
        ];
    }
}

