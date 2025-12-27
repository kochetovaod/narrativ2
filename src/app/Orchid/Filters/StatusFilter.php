<?php

declare(strict_types=1);

namespace App\Orchid\Filters;

use Illuminate\Database\Eloquent\Builder;
use Orchid\Filters\Filter;
use Orchid\Screen\Field;
use Orchid\Screen\Fields\Select;

class StatusFilter extends Filter
{
    /**
     * @var string|array<int, string>
     */
    public $name = 'Статус';

    /**
     * @var array<int, string>
     */
    public $parameters = ['status'];

    /**
     * @return array<Field>
     */
    public function display(): array
    {
        return [
            Select::make('status')
                ->title('Статус')
                ->options([
                    '' => 'Все',
                    'draft' => 'Черновик',
                    'published' => 'Опубликовано',
                ])
                ->value($this->request->get('status'))
                ->empty()
                ->placeholder('Выберите статус')
                ->help('Фильтр по статусу публикации'),
        ];
    }

    /**
     * @param Builder $query
     * @return Builder
     */
    public function run(Builder $query): Builder
    {
        $status = $this->request->get('status');

        if ($status !== null && $status !== '') {
            return $query->where('status', $status);
        }

        return $query;
    }
}
