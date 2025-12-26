<?php

namespace App\Orchid\Screens;

use Orchid\Screen\Screen;
use Orchid\Support\Facades\Layout;

class DashboardScreen extends Screen
{
    /**
     * Query data.
     */
    public function query(): iterable
    {
        return [];
    }

    /**
     * Display header name.
     */
    public function name(): ?string
    {
        return __('Панель управления');
    }

    /**
     * Display header description.
     */
    public function description(): ?string
    {
        return __('Стартовая страница административной панели');
    }

    /**
     * Button commands.
     */
    public function commandBar(): iterable
    {
        return [];
    }

    /**
     * Views.
     */
    public function layout(): iterable
    {
        return [
            Layout::view('platform.dashboard'),
        ];
    }
}
