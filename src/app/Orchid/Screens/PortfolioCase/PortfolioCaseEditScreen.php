<?php

declare(strict_types=1);

namespace App\Orchid\Screens\PortfolioCase;

use App\Models\PortfolioCase;
use App\Models\Product;
use App\Models\Service;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Orchid\Screen\Actions\Button;
use Orchid\Screen\Actions\Link;
use Orchid\Screen\Fields\CheckBoxList;
use Orchid\Screen\Fields\DateTimer;
use Orchid\Screen\Fields\Input;
use Orchid\Screen\Fields\Picture;
use Orchid\Screen\Fields\Select;
use Orchid\Screen\Fields\Switcher;
use Orchid\Screen\Fields\TextArea;
use Orchid\Screen\Screen;
use Orchid\Support\Facades\Alert;
use Orchid\Support\Facades\Layout;

class PortfolioCaseEditScreen extends Screen
{
    public PortfolioCase $case;

    /**
     * @var string|array<int, string>
     */
    public $permission = 'platform.systems.portfolio_cases';

    public function query(PortfolioCase $case): iterable
    {
        $case->load(['products', 'services']);

        $products = Product::query()
            ->where('status', 'published')
            ->with('category')
            ->orderBy('title')
            ->get()
            ->mapWithKeys(function (Product $product) {
                $categoryTitle = $product->category ? $product->category->title : 'Без категории';

                return [$product->id => "{$product->title} ({$categoryTitle})"];
            })
            ->all();

        $services = Service::query()
            ->where('status', 'published')
            ->orderBy('title')
            ->pluck('title', 'id')
            ->all();

        $selectedProducts = $case->products->pluck('id')->toArray();
        $selectedServices = $case->services->pluck('id')->toArray();

        return [
            'case' => $case,
            'products' => $products,
            'services' => $services,
            'selected_products' => $selectedProducts,
            'selected_services' => $selectedServices,
        ];
    }

    public function name(): ?string
    {
        return $this->case->exists
            ? __('Редактирование кейса')
            : __('Создание кейса');
    }

    public function description(): ?string
    {
        return __('Управление кейсами и их связями с товарами/услугами');
    }

    public function commandBar(): iterable
    {
        return [
            Button::make(__('Сохранить'))
                ->icon('check')
                ->method('save'),

            Button::make(__('Удалить'))
                ->icon('trash')
                ->confirm(__('Удалить этот кейс?'))
                ->method('remove')
                ->canSee($this->case->exists),

            Link::make(__('Назад'))
                ->icon('action-undo')
                ->route('platform.systems.portfolio_cases'),
        ];
    }

