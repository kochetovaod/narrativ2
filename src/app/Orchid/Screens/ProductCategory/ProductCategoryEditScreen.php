<?php

declare(strict_types=1);

namespace App\Orchid\Screens\ProductCategory;

use App\Models\ProductCategory;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Orchid\Screen\Actions\Button;
use Orchid\Screen\Actions\Link;
use Orchid\Screen\Fields\Input;
use Orchid\Screen\Fields\Picture;
use Orchid\Screen\Fields\Select;
use Orchid\Screen\Fields\Switcher;
use Orchid\Screen\Fields\TextArea;
use Orchid\Screen\Screen;
use Orchid\Support\Facades\Alert;
use Orchid\Support\Facades\Layout;

class ProductCategoryEditScreen extends Screen
{
    public ProductCategory $category;

    /**
     * @var string|array<int, string>
     */
    public $permission = 'platform.systems.product_categories';

    public function query(ProductCategory $category): iterable
    {
        return [
            'category' => $category,
        ];
    }

    public function name(): ?string
    {
        return $this->category->exists
            ? __('Редактирование категории')
            : __('Создание категории');
    }

    public function description(): ?string
    {
        return __('Управление категориями товаров');
    }

    public function commandBar(): iterable
    {
        return [
            Button::make(__('Сохранить'))
                ->icon('check')
                ->method('save'),

            Button::make(__('Удалить'))
                ->icon('trash')
                ->confirm(__('Удалить эту категорию? Все товары останутся, но будут без категории.'))
                ->method('remove')
                ->canSee($this->category->exists),

            Link::make(__('Назад'))
                ->icon('action-undo')
                ->route('platform.systems.product_categories'),
        ];
    }

    public function layout(): iterable
    {
        return [
            Layout::tabs([
                __('Основные') => Layout::rows([
                    Input::make('category.title')
                        ->title(__('Название категории'))
                        ->placeholder(__('Например: Вывески'))
                        ->required()
                        ->help(__('Отображается в меню и заголовке страницы')),

                    Input::make('category.slug')
                        ->title(__('URL'))
                        ->placeholder(__('viveski'))
                        ->required()
                        ->help(__('URL адрес категории: /produkciya/[slug]')),

                    TextArea::make('category.intro_text')
                        ->title(__('Вводный текст'))
                        ->placeholder(__('Краткое описание категории'))
                        ->rows(3)
                        ->help(__('Отображается в начале страницы категории')),

                    Select::make('category.status')
                        ->title(__('Статус'))
                        ->options([
                            'draft' => __('Черновик'),
                            'published' => __('Опубликовано'),
                        ])
                        ->default('draft'),

                    Switcher::make('category.published_at')
                        ->title(__('Опубликовать сейчас'))
                        ->sendTrueOrFalse()
                        ->help(__('При включении статус автоматически станет "Опубликовано"')),
                ])->title(__('Основная информация')),

                __('Контент') => Layout::rows([
                    TextArea::make('category.body.content')
                        ->title(__('Описание категории'))
                        ->placeholder(__('Полное описание для SEO и контента'))
                        ->rows(8)
                        ->help(__('Подробное описание с ключевыми словами для SEO')),
                ])->title(__('Контент')),

                __('Медиа') => Layout::rows([
                    Picture::make('category.banner_image')
                        ->title(__('Баннер категории'))
                        ->targetId()
                        ->help(__('Изображение для страницы категории')),
                ])->title(__('Изображения')),

                __('SEO') => Layout::rows([
                    Input::make('category.seo.title')
                        ->title(__('Title'))
                        ->placeholder(__('Заголовок страницы'))
                        ->help(__('Оставляйте пустым для автогенерации')),

                    Input::make('category.seo.description')
                        ->title(__('Description'))
                        ->placeholder(__('Описание страницы'))
                        ->help(__('Описание для поисковых систем')),

                    Input::make('category.seo.h1')
                        ->title(__('H1'))
                        ->placeholder(__('Заголовок H1'))
                        ->help(__('Главный заголовок страницы')),
                ])->title(__('Настройки SEO')),
            ]),
        ];
    }

    public function save(ProductCategory $category, Request $request): void
    {
        $payload = $request->validate([
            'category.title' => ['required', 'string', 'max:255'],
            'category.slug' => [
                'required',
                'string',
                'max:255',
                Rule::unique(ProductCategory::class, 'slug')->ignore($category),
            ],
            'category.intro_text' => ['nullable', 'string'],
            'category.status' => ['required', 'in:draft,published'],
            'category.body.content' => ['nullable', 'string'],
            'category.seo.title' => ['nullable', 'string', 'max:255'],
            'category.seo.description' => ['nullable', 'string', 'max:500'],
            'category.seo.h1' => ['nullable', 'string', 'max:255'],
            'category.published_at' => ['nullable', 'boolean'],
        ]);

        $categoryData = $payload['category'];
        $seoData = $categoryData['seo'] ?? [];
        $bodyData = $categoryData['body'] ?? [];

        // Установка статуса публикации
        if (! empty($categoryData['published_at'])) {
            $categoryData['status'] = 'published';
            $categoryData['published_at'] = now();
        } else {
            $categoryData['published_at'] = null;
        }

        $category->fill([
            'title' => $categoryData['title'],
            'slug' => $categoryData['slug'],
            'intro_text' => $categoryData['intro_text'],
            'status' => $categoryData['status'],
            'body' => ! empty($bodyData) ? $bodyData : null,
            'seo' => ! empty($seoData) ? $seoData : null,
        ]);

        $category->save();

        Alert::info(__('Категория сохранена'));

        $this->redirect(route('platform.systems.product_categories'));
    }

    public function remove(ProductCategory $category): void
    {
        $category->delete();

        Alert::info(__('Категория удалена'));

        $this->redirect(route('platform.systems.product_categories'));
    }
}
