<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\NewsPost;
use App\Models\Page;
use App\Models\PortfolioCase;
use App\Models\Product;
use App\Models\Service;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Throwable;

class SearchService
{
    private const HIGHLIGHT_PRE_TAG = '<mark>';

    private const HIGHLIGHT_POST_TAG = '</mark>';

    private const SEARCH_LIMITS = [
        'products' => 8,
        'services' => 8,
        'portfolio' => 6,
        'news' => 6,
        'pages' => 4,
    ];

    private const SUGGESTION_LIMITS = [
        'products' => 3,
        'services' => 3,
        'news' => 3,
        'portfolio' => 2,
        'pages' => 2,
    ];

    private const TYPE_LABELS = [
        'products' => 'Продукция',
        'services' => 'Услуги',
        'portfolio' => 'Кейсы',
        'news' => 'Новости',
        'pages' => 'Страницы',
    ];

    public function search(string $query): Collection
    {
        return collect([
            'products' => $this->searchProducts($query, self::SEARCH_LIMITS['products']),
            'services' => $this->searchServices($query, self::SEARCH_LIMITS['services']),
            'portfolio' => $this->searchPortfolio($query, self::SEARCH_LIMITS['portfolio']),
            'news' => $this->searchNews($query, self::SEARCH_LIMITS['news']),
            'pages' => $this->searchPages($query, self::SEARCH_LIMITS['pages']),
        ])->map(function (Collection $items, string $type): array {
            return [
                'label' => self::TYPE_LABELS[$type] ?? ucfirst($type),
                'items' => $items,
            ];
        });
    }

    public function suggestions(string $query): Collection
    {
        $suggestions = collect();

        $suggestions->push(
            ...$this->searchProducts($query, self::SUGGESTION_LIMITS['products'], 80)
        );
        $suggestions->push(
            ...$this->searchServices($query, self::SUGGESTION_LIMITS['services'], 80)
        );
        $suggestions->push(
            ...$this->searchNews($query, self::SUGGESTION_LIMITS['news'], 80)
        );
        $suggestions->push(
            ...$this->searchPortfolio($query, self::SUGGESTION_LIMITS['portfolio'], 80)
        );
        $suggestions->push(
            ...$this->searchPages($query, self::SUGGESTION_LIMITS['pages'], 80)
        );

        return $suggestions->values();
    }

    private function searchProducts(string $query, int $limit, int $cropLength = 140): Collection
    {
        return $this->performSearch(Product::class, $query, $limit, $cropLength)
            ->map(function (array $hit) {
                $categorySlug = Arr::get($hit, 'category_slug');
                $slug = Arr::get($hit, 'slug');

                if (blank($categorySlug) || blank($slug)) {
                    return null;
                }

                return $this->formatHit($hit, [
                    'type' => 'products',
                    'url' => route('products.show', [
                        'categorySlug' => $categorySlug,
                        'productSlug' => $slug,
                    ]),
                    'snippetFields' => ['short_text', 'description'],
                ]);
            })
            ->filter()
            ->values();
    }

    private function searchServices(string $query, int $limit, int $cropLength = 140): Collection
    {
        return $this->performSearch(Service::class, $query, $limit, $cropLength)
            ->map(function (array $hit) {
                $slug = Arr::get($hit, 'slug');

                if (blank($slug)) {
                    return null;
                }

                return $this->formatHit($hit, [
                    'type' => 'services',
                    'url' => route('services.show', ['serviceSlug' => $slug]),
                    'snippetFields' => ['content_text'],
                ]);
            })
            ->filter()
            ->values();
    }

    private function searchPortfolio(string $query, int $limit, int $cropLength = 140): Collection
    {
        return $this->performSearch(PortfolioCase::class, $query, $limit, $cropLength)
            ->map(function (array $hit) {
                $slug = Arr::get($hit, 'slug');

                if (blank($slug)) {
                    return null;
                }

                return $this->formatHit($hit, [
                    'type' => 'portfolio',
                    'url' => route('portfolio.show', ['caseSlug' => $slug]),
                    'snippetFields' => ['description'],
                ]);
            })
            ->filter()
            ->values();
    }

    private function searchNews(string $query, int $limit, int $cropLength = 140): Collection
    {
        return $this->performSearch(NewsPost::class, $query, $limit, $cropLength)
            ->map(function (array $hit) {
                $slug = Arr::get($hit, 'slug');

                if (blank($slug)) {
                    return null;
                }

                return $this->formatHit($hit, [
                    'type' => 'news',
                    'url' => route('news.show', ['newsSlug' => $slug]),
                    'snippetFields' => ['excerpt', 'content'],
                ]);
            })
            ->filter()
            ->values();
    }

    private function searchPages(string $query, int $limit, int $cropLength = 140): Collection
    {
        return $this->performSearch(Page::class, $query, $limit, $cropLength)
            ->map(function (array $hit) {
                $slug = Arr::get($hit, 'slug');

                if (blank($slug)) {
                    return null;
                }

                return $this->formatHit($hit, [
                    'type' => 'pages',
                    'url' => route('pages.show', ['pageSlug' => $slug]),
                    'snippetFields' => ['sections_text', 'code'],
                ]);
            })
            ->filter()
            ->values();
    }

    private function performSearch(string $modelClass, string $query, int $limit, int $cropLength): Collection
    {
        try {
            $raw = $modelClass::search(
                $query,
                function ($meilisearch, string $query, array $options) use ($limit, $cropLength) {
                    $options = array_merge($options, [
                        'limit' => $limit,
                        'attributesToHighlight' => ['title', 'short_text', 'description', 'excerpt', 'content', 'content_text', 'sections_text', 'code', 'client_name'],
                        'attributesToCrop' => ['short_text', 'description', 'excerpt', 'content', 'content_text', 'sections_text'],
                        'cropLength' => $cropLength,
                        'highlightPreTag' => self::HIGHLIGHT_PRE_TAG,
                        'highlightPostTag' => self::HIGHLIGHT_POST_TAG,
                    ]);

                    return $meilisearch->search($query, $options);
                }
            )->raw();
        } catch (Throwable $exception) {
            report($exception);

            return collect();
        }

        return collect($raw['hits'] ?? []);
    }

    private function formatHit(array $hit, array $context): array
    {
        $type = $context['type'];

        return [
            'type' => $type,
            'type_label' => self::TYPE_LABELS[$type] ?? ucfirst($type),
            'title' => Arr::get($hit, 'title', ''),
            'highlighted_title' => Arr::get($hit, '_formatted.title')
                ?? Arr::get($hit, 'title', ''),
            'url' => $context['url'],
            'snippet' => $this->extractSnippet($hit, $context['snippetFields'] ?? []),
            'published_at' => Arr::get($hit, 'published_at'),
            'client_name' => Arr::get($hit, 'client_name'),
        ];
    }

    private function extractSnippet(array $hit, array $fields): ?string
    {
        foreach ($fields as $field) {
            $formatted = Arr::get($hit, "_formatted.$field");
            $raw = Arr::get($hit, $field);

            if ($formatted) {
                return $formatted;
            }

            if ($raw) {
                return e(Str::limit((string) $raw, 160));
            }
        }

        return null;
    }
}
