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

            // Переиндексируем секции для обеспечения последовательности
            $reindexedSections = [];
            $order = 0;
            foreach ($sections as $section) {
                $section['order'] = $order++;
                $reindexedSections[] = $section;
            }

            $pageData['sections'] = $reindexedSections;
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

        // Получаем текущие секции
        $sections = $this->page->sections ?? [];

        // Создаем новую секцию
        $newSection = [
            'id' => 'section_'.time().'_'.rand(1000, 9999),
            'type' => $sectionType,
            'order' => count($sections),
            'settings' => $this->getDefaultSectionSettings($sectionType),
        ];

        $sections[] = $newSection;
        $this->page->sections = $sections;
        $this->page->save();

        Alert::success(__("Секция '{$this->getSectionTypeLabel($sectionType)}' добавлена"));

        $this->redirect(route('platform.systems.pages.edit', $this->page));
    }

    public function removeSection(Request $request): void
    {
        $sectionId = $request->input('section_id');

        if (! $sectionId) {
            Alert::error(__('ID секции не указан'));

            return;
        }

        $sections = $this->page->sections ?? [];
        $filteredSections = array_filter($sections, fn ($section) => $section['id'] !== $sectionId);

        // Пересортировка
        $reorderedSections = [];
        $order = 0;
        foreach ($filteredSections as $section) {
            $section['order'] = $order++;
            $reorderedSections[] = $section;
        }

        $this->page->sections = $reorderedSections;
        $this->page->save();

        Alert::success(__('Секция удалена'));

        $this->redirect(route('platform.systems.pages.edit', $this->page));
    }

    public function reorderSections(Request $request): void
    {
        $sectionOrders = $request->input('section_orders', []);

        if (empty($sectionOrders)) {
            Alert::error(__('Порядок секций не указан'));

            return;
        }

        $sections = $this->page->sections ?? [];
        $reorderedSections = [];

        // Пересортировываем секции согласно новому порядку
        foreach ($sectionOrders as $orderData) {
            $sectionId = $orderData['id'] ?? null;
            $newOrder = $orderData['order'] ?? 0;

            foreach ($sections as $section) {
                if ($section['id'] === $sectionId) {
                    $section['order'] = $newOrder;
                    $reorderedSections[] = $section;
                    break;
                }
            }
        }

        // Сортируем по order
        usort($reorderedSections, fn ($a, $b) => ($a['order'] ?? 0) <=> ($b['order'] ?? 0));

        $this->page->sections = $reorderedSections;
        $this->page->save();

        Alert::success(__('Порядок секций обновлен'));

        $this->redirect(route('platform.systems.pages.edit', $this->page));
    }

    private function getDefaultSectionSettings(string $sectionType): array
    {
        return match ($sectionType) {
            'hero' => [
                'title' => '',
                'subtitle' => '',
                'background_image' => '',
                'cta_buttons' => [],
            ],
            'text' => [
                'title' => '',
                'content' => '',
                'alignment' => 'left',
            ],
            'categories_grid' => [
                'title' => '',
                'columns_count' => 3,
                'show_count' => false,
                'description' => '',
            ],
            'services_list' => [
                'title' => '',
                'layout_type' => 'grid',
                'description' => '',
            ],
            'portfolio' => [
                'title' => '',
                'limit' => 6,
                'show_filters' => false,
                'description' => '',
            ],
            'cta_form' => [
                'title' => '',
                'description' => '',
                'form_type' => 'call',
            ],
            'contacts' => [
                'title' => '',
                'contact_type' => 'embedded',
                'phone' => '',
                'email' => '',
                'address' => '',
                'work_hours' => '',
                'map_embed' => '',
                'cta_title' => '',
                'cta_text' => '',
                'cta_button_text' => '',
                'cta_button_link' => '',
                'cta_secondary_text' => '',
                'cta_secondary_link' => '',
            ],
            'gallery' => [
                'title' => '',
                'columns_count' => 3,
                'lightbox' => true,
            ],
            'advantages' => [
                'title' => '',
                'advantages' => [],
            ],
            'global_block' => [
                'block_code' => '',
            ],
            default => [],
        };
    }

    private function getSectionTypeLabel(string $sectionType): string
    {
        return match ($sectionType) {
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
            default => __('Неизвестная секция'),
        };
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

        $previewUrl = route('preview.page', ['token' => $page->preview_token]);

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
