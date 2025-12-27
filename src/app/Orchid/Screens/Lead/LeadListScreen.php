<?php

declare(strict_types=1);

namespace App\Orchid\Screens\Lead;

use App\Models\Lead;
use App\Orchid\Filters\SearchFilter;
use App\Orchid\Filters\StatusFilter;
use App\Orchid\Permissions\Rbac;
use Illuminate\Http\Request;
use Orchid\Screen\Actions\Link;
use Orchid\Screen\Actions\ModalToggle;
use Orchid\Screen\Screen;
use Orchid\Screen\TD;
use Orchid\Support\Facades\Layout;

class LeadListScreen extends Screen
{
    /**
     * @var string|array<int, string>
     */
    public $permission = 'platform.leads';

    public function query(): iterable
    {
        return [
            'leads' => Lead::with('dedupIndex')
                ->filters([SearchFilter::class, StatusFilter::class])
                ->defaultSort('created_at', 'desc')
                ->paginate(),
        ];
    }

    public function name(): ?string
    {
        return __('Заявки');
    }

    public function description(): ?string
    {
        return __('Просмотр и управление заявками с сайта');
    }

    public function commandBar(): iterable
    {
        return [
            Link::make(__('Формы'))
                ->icon('envelope')
                ->route('platform.forms.index')
                ->permission(Rbac::PERMISSION_FORMS),

            Link::make(__('Экспорт CSV'))
                ->icon('download')
                ->route('platform.leads.export')
                ->permission(Rbac::PERMISSION_FORMS),
        ];
    }

    public function layout(): iterable
    {
        return [
            Layout::table('leads', [
                TD::make('id', __('ID'))
                    ->width('80px')
                    ->render(fn (Lead $lead) => '#'.$lead->id),

                TD::make('form_code', __('Форма'))
                    ->render(fn (Lead $lead) => $this->getFormTitle($lead->form_code))
                    ->width('150px'),

                TD::make('name', __('Имя'))
                    ->render(fn (Lead $lead) => $lead->payload['name'] ?? '—')
                    ->width('150px'),

                TD::make('phone', __('Телефон'))
                    ->render(fn (Lead $lead) => $lead->phone ?? '—')
                    ->width('130px'),

                TD::make('email', __('Email'))
                    ->render(fn (Lead $lead) => $lead->email ?? '—')
                    ->width('180px'),

                TD::make('status', __('Статус'))
                    ->render(fn (Lead $lead) => $this->getStatusBadge($lead->status))
                    ->width('120px'),

                TD::make('source_url', __('Источник'))
                    ->render(fn (Lead $lead) => $this->truncateUrl($lead->source_url))
                    ->width('200px'),

                TD::make('created_at', __('Дата'))
                    ->render(fn (Lead $lead) => $lead->created_at?->toDateTimeString())
                    ->width('150px')
                    ->sort(),

                TD::make('actions', __('Действия'))
                    ->render(fn (Lead $lead) => ModalToggle::make(__('Просмотр'))
                        ->modal('leadDetailModal')
                        ->modalTitle(__('Детали заявки #').$lead->id)
                        ->async('asyncGetLeadDetail', $lead->id)
                        ->icon('eye'))
                    ->width('100px'),
            ]),
        ];
    }

    public function asyncGetLeadDetail(Lead $lead): array
    {
        return [
            'lead' => $lead->load('dedupIndex'),
            'payload' => json_encode($lead->payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE),
            'utm' => json_encode($lead->utm, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE),
            'form_title' => $this->getFormTitle($lead->form_code),
            'status_text' => $this->getStatusText($lead->status),
        ];
    }

    public function updateStatus(Request $request, int $leadId): void
    {
        $request->validate([
            'status' => 'required|in:new,in_progress,closed',
        ]);

        $lead = Lead::findOrFail($leadId);
        $lead->update(['status' => $request->input('status')]);

        // TODO: Уведомить менеджера об изменении статуса
    }

    public function addComment(Request $request, int $leadId): void
    {
        $request->validate([
            'comment' => 'required|string|max:1000',
        ]);

        $lead = Lead::findOrFail($leadId);
        $currentComment = $lead->manager_comment ?? '';
        $newComment = $currentComment ? $currentComment."\n\n" : '';
        $newComment .= date('d.m.Y H:i').': '.$request->input('comment');

        $lead->update(['manager_comment' => $newComment]);
    }

    private function getFormTitle(string $formCode): string
    {
        return match ($formCode) {
            'callback' => 'Обратный звонок',
            'calc' => 'Калькулятор',
            'question' => 'Вопрос специалисту',
            default => 'Неизвестная форма',
        };
    }

    private function getStatusBadge(string $status): string
    {
        return match ($status) {
            'new' => '<span class="badge badge-primary">'.__('Новая').'</span>',
            'in_progress' => '<span class="badge badge-warning">'.__('В работе').'</span>',
            'closed' => '<span class="badge badge-success">'.__('Закрыта').'</span>',
            default => '<span class="badge badge-secondary">'.__('Неизвестно').'</span>',
        };
    }

    private function getStatusText(string $status): string
    {
        return match ($status) {
            'new' => 'Новая',
            'in_progress' => 'В работе',
            'closed' => 'Закрыта',
            default => 'Неизвестно',
        };
    }

    private function truncateUrl(?string $url): string
    {
        if (! $url) {
            return '—';
        }

        $truncated = strlen($url) > 30 ? substr($url, 0, 30).'...' : $url;

        return '<a href="'.$url.'" target="_blank">'.$truncated.'</a>';
    }
}
