<?php

declare(strict_types=1);

namespace App\Orchid\Screens\Service;

use App\Models\PortfolioCase;
use App\Models\Service;
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

class ServiceEditScreen extends Screen
{
    public Service $service;

    /**
     * @var string|array<int, string>
     */
    public $permission = 'platform.systems.services';

    public function query(Service $service): iterable
    {
        $service->load('portfolioCases');

        $cases = PortfolioCase::query()
            ->where('status', 'published')
            ->orderBy('title')
            ->pluck('title', 'id')
            ->all();

        $selectedCases = $service->portfolioCases->pluck('id')->toArray();

        return [
            'service' => $service,
            'cases' => $cases,
            'selected_cases' => $selectedCases,
        ];
    }

    public function name(): ?string
    {
        return $this->service->exists
            ? __('Редактирование услуги')
            : __('Создание услуги');
    }

    public function description(): ?string
    {
        return __('Управление услугами и связанными кейсами');
    }

    public function commandBar(): iterable
    {
        return [
            Button::make(__('Сохранить'))
                ->icon('check')
                ->method('save'),

            Button::make(__('Удалить'))
                ->icon('trash')
                ->confirm(__('Удалить эту услугу?'))
                ->method('remove')
                ->canSee($this->service->exists),

            Link::make(__('Назад'))
                ->icon('action-undo')
                ->route('platform.systems.services'),
        ];
    }

    public function layout(): iterable
    {
        return [
            Layout::tabs([
                __('Основные') => Layout::rows([
                    Input::make('service.title')
                        ->title(__('Название услуги'))
                        ->placeholder(__('Например: Проектирование вывесок'))
                        ->required()
                        ->help(__('Полное название услуги')),

                    Input::make('service.slug')
                        ->title(__('URL'))
                        ->placeholder(__('proektirovanie-vivesok'))
                        ->required()
                        ->help(__('URL адрес услуги: /uslugi/[slug]')),

                    Select::make('service.status')
                        ->title(__('Статус'))
                        ->options([
                            'draft' => __('Черновик'),
                            'published' => __('Опубликовано'),
                        ])
                        ->default('draft'),

                    Switcher::make('service.published_at')
                        ->title(__('Опубликовать сейчас'))
                        ->sendTrueOrFalse()
                        ->help(__('При включении статус автоматически станет "Опубликовано"')),

                    Switcher::make('service.show_cases')
                        ->title(__('Показывать кейсы'))
                        ->sendTrueOrFalse()
                        ->help(__('Отображать блок "Примеры работ" на странице услуги')),
                ])->title(__('Основная информация')),

                __('Контент') => Layout::rows([
                    TextArea::make('service.content.description')
                        ->title(__('Описание услуги'))
                        ->placeholder(__('Подробное описание услуги'))
                        ->rows(10)
                        ->help(__('Полное описание с этапами, преимуществами, особенностями')),

                    TextArea::make('service.content.stages')
                        ->title(__('Этапы работы'))
                        ->placeholder(__('Список этапов работы'))
                        ->rows(6)
                        ->help(__('Описание этапов оказания услуги')),

                    TextArea::make('service.content.benefits')
                        ->title(__('Преимущества'))
                        ->placeholder(__('Ключевые преимущества'))
                        ->rows(4)
                        ->help(__('Что отличает эту услугу')),
                ])->title(__('Контент')),

                __('Связи') => Layout::rows([
                    CheckBoxList::make('service.portfolio_cases')
                        ->title(__('Связанные кейсы'))
                        ->options($this->cases)
                        ->value($this->selected_cases)
                        ->help(__('Кейсы, демонстрирующие эту услугу')),
                ])->title(__('Связи с кейсами')),

                __('Медиа') => Layout::rows([
                    Picture::make('service.gallery_images')
                        ->title(__('Галерея изображений'))
                        ->targetId()
                        ->multiple()
                        ->help(__('Изображения для демонстрации услуги')),
                ])->title(__('Изображения')),

                __('SEO') => Layout::rows([
                    Input::make('service.seo.title')
                        ->title(__('Title'))
                        ->placeholder(__('Заголовок страницы'))
                        ->help(__('Оставляйте пустым для автогенерации')),

                    Input::make('service.seo.description')
                        ->title(__('Description'))
                        ->placeholder(__('Описание страницы'))
                        ->help(__('Описание для поисковых систем')),

                    Input::make('service.seo.h1')
                        ->title(__('H1'))
                        ->placeholder(__('Заголовок H1'))
                        ->help(__('Главный заголовок страницы')),
                ])->title(__('Настройки SEO')),
            ]),
        ];
    }

    public function save(Service $service, Request $request): void
    {
        $payload = $request->validate([
            'service.title' => ['required', 'string', 'max:255'],
            'service.slug' => [
                'required',
                'string',
                'max:255',
                Rule::unique(Service::class, 'slug')->ignore($service),
            ],
            'service.content.description' => ['nullable', 'string'],
            'service.content.stages' => ['nullable', 'string'],
            'service.content.benefits' => ['nullable', 'string'],
            'service.status' => ['required', 'in:draft,published'],
            'service.show_cases' => ['nullable', 'boolean'],
            'service.portfolio_cases' => ['array'],
            'service.portfolio_cases.*' => ['exists:portfolio_cases,id'],
            'service.seo.title' => ['nullable', 'string', 'max:255'],
            'service.seo.description' => ['nullable', 'string', 'max:500'],
            'service.seo.h1' => ['nullable', 'string', 'max:255'],
            'service.published_at' => ['nullable', 'boolean'],
        ]);

        $serviceData = $payload['service'];
        $seoData = $serviceData['seo'] ?? [];
        $contentData = $serviceData['content'] ?? [];

        // Установка статуса публикации
        if (! empty($serviceData['published_at'])) {
            $serviceData['status'] = 'published';
            $serviceData['published_at'] = now();
        } else {
            $serviceData['published_at'] = null;
        }

        $service->fill([
            'title' => $serviceData['title'],
            'slug' => $serviceData['slug'],
            'status' => $serviceData['status'],
            'show_cases' => $serviceData['show_cases'] ?? false,
            'content' => ! empty($contentData) ? $contentData : null,
            'seo' => ! empty($seoData) ? $seoData : null,
        ]);

        $service->save();

        // Обновление связей с кейсами
        if (isset($serviceData['portfolio_cases'])) {
            $service->portfolioCases()->sync($serviceData['portfolio_cases']);
        }

        Alert::info(__('Услуга сохранена'));

        $this->redirect(route('platform.systems.services'));
    }

    public function remove(Service $service): void
    {
        $service->portfolioCases()->detach();
        $service->delete();

        Alert::info(__('Услуга удалена'));

        $this->redirect(route('platform.systems.services'));
    }
}
