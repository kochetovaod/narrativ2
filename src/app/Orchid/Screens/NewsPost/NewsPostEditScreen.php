<?php

declare(strict_types=1);

namespace App\Orchid\Screens\NewsPost;

use App\Models\NewsPost;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Orchid\Screen\Actions\Button;
use Orchid\Screen\Actions\Link;
use Orchid\Screen\Fields\DateTimer;
use Orchid\Screen\Fields\Input;
use Orchid\Screen\Fields\Picture;
use Orchid\Screen\Fields\Select;
use Orchid\Screen\Fields\Switcher;
use Orchid\Screen\Fields\TextArea;
use Orchid\Screen\Screen;
use Orchid\Support\Facades\Alert;
use Orchid\Support\Facades\Layout;

class NewsPostEditScreen extends Screen
{
    public NewsPost $news;

    /**
     * @var string|array<int, string>
     */
    public $permission = 'platform.systems.news_posts';

    public function query(NewsPost $news): iterable
    {
        return [
            'news' => $news,
        ];
    }

    public function name(): ?string
    {
        return $this->news->exists
            ? __('Редактирование новости')
            : __('Создание новости');
    }

    public function description(): ?string
    {
        return __('Управление новостями и пресс-релизами');
    }

    public function commandBar(): iterable
    {
        return [
            Button::make(__('Сохранить'))
                ->icon('check')
                ->method('save'),

            Button::make(__('Удалить'))
                ->icon('trash')
                ->confirm(__('Удалить эту новость?'))
                ->method('remove')
                ->canSee($this->news->exists),

            Link::make(__('Назад'))
                ->icon('action-undo')
                ->route('platform.systems.news_posts'),
        ];
    }

    public function layout(): iterable
    {
        return [
            Layout::tabs([
                __('Основные') => Layout::rows([
                    Input::make('news.title')
                        ->title(__('Заголовок новости'))
                        ->placeholder(__('Например: Открытие нового производственного цеха'))
                        ->required()
                        ->help(__('Заголовок новости для отображения в списке и на странице')),

                    Input::make('news.slug')
                        ->title(__('URL'))
                        ->placeholder(__('otkrytie-novogo-ceha'))
                        ->required()
                        ->help(__('URL адрес новости: /news/[slug]')),

                    TextArea::make('news.excerpt')
                        ->title(__('Анонс'))
                        ->placeholder(__('Краткий анонс новости'))
                        ->rows(4)
                        ->help(__('Краткое описание для листинга новостей')),

                    DateTimer::make('news.published_at')
                        ->title(__('Дата публикации'))
                        ->placeholder(__('Дата и время публикации'))
                        ->help(__('Оставьте пустым для сохранения как черновик')),

                    Select::make('news.status')
                        ->title(__('Статус'))
                        ->options([
                            'draft' => __('Черновик'),
                            'published' => __('Опубликовано'),
                        ])
                        ->default('draft'),

                    Switcher::make('news.publish_now')
                        ->title(__('Опубликовать сейчас'))
                        ->sendTrueOrFalse()
                        ->help(__('При включении статус автоматически станет "Опубликовано"')),
                ])->title(__('Основная информация')),

                __('Контент') => Layout::rows([
                    TextArea::make('news.content.text')
                        ->title(__('Содержимое новости'))
                        ->placeholder(__('Полный текст новости'))
                        ->rows(12)
                        ->help(__('Основной текст новости с детальной информацией')),
                ])->title(__('Текст новости')),

                __('Медиа') => Layout::rows([
                    Picture::make('news.main_image')
                        ->title(__('Главное изображение'))
                        ->targetId()
                        ->help(__('Основное изображение для новости')),
                    
                    Picture::make('news.gallery_images')
                        ->title(__('Галерея изображений'))
                        ->targetId()
                        ->multiple()
                        ->help(__('Дополнительные изображения к новости')),
                ])->title(__('Изображения')),

                __('SEO') => Layout::rows([
                    Input::make('news.seo.title')
                        ->title(__('Title'))
                        ->placeholder(__('Заголовок страницы'))
                        ->help(__('Оставляйте пустым для автогенерации')),

                    Input::make('news.seo.description')
                        ->title(__('Description'))
                        ->placeholder(__('Описание страницы'))
                        ->help(__('Описание для поисковых систем')),

                    Input::make('news.seo.h1')
                        ->title(__('H1'))
                        ->placeholder(__('Заголовок H1'))
                        ->help(__('Главный заголовок страницы')),
                ])->title(__('Настройки SEO')),
            ]),
        ];
    }

    public function save(NewsPost $news, Request $request): void
    {
        $payload = $request->validate([
            'news.title' => ['required', 'string', 'max:255'],
            'news.slug' => [
                'required',
                'string',
                'max:255',
                Rule::unique(NewsPost::class, 'slug')->ignore($news),
            ],
            'news.excerpt' => ['nullable', 'string'],
            'news.content.text' => ['nullable', 'string'],
            'news.status' => ['required', 'in:draft,published'],
            'news.published_at' => ['nullable', 'date'],
            'news.publish_now' => ['nullable', 'boolean'],
            'news.seo.title' => ['nullable', 'string', 'max:255'],
            'news.seo.description' => ['nullable', 'string', 'max:500'],
            'news.seo.h1' => ['nullable', 'string', 'max:255'],
        ]);

        $newsData = $payload['news'];
        $seoData = $newsData['seo'] ?? [];
        $contentData = $newsData['content'] ?? [];

        // Установка статуса публикации
        if (!empty($newsData['publish_now'])) {
            $newsData['status'] = 'published';
            $newsData['published_at'] = $newsData['published_at'] ?: now();
        } elseif (empty($newsData['published_at']) && $newsData['status'] === 'published') {
            $newsData['published_at'] = now();
        }

        $news->fill([
            'title' => $newsData['title'],
            'slug' => $newsData['slug'],
            'excerpt' => $newsData['excerpt'],
            'status' => $newsData['status'],
            'published_at' => $newsData['published_at'],
            'content' => !empty($contentData) ? $contentData : null,
            'seo' => !empty($seoData) ? $seoData : null,
        ]);

        $news->save();

        Alert::info(__('Новость сохранена'));

        $this->redirect(route('platform.systems.news_posts'));
    }

    public function remove(NewsPost $news): void
    {
        $news->delete();

        Alert::info(__('Новость удалена'));

        $this->redirect(route('platform.systems.news_posts'));
    }
}

