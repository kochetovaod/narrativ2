<?php

declare(strict_types=1);

namespace App\Orchid\Screens\Product;

use App\Models\PortfolioCase;
use App\Models\Product;
use App\Models\ProductCategory;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Orchid\Screen\Actions\Button;
use Orchid\Screen\Actions\Link;
use Orchid\Screen\Fields\CheckBoxList;
use Orchid\Screen\Fields\Input;
use Orchid\Screen\Fields\Picture;
use Orchid\Screen\Fields\Select;
use Orchid\Screen\Fields\Switcher;
use Orchid\Screen\Fields\TextArea;
use Orchid\Screen\Screen;
use Orchid\Support\Facades\Alert;
use Orchid\Support\Facades\Layout;

class ProductEditScreen extends Screen
{
    public Product $product;

    /**
     * @var string|array<int, string>
     */
    public $permission = 'platform.systems.products';

    public function query(Product $product): iterable
    {
        $product->load(['category', 'portfolioCases']);

        $categories = ProductCategory::query()
            ->orderBy('title')
            ->pluck('title', 'id')
            ->all();

        $cases = PortfolioCase::query()
            ->where('status', 'published')
            ->orderBy('title')
            ->pluck('title', 'id')
            ->all();

        $selectedCases = $product->portfolioCases->pluck('id')->toArray();

        return [
            'product' => $product,
            'categories' => $categories,
            'cases' => $cases,
            'selected_cases' => $selectedCases,
        ];
    }

    public function name(): ?string
    {
        return $this->product->exists
            ? __('Редактирование товара')
            : __('Создание товара');
    }

    public function description(): ?string
    {
        return __('Управление товарами и их характеристиками');
    }

    public function commandBar(): iterable
    {
        return [
            Button::make(__('Сохранить'))
                ->icon('check')
                ->method('save'),

            Button::make(__('Удалить'))
                ->icon('trash')
                ->confirm(__('Удалить этот товар?'))
                ->method('remove')
                ->canSee($this->product->exists),

            Link::make(__('Назад'))
                ->icon('action-undo')
                ->route('platform.systems.products'),
        ];
    }

    public function layout(): iterable
    {
        return [
            Layout::tabs([
                __('Основные') => Layout::rows([
                    Input::make('product.title')
                        ->title(__('Название товара'))
                        ->placeholder(__('Например: Световые буквы'))
                        ->required()
                        ->help(__('Полное название товара')),

                    Input::make('product.slug')
                        ->title(__('URL'))
                        ->placeholder(__('svetovye-bukvy'))
                        ->required()
                        ->help(__('URL адрес товара: /produkciya/[категория]/[slug]')),

                    Select::make('product.category_id')
                        ->title(__('Категория'))
                        ->options($this->categories)
                        ->required()
                        ->help(__('Категория, к которой относится товар')),

                    TextArea::make('product.short_text')
                        ->title(__('Краткое описание'))
                        ->placeholder(__('Краткое описание для листинга'))
                        ->rows(3)
                        ->help(__('Отображается в списках и превью')),

                    Select::make('product.status')
                        ->title(__('Статус'))
                        ->options([
                            'draft' => __('Черновик'),
                            'published' => __('Опубликовано'),
                        ])
                        ->default('draft'),

                    Switcher::make('product.published_at')
                        ->title(__('Опубликовать сейчас'))
                        ->sendTrueOrFalse()
                        ->help(__('При включении статус автоматически станет "Опубликовано"')),
                ])->title(__('Основная информация')),

                __('Описание') => Layout::rows([
                    TextArea::make('product.description.content')
                        ->title(__('Полное описание'))
                        ->placeholder(__('Подробное описание товара'))
                        ->rows(10)
                        ->help(__('Детальное описание с преимуществами, особенностями')),

                    TextArea::make('product.specs.data')
                        ->title(__('Характеристики'))
                        ->placeholder(__('{"Материал": "Акрил", "Подсветка": "LED"}'))
                        ->rows(6)
                        ->help(__('Характеристики в формате JSON: ключ → значение')),
                ])->title(__('Контент')),

                __('Связи') => Layout::rows([
                    CheckBoxList::make('product.portfolio_cases')
                        ->title(__('Связанные кейсы'))
                        ->options($this->cases)
                        ->value($this->selected_cases)
                        ->help(__('Кейсы, которые демонстрируют этот товар')),
                ])->title(__('Связи с кейсами')),

                __('Медиа') => Layout::rows([
                    Picture::make('product.gallery_images')
                        ->title(__('Галерея изображений'))
                        ->targetId()
                        ->multiple()
                        ->help(__('Основные изображения товара')),
                ])->title(__('Изображения')),

                __('SEO') => Layout::rows([
                    Input::make('product.seo.title')
                        ->title(__('Title'))
                        ->placeholder(__('Заголовок страницы'))
                        ->help(__('Оставляйте пустым для автогенерации')),

                    Input::make('product.seo.description')
                        ->title(__('Description'))
                        ->placeholder(__('Описание страницы'))
                        ->help(__('Описание для поисковых систем')),

                    Input::make('product.seo.h1')
                        ->title(__('H1'))
                        ->placeholder(__('Заголовок H1'))
                        ->help(__('Главный заголовок страницы')),
                ])->title(__('Настройки SEO')),
            ]),
        ];
    }

