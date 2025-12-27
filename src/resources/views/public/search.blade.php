@extends('public.layouts.app')

@section('title', 'Поиск')

@section('content')
    <section class="section">
        <h1>Поиск</h1>
        <p class="meta">Результаты для запроса: "{{ $query }}"</p>
    </section>

    @if(!$query)
        <p>Введите запрос в поле поиска, чтобы увидеть результаты.</p>
    @elseif($results->isEmpty() || $results->flatten()->isEmpty())
        <p>Ничего не найдено. Попробуйте другой запрос.</p>
    @else
        @foreach($results as $type => $items)
            @if($items->isNotEmpty())
                <section class="section">
                    <div style="display: flex; align-items: baseline; justify-content: space-between; gap: 1rem;">
                        <h2 style="margin: 0;">{{ ucfirst($type) }}</h2>
                        <span class="meta">{{ $items->count() }} результатов</span>
                    </div>

                    <div class="grid columns-3" style="margin-top: 1rem;">
                        @foreach($items as $item)
                            <article class="card">
                                <div class="tag">{{ ucfirst($type) }}</div>
                                <h3 style="margin-top: 0.5rem;">
                                    @switch($type)
                                        @case('products')
                                            @if($item->category)
                                                <a href="{{ route('products.show', [$item->category->slug, $item->slug]) }}">{{ $item->title }}</a>
                                            @else
                                                {{ $item->title }}
                                            @endif
                                            @break
                                        @case('services')
                                            <a href="{{ route('services.show', $item->slug) }}">{{ $item->title }}</a>
                                            @break
                                        @case('portfolio')
                                            <a href="{{ route('portfolio.show', $item->slug) }}">{{ $item->title }}</a>
                                            @break
                                        @case('news')
                                            <a href="{{ route('news.show', $item->slug) }}">{{ $item->title }}</a>
                                            @break
                                        @case('pages')
                                            <a href="{{ route('pages.show', $item->slug) }}">{{ $item->title }}</a>
                                            @break
                                        @default
                                            {{ $item->title ?? 'Результат' }}
                                    @endswitch
                                </h3>

                                @switch($type)
                                    @case('products')
                                        <p>{{ Str::limit(strip_tags($item->description), 120) }}</p>
                                        @break
                                    @case('services')
                                        @php
                                            $desc = is_array($item->content ?? null)
                                                ? ($item->content['description'] ?? ($item->content[0]['value'] ?? null))
                                                : null;
                                        @endphp
                                        @if($desc)
                                            <p>{{ Str::limit(strip_tags($desc), 120) }}</p>
                                        @endif
                                        @break
                                    @case('news')
                                        <p class="meta">{{ optional($item->published_at)->translatedFormat('d M Y') }}</p>
                                        @if($item->excerpt)
                                            <p>{{ Str::limit(strip_tags($item->excerpt), 120) }}</p>
                                        @endif
                                        @break
                                    @case('portfolio')
                                        @if($item->description)
                                            <p>{{ Str::limit(strip_tags($item->description), 120) }}</p>
                                        @endif
                                        @break
                                    @case('pages')
                                        <p>{{ $item->code ?: 'Статическая страница' }}</p>
                                        @break
                                @endswitch
                            </article>
                        @endforeach
                    </div>
                </section>
            @endif
        @endforeach
    @endif
@endsection
