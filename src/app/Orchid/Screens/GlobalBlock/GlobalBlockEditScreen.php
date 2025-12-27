<?php

declare(strict_types=1);

namespace App\Orchid\Screens\GlobalBlock;

use App\Models\GlobalBlock;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Orchid\Screen\Actions\Button;
use Orchid\Screen\Actions\Link;
use Orchid\Screen\Fields\Input;
use Orchid\Screen\Fields\Select;
use Orchid\Screen\Fields\Switcher;
use Orchid\Screen\Fields\TextArea;
use Orchid\Screen\Screen;
use Orchid\Support\Facades\Alert;
use Orchid\Support\Facades\Layout;

class GlobalBlockEditScreen extends Screen
{
    public GlobalBlock $globalBlock;

    /**
     * @var string|array<int, string>
     */
    public $permission = 'platform.page_builder';

    public function query(GlobalBlock $globalBlock): iterable
    {
        return [
            'globalBlock' => $globalBlock,
        ];
    }

    public function name(): ?string
    {
        return $this->globalBlock->exists
            ? __('Редактирование блока')
            : __('Создание блока');
    }

    public function description(): ?string
    {
        return __('Управление глобальными блоками контента');
    }

    public function commandBar(): iterable
    {
        return [
            Button::make(__('Сохранить'))
                ->icon('check')
                ->method('save'),

            Button::make(__('Удалить'))
                ->icon('trash')
                ->confirm(__('Удалить этот блок?'))
                ->method('remove')
                ->canSee($this->globalBlock->exists),

            Link::make(__('Назад'))
                ->icon('action-undo')
                ->route('platform.systems.global-blocks'),
        ];
    }

    public function layout(): iterable
    {
        return [
            Layout::tabs([
                __('Основные') => Layout::rows([
                    Input::make('globalBlock.code')
                        ->title(__('Код блока'))
                        ->placeholder(__('contact_form_main'))
                        ->required()
                        ->help(__('Уникальный код для вставки блока на страницу')),

                    Input::make('globalBlock.title')
                        ->title(__('Название блока'))
                        ->placeholder(__('Основная форма контактов'))
                        ->required()
                        ->help(__('Название блока для удобства управления')),

                    Switcher::make('globalBlock.is_active')
                        ->title(__('Статус'))
                        ->placeholder(__('Активен'))
                        ->sendTrueOrFalse()
                        ->help(__('Неактивные блоки не отображаются на сайте')),
                ])->title(__('Основная информация')),

                __('Тип блока') => Layout::rows([
                    Select::make('content_type')
                        ->title(__('Тип контента'))
                        ->options([
                            'cta_form' => __('CTA форма'),
                            'contact_info' => __('Информация о контактах'),
                            'advantages' => __('Блок преимуществ'),
                            'about_company' => __('О компании'),
                            'social_links' => __('Ссылки на социальные сети'),
                            'custom' => __('Пользовательский контент'),
                        ])
                        ->help(__('Выберите тип блока для настройки соответствующих полей')),

                    // CTA Форма
                    'cta_form' => Layout::rows([
                        Input::make('content.form_type')
                            ->title(__('Тип формы'))
                            ->options([
                                'call' => __('Заказать звонок'),
                                'calculation' => __('Получить расчет'),
                                'question' => __('Задать вопрос'),
                            ])
                            ->help(__('Тип формы для отображения')),

                        Input::make('content.form_title')
                            ->title(__('Заголовок формы'))
                            ->placeholder(__('Получить консультацию'))
                            ->help(__('Заголовок отображаемый над формой')),

                        TextArea::make('content.form_description')
                            ->title(__('Описание'))
                            ->placeholder(__('Оставьте заявку и мы свяжемся с вами'))
                            ->rows(3)
                            ->help(__('Дополнительное описание под заголовком')),

                        Input::make('content.submit_text')
                            ->title(__('Текст кнопки'))
                            ->placeholder(__('Отправить заявку'))
                            ->help(__('Текст на кнопке отправки формы')),
                    ])->title(__('Настройки CTA формы')),

                    // Контактная информация
                    'contact_info' => Layout::rows([
                        Input::make('content.phone')
                            ->title(__('Телефон'))
                            ->placeholder(__('+7 (999) 123-45-67'))
                            ->help(__('Основной номер телефона')),

                        Input::make('content.email')
                            ->title(__('Email'))
                            ->placeholder(__('info@example.com'))
                            ->help(__('Основной email адрес')),

                        Input::make('content.address')
                            ->title(__('Адрес'))
                            ->placeholder(__('г. Москва, ул. Примерная, д. 1'))
                            ->help(__('Физический адрес компании')),

                        Input::make('content.work_hours')
                            ->title(__('Часы работы'))
                            ->placeholder(__('Пн-Пт: 9:00-18:00'))
                            ->help(__('Время работы компании')),

                        TextArea::make('content.additional_info')
                            ->title(__('Дополнительная информация'))
                            ->placeholder(__('Дополнительная информация о контактах'))
                            ->rows(4)
                            ->help(__('Любая дополнительная контактная информация')),
                    ])->title(__('Контактная информация')),

                    // Преимущества
                    'advantages' => Layout::rows([
                        TextArea::make('content.advantages_list')
                            ->title(__('Список преимуществ'))
                            ->placeholder(__("Преимущество 1\nПреимущество 2\nПреимущество 3"))
                            ->rows(6)
                            ->help(__('Введите каждое преимущество с новой строки')),

                        Select::make('content.layout')
                            ->title(__('Макет'))
                            ->options([
                                'grid' => __('Сетка'),
                                'list' => __('Список'),
                                'cards' => __('Карточки'),
                            ])
                            ->help(__('Способ отображения преимуществ')),
                    ])->title(__('Блок преимуществ')),

                    // О компании
                    'about_company' => Layout::rows([
                        TextArea::make('content.company_description')
                            ->title(__('Описание компании'))
                            ->placeholder(__('Краткое описание компании'))
                            ->rows(6)
                            ->help(__('Основная информация о компании')),

                        Input::make('content.established_year')
                            ->title(__('Год основания'))
                            ->placeholder(__('2010'))
                            ->help(__('Год основания компании')),

                        Input::make('content.main_services')
                            ->title(__('Основные услуги'))
                            ->placeholder(__('Производство, Проектирование, Монтаж'))
                            ->help(__('Перечислите основные услуги через запятую')),
                    ])->title(__('О компании')),

                    // Социальные сети
                    'social_links' => Layout::rows([
                        Input::make('content.telegram')
                            ->title(__('Telegram'))
                            ->placeholder(__('https://t.me/username'))
                            ->help(__('Ссылка на Telegram канал')),

                        Input::make('content.whatsapp')
                            ->title(__('WhatsApp'))
                            ->placeholder(__('https://wa.me/79991234567'))
                            ->help(__('Ссылка на WhatsApp')),

                        Input::make('content.vk')
                            ->title(__('VK'))
                            ->placeholder(__('https://vk.com/company'))
                            ->help(__('Ссылка на группу VK')),

                        Input::make('content.instagram')
                            ->title(__('Instagram'))
                            ->placeholder(__('https://instagram.com/company'))
                            ->help(__('Ссылка на Instagram')),

                        Input::make('content.youtube')
                            ->title(__('YouTube'))
                            ->placeholder(__('https://youtube.com/channel'))
                            ->help(__('Ссылка на YouTube канал')),
                    ])->title(__('Социальные сети')),

                    // Пользовательский контент
                    'custom' => Layout::rows([
                        TextArea::make('content.custom_html')
                            ->title(__('Пользовательский HTML'))
                            ->placeholder(__('<div>Ваш контент здесь</div>'))
                            ->rows(8)
                            ->help(__('Пользовательский HTML код (используйте осторожно)')),

                        TextArea::make('content.custom_css')
                            ->title(__('Пользовательские стили CSS'))
                            ->placeholder(__('.custom-class { color: red; }'))
                            ->rows(6)
                            ->help(__('CSS стили для блока (опционально)')),
                    ])->title(__('Пользовательский контент')),
                ])->title(__('Контент блока')),
            ]),
        ];
    }

