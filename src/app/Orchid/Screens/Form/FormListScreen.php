<?php

declare(strict_types=1);

namespace App\Orchid\Screens\Form;

use App\Models\Form;
use App\Orchid\Permissions\Rbac;
use Orchid\Screen\Actions\Link;
use Orchid\Screen\Screen;
use Orchid\Screen\TD;
use Orchid\Support\Facades\Layout;

class FormListScreen extends Screen
{
    /**
     * @var string|array<int, string>
     */
    public $permission = 'platform.forms';

    /**
     * Fetch the forms data.
     */
    public function query(): iterable
    {
        return [
            'forms' => Form::with('fields')
                ->filters()
                ->defaultSort('created_at', 'desc')
                ->paginate(),
        ];
    }

    public function name(): ?string
    {
        return __('Формы и заявки');
    }

    public function description(): ?string
    {
        return __('Управление формами и просмотр заявок');
    }

    public function commandBar(): iterable
    {
        return [
            Link::make(__('Добавить форму'))
                ->icon('plus')
                ->route('platform.forms.create')
                ->permission(Rbac::PERMISSION_FORMS),

            Link::make(__('Заявки'))
                ->icon('envelope')
                ->route('platform.leads.index')
                ->permission(Rbac::PERMISSION_FORMS),
        ];
    }

    public function layout(): iterable
    {
        return [
            Layout::table('forms', [
                TD::make('title', __('Название'))
                    ->cantHide()
                    ->render(fn (Form $form) => $form->title),

                TD::make('code', __('Код'))
                    ->render(fn (Form $form) => $form->code)
                    ->width('150px'),

                TD::make('is_active', __('Статус'))
                    ->render(fn (Form $form) => $form->is_active
                        ? '<span class="badge badge-success">'.__('Активна').'</span>'
                        : '<span class="badge badge-secondary">'.__('Неактивна').'</span>')
                    ->width('120px'),

                TD::make('fields_count', __('Полей'))
                    ->render(fn (Form $form) => $form->fields->count())
                    ->width('80px'),

                TD::make('notification_email', __('Email уведомления'))
                    ->render(fn (Form $form) => ! empty($form->notification_email)
                        ? '<i class="icon-check text-success"></i>'
                        : '<i class="icon-close text-muted"></i>')
                    ->width('100px'),

                TD::make('notification_telegram', __('Telegram'))
                    ->render(fn (Form $form) => ! empty($form->notification_telegram)
                        ? '<i class="icon-check text-success"></i>'
                        : '<i class="icon-close text-muted"></i>')
                    ->width('100px'),

                TD::make('actions', __('Действия'))
                    ->render(fn (Form $form) => Link::make(__('Редактировать'))
                        ->icon('pencil')
                        ->route('platform.forms.edit', $form->id)
                        ->permission(Rbac::PERMISSION_FORMS))
                    ->width('150px'),
            ]),
        ];
    }
}
