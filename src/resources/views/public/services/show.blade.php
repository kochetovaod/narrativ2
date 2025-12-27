@extends('public.layouts.app')

@section('title', $service->seo['title'] ?? $service->title)
@section('meta_description', $service->seo['description'] ?? '')

@section('content')
    <section class="section">
        <div class="tag">Услуга</div>
        <h1 style="margin-top: 0.5rem;">{{ $service->seo['h1'] ?? $service->title }}</h1>

        @php
            $content = $service->content ?? [];
            $description = is_array($content) ? ($content['description'] ?? ($content[0]['value'] ?? null)) : null;
            $stages = is_array($content) ? ($content['stages'] ?? null) : null;
            $benefits = is_array($content) ? ($content['benefits'] ?? null) : null;
        @endphp

        <div class="card" style="margin-top: 1rem;">
            <h3>Описание</h3>
            <p>{{ $description ?: 'Описание услуги скоро появится.' }}</p>

            @if($stages)
                <div style="margin-top: 1rem;">
                    <h4>Этапы работы</h4>
                    <p>{{ $stages }}</p>
                </div>
            @endif

            @if($benefits)
                <div style="margin-top: 1rem;">
                    <h4>Преимущества</h4>
                    <p>{{ $benefits }}</p>
                </div>
            @endif
        </div>
    </section>

    @if($service->show_cases && $service->portfolioCases->isNotEmpty())
        <section class="section">
            <h2>Примеры работ</h2>
            <div class="grid columns-3">
                @foreach($service->portfolioCases as $case)
                    <article class="card">
                        <div class="tag">Кейс</div>
                        <h3 style="margin-top: 0.5rem;">
                            <a href="{{ route('portfolio.show', $case->slug) }}">{{ $case->title }}</a>
                        </h3>
                        @if($case->description)
                            <p>{{ Str::limit(strip_tags($case->description), 130) }}</p>
                        @endif
                        <a class="btn secondary" href="{{ route('portfolio.show', $case->slug) }}">Смотреть кейс</a>
                    </article>
                @endforeach
            </div>
        </section>
    @endif

    @if($relatedServices->isNotEmpty())
        <section class="section">
            <h2>Другие услуги</h2>
            <div class="grid columns-3">
                @foreach($relatedServices as $other)
                    <article class="card">
                        <div class="tag">Услуга</div>
                        <h3 style="margin-top: 0.5rem;">
                            <a href="{{ route('services.show', $other->slug) }}">{{ $other->title }}</a>
                        </h3>
                        @php
                            $otherDescription = is_array($other->content ?? null)
                                ? ($other->content['description'] ?? ($other->content[0]['value'] ?? null))
                                : null;
                        @endphp
                        @if($otherDescription)
                            <p>{{ Str::limit(strip_tags($otherDescription), 120) }}</p>
                        @endif
                    </article>
                @endforeach
            </div>
        </section>
    @endif
@endsection