    public function layout(): iterable
    {
        return [
            Layout::tabs([
                __('Основные') => Layout::rows([
                    Input::make('case.title')
                        ->title(__('Название кейса'))
                        ->placeholder(__('Например: Вывеска для ресторана "Вкус жизни"'))
                        ->required()
                        ->help(__('Название проекта или кейса')),

                    Input::make('case.slug')
                        ->title(__('URL'))
                        ->placeholder(__('viveska-restoran-vkus-zhizni'))
                        ->required()
                        ->help(__('URL адрес кейса: /portfolio/[slug]')),

                    TextArea::make('case.description.content')
                        ->title(__('Описание кейса'))
                        ->placeholder(__('Подробное описание проекта, задач, решений'))
                        ->rows(8)
                        ->help(__('Описание проекта с деталями выполнения')),

                    DateTimer::make('case.date')
                        ->title(__('Дата проекта'))
                        ->placeholder(__('Дата завершения проекта'))
                        ->help(__('Дата, когда был завершен проект')),

                    Select::make('case.status')
                        ->title(__('Статус'))
                        ->options([
                            'draft' => __('Черновик'),
                            'published' => __('Опубликовано'),
                        ])
                        ->default('draft'),

                    Switcher::make('case.published_at')
                        ->title(__('Опубликовать сейчас'))
                        ->sendTrueOrFalse()
                        ->help(__('При включении статус автоматически станет "Опубликовано"')),
                ])->title(__('Основная информация')),

                __('Клиент') => Layout::rows([
                    Input::make('case.client_name')
                        ->title(__('Название клиента'))
                        ->placeholder(__('Например: Ресторан "Вкус жизни"'))
                        ->help(__('Полное название клиента')),

                    Switcher::make('case.is_nda')
                        ->title(__('NDA (конфиденциальность)'))
                        ->sendTrueOrFalse()
                        ->help(__('При включении клиент будет скрыт на публичной части')),

                    Input::make('case.public_client_label')
                        ->title(__('Публичное название (при NDA)'))
                        ->placeholder(__('Например: Ресторан в центре города'))
                        ->help(__('Отображается публично вместо реального названия при NDA')),
                ])->title(__('Информация о клиенте')),

                __('Связи') => Layout::rows([
                    CheckBoxList::make('case.products')
                        ->title(__('Связанные товары'))
                        ->options($this->products)
                        ->value($this->selected_products)
                        ->help(__('Товары, которые использовались в этом проекте')),

                    CheckBoxList::make('case.services')
                        ->title(__('Связанные услуги'))
                        ->options($this->services)
                        ->value($this->selected_services)
                        ->help(__('Услуги, которые оказывались в этом проекте')),
                ])->title(__('Связи с товарами и услугами')),

                __('Медиа') => Layout::rows([
                    Picture::make('case.gallery_images')
                        ->title(__('Галерея изображений'))
                        ->targetId()
                        ->multiple()
                        ->help(__('Изображения проекта/кейса')),
                ])->title(__('Изображения')),

                __('SEO') => Layout::rows([
                    Input::make('case.seo.title')
                        ->title(__('Title'))
                        ->placeholder(__('Заголовок страницы'))
                        ->help(__('Оставляйте пустым для автогенерации')),

                    Input::make('case.seo.description')
                        ->title(__('Description'))
                        ->placeholder(__('Описание страницы'))
                        ->help(__('Описание для поисковых систем')),

                    Input::make('case.seo.h1')
                        ->title(__('H1'))
                        ->placeholder(__('Заголовок H1'))
                        ->help(__('Главный заголовок страницы')),
                ])->title(__('Настройки SEO')),
            ]),
        ];
    }

    public function save(PortfolioCase $case, Request $request): void
    {
        $payload = $request->validate([
            'case.title' => ['required', 'string', 'max:255'],
            'case.slug' => [
                'required',
                'string',
                'max:255',
                Rule::unique(PortfolioCase::class, 'slug')->ignore($case),
            ],
            'case.description.content' => ['nullable', 'string'],
            'case.client_name' => ['nullable', 'string', 'max:255'],
            'case.is_nda' => ['nullable', 'boolean'],
            'case.public_client_label' => ['nullable', 'string', 'max:255'],
            'case.date' => ['nullable', 'date'],
            'case.status' => ['required', 'in:draft,published'],
            'case.products' => ['array'],
            'case.products.*' => ['exists:products,id'],
            'case.services' => ['array'],
            'case.services.*' => ['exists:services,id'],
            'case.seo.title' => ['nullable', 'string', 'max:255'],
            'case.seo.description' => ['nullable', 'string', 'max:500'],
            'case.seo.h1' => ['nullable', 'string', 'max:255'],
            'case.published_at' => ['nullable', 'boolean'],
        ]);

        $caseData = $payload['case'];
        $seoData = $caseData['seo'] ?? [];
        $descriptionData = $caseData['description'] ?? [];

        // Установка статуса публикации
        if (! empty($caseData['published_at'])) {
            $caseData['status'] = 'published';
            $caseData['published_at'] = now();
        } else {
            $caseData['published_at'] = null;
        }

        $case->fill([
            'title' => $caseData['title'],
            'slug' => $caseData['slug'],
            'description' => ! empty($descriptionData) ? $descriptionData : null,
            'client_name' => $caseData['client_name'],
            'is_nda' => $caseData['is_nda'] ?? false,
            'public_client_label' => $caseData['public_client_label'],
            'date' => $caseData['date'],
            'status' => $caseData['status'],
            'seo' => ! empty($seoData) ? $seoData : null,
        ]);

        $case->save();

        // Обновление связей
        if (isset($caseData['products'])) {
            $case->products()->sync($caseData['products']);
        }

        if (isset($caseData['services'])) {
            $case->services()->sync($caseData['services']);
        }

        Alert::info(__('Кейс сохранен'));

        $this->redirect(route('platform.systems.portfolio_cases'));
    }

    public function remove(PortfolioCase $case): void
    {
        $case->products()->detach();
        $case->services()->detach();
        $case->delete();

        Alert::info(__('Кейс удален'));

        $this->redirect(route('platform.systems.portfolio_cases'));
    }
}