    public function save(Product $product, Request $request): void
    {
        $payload = $request->validate([
            'product.title' => ['required', 'string', 'max:255'],
            'product.slug' => [
                'required',
                'string',
                'max:255',
                Rule::unique(Product::class, 'slug')->ignore($product),
            ],
            'product.category_id' => ['required', 'exists:product_categories,id'],
            'product.short_text' => ['nullable', 'string'],
            'product.description.content' => ['nullable', 'string'],
            'product.specs.data' => ['nullable', 'string'],
            'product.status' => ['required', 'in:draft,published'],
            'product.portfolio_cases' => ['array'],
            'product.portfolio_cases.*' => ['exists:portfolio_cases,id'],
            'product.seo.title' => ['nullable', 'string', 'max:255'],
            'product.seo.description' => ['nullable', 'string', 'max:500'],
            'product.seo.h1' => ['nullable', 'string', 'max:255'],
            'product.published_at' => ['nullable', 'boolean'],
        ]);

        $productData = $payload['product'];
        $seoData = $productData['seo'] ?? [];
        $descriptionData = $productData['description'] ?? [];
        $specsData = $productData['specs'] ?? [];

        // Установка статуса публикации
        if (! empty($productData['published_at'])) {
            $productData['status'] = 'published';
            $productData['published_at'] = now();
        } else {
            $productData['published_at'] = null;
        }

        // Парсинг характеристик из JSON
        $specs = null;
        if (! empty($specsData['data'])) {
            $specs = json_decode($specsData['data'], true);
            if (json_last_error() !== JSON_ERROR_NONE) {
                Alert::warning(__('Некорректный формат характеристик. Ожидается валидный JSON.'));
            }
        }

        $product->fill([
            'title' => $productData['title'],
            'slug' => $productData['slug'],
            'category_id' => $productData['category_id'],
            'short_text' => $productData['short_text'],
            'status' => $productData['status'],
            'description' => ! empty($descriptionData) ? $descriptionData : null,
            'specs' => $specs,
            'seo' => ! empty($seoData) ? $seoData : null,
        ]);

        $product->save();

        // Обновление связей с кейсами
        if (isset($productData['portfolio_cases'])) {
            $product->portfolioCases()->sync($productData['portfolio_cases']);
        }

        Alert::info(__('Товар сохранен'));

        $this->redirect(route('platform.systems.products'));
    }

    public function remove(Product $product): void
    {
        $product->portfolioCases()->detach();
        $product->delete();

        Alert::info(__('Товар удален'));

        $this->redirect(route('platform.systems.products'));
    }
}
