<?php

declare(strict_types=1);

namespace App\Orchid\Screens\Page;

use App\Models\GlobalBlock;
use App\Models\Page;
use App\Models\ProductCategory;
use App\Models\Service;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Orchid\Screen\Actions\Button;
use Orchid\Screen\Actions\Link;
use Orchid\Screen\Fields\Input;
use Orchid\Screen\Fields\Select;
use Orchid\Screen\Fields\Switcher;
use Orchid\Screen\Screen;
use Orchid\Support\Facades\Alert;
use Orchid\Support\Facades\Layout;

class PageEditScreen extends Screen
{
    public Page $page;

    /**
     * @var string|array<int, string>
     */
    public $permission = 'platform.systems.pages';

    public function query(Page $page): iterable
    {
        return [
            'page' => $page,
            'sections' => $page->sections ?? [],
            'globalBlocks' => GlobalBlock::where('is_active', true)->get(),
            'productCategories' => ProductCategory::where('status', 'published')->get(),
            'services' => Service::where('status', 'published')->get(),
        ];
    }

    public function name(): ?string
    {
        return $this->page->exists
            ? __('Редактирование страницы')
            : __('Создание страницы');
    }

    public function description(): ?string
    {
        return __('Управление страницами с помощью Page Builder');
    }

    public function commandBar(): iterable
    {
        return [
            Button::make(__('Сохранить'))
                ->icon('check')
                ->method('save'),

            Button::make(__('Предпросмотр'))
                ->icon('eye')
                ->method('preview')
                ->canSee($this->page->exists && $this->page->status === 'draft'),

            Button::make(__('Удалить'))
                ->icon('trash')
                ->confirm(__('Удалить эту страницу?'))
                ->method('remove')
                ->canSee($this->page->exists),

            Link::make(__('Назад'))
                ->icon('action-undo')
                ->route('platform.systems.pages'),
        ];
    }

    public function layout(): iterable
    {
        return [
            Layout::tabs([
                __('Основные') => Layout::rows([
                    Input::make('page.code')
                        ->title(__('Код страницы'))
                        ->placeholder(__('home, contacts, about'))
                        ->help(__('Уникальный код для системных страниц (опционально)')),

                    Input::make('page.title')
                        ->title(__('Название страницы'))
                        ->placeholder(__('Например: Главная страница'))
                        ->required()
                        ->help(__('Название страницы для меню и заголовка')),

                    Input::make('page.slug')
                        ->title(__('URL'))
                        ->placeholder(__('home, contacts, about-us'))
                        ->required()
                        ->help(__('URL адрес страницы')),

                    Select::make('page.status')
                        ->title(__('Статус'))
                        ->options([
                            'draft' => __('Черновик'),
                            'published' => __('Опубликовано'),
                        ])
                        ->default('draft'),

                    Switcher::make('page.published_at')
                        ->title(__('Опубликовать сейчас'))
                        ->sendTrueOrFalse()
                        ->help(__('При включении статус автоматически станет "Опубликовано"')),
                ])->title(__('Основная информация')),

                __('Page Builder') => $this->pageBuilderLayout(),

                __('SEO') => Layout::rows([
                    Input::make('page.seo.title')
                        ->title(__('Title'))
                        ->placeholder(__('Заголовок страницы'))
                        ->help(__('Оставляйте пустым для автогенерации')),

                    Input::make('page.seo.description')
                        ->title(__('Description'))
                        ->placeholder(__('Описание страницы'))
                        ->help(__('Описание для поисковых систем')),

                    Input::make('page.seo.h1')
                        ->title(__('H1'))
                        ->placeholder(__('Заголовок H1'))
                        ->help(__('Главный заголовок страницы')),
                ])->title(__('Настройки SEO')),
            ]),
        ];
    }

