<?php

declare(strict_types=1);

namespace App\Orchid\Filters;

use Illuminate\Database\Eloquent\Builder;
use Orchid\Filters\Filter;
use Orchid\Screen\Field;
use Orchid\Screen\Fields\Input;

class SearchFilter extends Filter
{
    /**
     * @var string|array<int, string>
     */
    public $name = 'Поиск';

    /**
     * @var array<int, string>
     */
    public $parameters = ['search'];

    /**
     * @return array<Field>
     */
    public function display(): array
    {
        return [
            Input::make('search')
                ->title('Поиск')
                ->placeholder('Поиск по названию, описанию...')
                ->value($this->request->get('search'))
                ->help('Поиск по названию и описанию'),
        ];
    }

    public function run(Builder $query): Builder
    {
        $search = $this->request->get('search');

        if (! empty($search)) {
            // Поля для поиска в зависимости от модели
            $table = $query->getModel()->getTable();

            $searchFields = match ($table) {
                'product_categories' => ['title', 'intro_text'],
                'products' => ['title', 'short_text', 'description'],
                'services' => ['title', 'content'],
                'portfolio_cases' => ['title', 'description', 'client_name'],
                'news_posts' => ['title', 'excerpt', 'content'],
                'pages' => ['title', 'content'],
                'global_blocks' => ['title', 'code'],
                default => ['title'],
            };

            return $query->where(function ($q) use ($search, $searchFields) {
                foreach ($searchFields as $field) {
                    if ($field === 'content') {
                        // Для JSON полей content ищем по title
                        $q->orWhereJsonContains('content->title', $search);
                    } else {
                        $q->orWhere($field, 'like', "%{$search}%");
                    }
                }
            });
        }

        return $query;
    }
}
