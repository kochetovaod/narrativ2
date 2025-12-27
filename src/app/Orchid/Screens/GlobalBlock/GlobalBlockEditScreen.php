<?php

declare(strict_types=1);

namespace App\Orchid\Screens\GlobalBlock;

use App\Models\GlobalBlock;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Orchid\Screen\Actions\Button;
use Orchid\Screen\Actions\Link;
use Orchid\Screen\Fields\Input;
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
    public $permission = 'platform.systems.global_blocks';

    public function query(GlobalBlock $globalBlock): iterable
    {
        return [
            'globalBlock' => $globalBlock,
            'content' => $globalBlock->content ?? [],
        ];
    }

    public function name(): ?string
    {
        return $this->globalBlock->exists
            ? __('Редактирование глобального блока')
            : __('Создание глобального блока');
    }

    public function description(): ?string
    {
        return __('Управление переиспользуемыми блоками контента');
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
                ->canSee($this->globalBlock->exists && $this->globalBlock->is_active),

            Button::make(__('Удалить'))
                ->icon('trash')
                ->confirm(__('Удалить этот глобальный блок?'))
                ->method('remove')
                ->canSee($this->globalBlock->exists),

            Link::make(__('Назад'))
                ->icon('action-undo')
                ->route('platform.systems.global_blocks'),
        ];
    }

    public function layout(): iterable
    {
        return [
            Layout::tabs([
                __('Основная информация') => Layout::rows([
                    Input::make('globalBlock.code')
                        ->title(__('Код блока'))
                        ->placeholder(__('например: footer-contacts, header-phone'))
                        ->required()
                        ->help(__('Уникальный идентификатор блока для использования в page builder')),

                    Input::make('globalBlock.title')
                        ->title(__('Название'))
                        ->placeholder(__('Например: Контакты в футере'))
                        ->required()
                        ->help(__('Понятное название блока для администраторов')),

                    Switcher::make('globalBlock.is_active')
                        ->title(__('Активность'))
                        ->sendTrueOrFalse()
                        ->help(__('Неактивные блоки не отображаются в page builder')),
                ])->title(__('Основные настройки')),

                __('Содержимое') => $this->contentLayout(),

                __('Предпросмотр') => Layout::view('orchid.global_block.preview'),
            ]),
        ];
    }

    private function contentLayout(): iterable
    {
        return [
            Layout::rows([
                TextArea::make('content.json')
                    ->title(__('JSON содержимое'))
                    ->rows(20)
                    ->placeholder(__('{"type": "contacts", "data": {...}}'))
                    ->help(__('Структурированное содержимое блока в JSON формате'))
                    ->value(function () {
                        return json_encode($this->globalBlock->content ?? [], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
                    }),

                TextArea::make('content.html')
                    ->title(__('HTML содержимое'))
                    ->rows(10)
                    ->placeholder(__('<div>HTML контент блока</div>'))
                    ->help(__('Готовый HTML для отображения (опционально)'))
                    ->value(function () {
                        return is_string($this->globalBlock->content) ? $this->globalBlock->content : '';
                    }),
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
            'globalBlock.is_active' => ['required', 'boolean'],
            'content.json' => ['nullable', 'string'],
            'content.html' => ['nullable', 'string'],
        ]);

        $globalBlockData = $payload['globalBlock'];
        $contentData = $payload['content'];

        // Обрабатываем содержимое
        $content = null;
        if (! empty($contentData['json'])) {
            $decodedJson = json_decode($contentData['json'], true);
            if (json_last_error() === JSON_ERROR_NONE) {
                $content = $decodedJson;
            } else {
                Alert::error(__('Ошибка в JSON: ').json_last_error_msg());

                return;
            }
        } elseif (! empty($contentData['html'])) {
            $content = $contentData['html'];
        }

        $globalBlock->fill([
            'code' => $globalBlockData['code'],
            'title' => $globalBlockData['title'],
            'is_active' => $globalBlockData['is_active'],
            'content' => $content,
        ]);

        $globalBlock->save();

        Alert::info(__('Глобальный блок сохранен'));

        $this->redirect(route('platform.systems.global_blocks'));
    }

    public function preview(GlobalBlock $globalBlock): void
    {
        if (! $globalBlock->is_active) {
            Alert::warning(__('Предпросмотр доступен только для активных блоков'));

            return;
        }

        $previewUrl = route('preview.global_block', $globalBlock->code);

        Alert::info(__("Предпросмотр доступен по адресу: {$previewUrl}"));

        // Открываем в новой вкладке
        echo "<script>window.open('{$previewUrl}', '_blank');</script>";
    }

    public function remove(GlobalBlock $globalBlock): void
    {
        $globalBlock->delete();

        Alert::info(__('Глобальный блок удален'));

        $this->redirect(route('platform.systems.global_blocks'));
    }
}
