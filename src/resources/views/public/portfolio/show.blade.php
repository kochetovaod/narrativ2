@extends('public.layouts.app')

@section('title', $case->seo['title'] ?? $case->title)
@section('meta_description', $case->seo['description'] ?? '')

@section('content')
    <section class="section">
        <div class="tag">Кейс</div>
        <h1 style="margin-top: 0.5rem;">{{ $case->seo['h1'] ?? $case->title }}</h1>
        <p class="meta">
            @if($case->date)
                {{ $case->date->translatedFormat('d M Y') }}
            @endif
            @if($case->client_name)
                • Клиент: {{ $case->is_nda ? $case->public_client_label ?? 'NDA' : $case->client_name }}
            @endif
        </p>

        <div class="card" style="margin-top: 1rem;">
            @if($case->description)
                <p>{!! nl2br(e($case->description)) !!}</p>
            @else
                <p>Описание кейса будет добавлено позже.</p>
            @endif

            @if($case->products->isNotEmpty() || $case->services->isNotEmpty())
                <div style="margin-top: 1rem;">
                    <h4>Связанные материалы</h4>
                    <div class="list-inline">
                        @foreach($case->products as $product)
                            <a class="tag" href="{{ route('products.show', [$product->category->slug ?? $product->category_id, $product->slug]) }}">{{ $product->title }}</a>
                        @endforeach
                        @foreach($case->services as $service)
                            <a class="tag" href="{{ route('services.show', $service->slug) }}">{{ $service->title }}</a>
                        @endforeach
                    </div>
                </div>
            @endif
        </div>
    </section>

    @if($relatedCases->isNotEmpty())
        <section class="section">
            <h2>Другие кейсы</h2>
            <div class="grid columns-3">
                @foreach($relatedCases as $related)
                    <article class="card">
                        <div class="tag">Кейс</div>
                        <h3 style="margin-top: 0.5rem;">
                            <a href="{{ route('portfolio.show', $related->slug) }}">{{ $related->title }}</a>
                        </h3>
                        <p class="meta">
                            {{ optional($related->date)->translatedFormat('d M Y') }}
                        </p>
                        @if($related->description)
                            <p>{{ Str::limit(strip_tags($related->description), 120) }}</p>
                        @endif
                    </article>
                @endforeach
            </div>
        </section>
    @endif
@endsection