    public function save(GlobalBlock $globalBlock, Request $request): void
    {
        $payload = $request->validate([
            'globalBlock.code' => [
                'required',
                'string',
                'max:255',
                Rule::unique(GlobalBlock::class, 'code')->ignore($globalBlock),
            ],
            'globalBlock.title' => ['required', 'string', 'max:255'],
            'globalBlock.is_active' => ['nullable', 'boolean'],
            'content_type' => ['required', 'string'],
            'content.form_type' => ['nullable', 'string'],
            'content.form_title' => ['nullable', 'string'],
            'content.form_description' => ['nullable', 'string'],
            'content.submit_text' => ['nullable', 'string'],
            'content.phone' => ['nullable', 'string'],
            'content.email' => ['nullable', 'email'],
            'content.address' => ['nullable', 'string'],
            'content.work_hours' => ['nullable', 'string'],
            'content.additional_info' => ['nullable', 'string'],
            'content.advantages_list' => ['nullable', 'string'],
            'content.layout' => ['nullable', 'string'],
            'content.company_description' => ['nullable', 'string'],
            'content.established_year' => ['nullable', 'integer'],
            'content.main_services' => ['nullable', 'string'],
            'content.telegram' => ['nullable', 'url'],
            'content.whatsapp' => ['nullable', 'url'],
            'content.vk' => ['nullable', 'url'],
            'content.instagram' => ['nullable', 'url'],
            'content.youtube' => ['nullable', 'url'],
            'content.custom_html' => ['nullable', 'string'],
            'content.custom_css' => ['nullable', 'string'],
        ]);

        $globalBlockData = $payload['globalBlock'];
        $contentData = $payload['content'] ?? [];
        $contentType = $payload['content_type'];

        // Формируем контент в зависимости от типа
        $finalContent = array_merge($contentData, ['type' => $contentType]);

        // Специальная обработка для преимуществ
        if ($contentType === 'advantages' && ! empty($contentData['advantages_list'])) {
            $advantages = array_filter(
                array_map('trim', explode("\n", $contentData['advantages_list'])),
                fn ($item) => ! empty($item)
            );
            $finalContent['advantages'] = $advantages;
            unset($finalContent['advantages_list']);
        }

        $globalBlock->fill([
            'code' => $globalBlockData['code'],
            'title' => $globalBlockData['title'],
            'content' => $finalContent,
            'is_active' => ! empty($globalBlockData['is_active']),
        ]);

        $globalBlock->save();

        Alert::info(__('Глобальный блок сохранен'));

        $this->redirect(route('platform.systems.global-blocks'));
    }

    public function remove(GlobalBlock $globalBlock): void
    {
        $globalBlock->delete();

        Alert::info(__('Глобальный блок удален'));

        $this->redirect(route('platform.systems.global-blocks'));
    }
}
