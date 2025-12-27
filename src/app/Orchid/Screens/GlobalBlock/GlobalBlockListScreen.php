<?php

declare(strict_types=1);

namespace App\Orchid\Screens\GlobalBlock;

use App\Models\GlobalBlock;
use App\Orchid\Filters\StatusFilter;
use App\Orchid\Filters\SearchFilter;
use Orchid\Screen\Actions\Link;
use Orchid\Screen\Screen;
use Orchid\Screen\TD;
use Orchid\Support\Facades\Layout;

class GlobalBlockListScreen extends Screen
{
    /**
     * @var string|array<int, string>
     */
    public $permission = 'platform.page_builder';

    public function query(): iterable
    {
        return [
            'globalBlocks' => GlobalBlock::with('mediaLinks')
                ->filters([SearchFilter::class])
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
        ];
    }

    public function name(): ?string
    {
        return __('Глобальные блоки');
    }

    public function description(): ?string
    {
        return __('Управление переиспользуемыми блоками для страниц');
    }

    public function commandBar(): iterable
    {
        return [
            Link::make(__('Добавить блок'))
                ->icon('plus')
                ->route('platform.systems.global-blocks.create'),
        ];
    }

    public function layout(): iterable
    {
        return [
            Layout::table('globalBlocks', [
                TD::make('code', __('Код'))
                    ->render(fn (GlobalBlock $block) => $block->code)
                    ->width('15%'),
                TD::make('title', __('Название'))
                    ->render(fn (GlobalBlock $block) => Link::make($block->title)
                        ->route('platform.systems.global-blocks.edit', $block))
                    ->width('30%'),
                TD::make('content_type', __('Тип блока'))
                    ->render(fn (GlobalBlock $block) => $this->getBlockType($block))
                    ->width('20%'),
                TD::make('is_active', __('Статус'))
                    ->render(fn (GlobalBlock $block) => $block->is_active
                        ? __('Активен')
                        : __('Неактивен'))
                    ->width('15%')
                    ->filter(
                        TD::FILTER_SELECT,
                        [
                            '1' => __('Активен'),
                            '0' => __('Неактивен'),
                        ]
                    ),
                TD::make('updated_at', __('Обновлено'))
                    ->render(fn (GlobalBlock $block) => $block->updated_at?->toDateTimeString())
                    ->sort()
                    ->width('20%'),
            ]),
        ];
    }

    private function getBlockType(GlobalBlock $block): string
    {
        $content = $block->content ?? [];
        
        if (!is_array($content)) {
            return __('Неизвестный');
        }

        // Определяем тип блока по содержимому
        if (isset($content['type'])) {
            return match($content['type']) {
                'cta_form' => __('CTA форма'),
                'contact_info' => __('Контакты'),
                'advantages' => __('Преимущества'),
                'about_company' => __('О компании'),
                'social_links' => __('Социальные сети'),
                'custom' => __('Пользовательский'),
                default => __('Неизвестный')
            };
        }

        // Определяем по ключам контента
        if (isset($content['phone']) || isset($content['email'])) {
            return __('Контакты');
        }

        if (isset($content['advantages'])) {
            return __('Преимущества');
        }

        if (isset($content['social'])) {
            return __('Социальные сети');
        }

        return __('Пользовательский');
    }
}
