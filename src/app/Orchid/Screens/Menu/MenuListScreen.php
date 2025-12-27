<?php

declare(strict_types=1);

namespace App\Orchid\Screens\Menu;

use App\Models\Menu;
use App\Orchid\Permissions\Rbac;
use Orchid\Screen\Actions\Link;
use Orchid\Screen\Screen;
use Orchid\Support\Facades\Layout;

class MenuListScreen extends Screen
{
    /**
     * @var string|array<int, string>
     */
    public $permission = 'platform.menu';

    /**
     * Fetch the navigation menu for editing.
     *
     * @return array<string, Menu|null>
     */
    public function query(): iterable
    {
        return [
            'headerMenu' => Menu::where('code', 'header')->with('items.children')->first(),
            'footerMenu' => Menu::where('code', 'footer')->with('items.children')->first(),
        ];
    }

    public function name(): ?string
    {
        return __('Меню и навигация');
    }

    public function description(): ?string
    {
        return __('Управление верхним и нижним меню сайта');
    }

    public function commandBar(): iterable
    {
        return [
            Link::make(__('Редактировать верхнее меню'))
                ->icon('menu')
                ->route('platform.systems.menu.edit', 'header')
                ->permission(Rbac::PERMISSION_MENU),

            Link::make(__('Редактировать нижнее меню'))
                ->icon('menu')
                ->route('platform.systems.menu.edit', 'footer')
                ->permission(Rbac::PERMISSION_MENU),
        ];
    }

    public function layout(): iterable
    {
        return [
            Layout::view('orchid.menu.list'),
        ];
    }
}