    private function pageBuilderLayout(): iterable
    {
        return [
            Layout::rows([
                // Добавление новых секций
                Select::make('new_section_type')
                    ->title(__('Добавить секцию'))
                    ->options([
                        'hero' => __('Hero секция'),
                        'text' => __('Текстовая секция'),
                        'categories_grid' => __('Сетка категорий'),
                        'services_list' => __('Список услуг'),
                        'portfolio' => __('Портфолио'),
                        'cta_form' => __('CTA секция'),
                        'contacts' => __('Контакты'),
                        'gallery' => __('Галерея'),
                        'advantages' => __('Преимущества'),
                        'global_block' => __('Глобальный блок'),
                    ])
                    ->help(__('Выберите тип секции для добавления')),

                Button::make(__('Добавить секцию'))
                    ->icon('plus')
                    ->method('addSection')
                    ->class('btn btn-primary mt-2'),
            ])->title(__('Управление секциями')),

            // Отображение существующих секций
            Layout::view('orchid.page-builder.sections-list'),
        ];
    }

    public function save(Page $page, Request $request): void
    {
        $payload = $request->validate([
            'page.code' => [
                'nullable',
                'string',
                'max:255',
                Rule::unique(Page::class, 'code')->ignore($page),
            ],
            'page.title' => ['required', 'string', 'max:255'],
            'page.slug' => [
                'required',
                'string',
                'max:255',
                Rule::unique(Page::class, 'slug')->ignore($page),
            ],
            'page.sections' => ['nullable', 'array'],
            'page.status' => ['required', 'in:draft,published'],
            'page.seo.title' => ['nullable', 'string', 'max:255'],
            'page.seo.description' => ['nullable', 'string', 'max:500'],
            'page.seo.h1' => ['nullable', 'string', 'max:255'],
            'page.published_at' => ['nullable', 'boolean'],
        ]);

        $pageData = $payload['page'];
        $seoData = $pageData['seo'] ?? [];

        // Установка статуса публикации
        if (! empty($pageData['published_at'])) {
            $pageData['status'] = 'published';
            $pageData['published_at'] = now();
        } else {
            $pageData['published_at'] = null;
        }

        // Обновляем секции
        $sections = $request->input('sections', []);
        if (! empty($sections)) {
            // Пересортировка по order
            usort($sections, fn ($a, $b) => ($a['order'] ?? 0) <=> ($b['order'] ?? 0));
            $pageData['sections'] = $sections;
        } else {
            $pageData['sections'] = [];
        }

        $page->fill([
            'code' => $pageData['code'],
            'title' => $pageData['title'],
            'slug' => $pageData['slug'],
            'status' => $pageData['status'],
            'sections' => $pageData['sections'],
            'seo' => ! empty($seoData) ? $seoData : null,
        ]);

        $page->save();

        Alert::info(__('Страница сохранена'));

        $this->redirect(route('platform.systems.pages'));
    }

    public function addSection(Request $request): void
    {
        $sectionType = $request->input('new_section_type');

        if (! $sectionType) {
            Alert::warning(__('Выберите тип секции'));

            return;
        }

        // Здесь будет логика добавления секции
        // Пока заглушка
        Alert::info(__("Добавление секции типа: {$sectionType}"));
    }

    public function preview(Page $page): void
    {
        if ($page->status !== 'draft') {
            Alert::warning(__('Предпросмотр доступен только для черновиков'));

            return;
        }

        // Генерируем preview token если его нет
        if (! $page->preview_token) {
            $page->preview_token = \Str::random(32);
            $page->save();
        }

        $previewUrl = route('preview.page', $page->preview_token);

        Alert::info(__("Предпросмотр доступен по адресу: {$previewUrl}"));

        // Открываем в новой вкладке
        echo "<script>window.open('{$previewUrl}', '_blank');</script>";
    }

    public function remove(Page $page): void
    {
        $page->delete();

        Alert::info(__('Страница удалена'));

        $this->redirect(route('platform.systems.pages'));
    }
}
