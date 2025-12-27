@extends('public.layouts.app')

@section('title', 'Поиск')

@section('content')
    <section class="section">
        <h1>Поиск</h1>
        <p class="meta">Результаты для запроса: "{{ $query }}"</p>
    </section>

    <style>
        .search-empty {
            padding: 1.25rem;
            border-radius: 1rem;
            border: 1px dashed #e2e8f0;
            background: #f8fafc;
            color: #475569;
        }

        .search-empty h3 {
            margin-top: 0;
            margin-bottom: 0.25rem;
        }

        .search-section .section-heading {
            display: flex;
            align-items: baseline;
            justify-content: space-between;
            gap: 1rem;
        }

        .search-section .badge {
            background: #e2e8f0;
            color: #475569;
            border-radius: 999px;
            padding: 0.25rem 0.75rem;
            font-size: 0.9rem;
            white-space: nowrap;
        }

        .search-grid {
            margin-top: 1rem;
        }

        .search-snippet {
            color: #475569;
            margin-bottom: 0;
        }
    </style>

    @if(!$query)
        <div class="search-empty">
            <h3>Начните поиск</h3>
            <p class="meta">Введите запрос в поле поиска в шапке сайта.</p>
        </div>
    @elseif($results->isEmpty() || $results->pluck('items')->flatten(1)->isEmpty())
        <div class="search-empty">
            <h3>Ничего не найдено</h3>
            <p class="meta">Попробуйте уточнить формулировку или проверьте, нет ли опечаток.</p>
        </div>
    @else
        @foreach($results as $group)
            @if($group['items']->isNotEmpty())
                <section class="section search-section">
                    <div class="section-heading">
                        <div>
                            <p class="meta">{{ $group['label'] }}</p>
                            <h2 style="margin: 0;">{{ $group['label'] }}</h2>
                        </div>
                        <span class="badge">{{ $group['items']->count() }} результатов</span>
                    </div>

                    <div class="grid columns-3 search-grid">
                        @foreach($group['items'] as $item)
                            <article class="card">
                                <div class="tag">{{ $item['type_label'] }}</div>
                                <h3 style="margin-top: 0.5rem;">
                                    <a href="{{ $item['url'] }}">{!! $item['highlighted_title'] !!}</a>
                                </h3>

                                @if($item['snippet'])
                                    <p class="search-snippet">{!! $item['snippet'] !!}</p>
                                @endif

                                @if($item['type'] === 'news' && $item['published_at'])
                                    <p class="meta" style="margin-top: 0.5rem;">
                                        {{ \Illuminate\Support\Carbon::parse($item['published_at'])->translatedFormat('d M Y') }}
                                    </p>
                                @endif

                                @if($item['type'] === 'portfolio' && $item['client_name'])
                                    <p class="meta" style="margin-top: 0.5rem;">Клиент: {{ $item['client_name'] }}</p>
                                @endif

                                @if($item['type'] === 'pages' && $item['snippet'] === null && $item['title'])
                                    <p class="meta" style="margin-top: 0.5rem;">Статическая страница</p>
                                @endif
                            </article>
                        @endforeach
                    </div>
                </section>
            @endif
        @endforeach
    @endif
@endsection
