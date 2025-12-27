@extends('public.layouts.app')

@section('title', 'Услуги')

@section('content')
    <section class="section">
        <h1>Услуги компании</h1>
        <p class="meta">Публикуются из админ-панели Orchid.</p>

        <div class="grid columns-3" style="margin-top: 1.25rem;">
            @forelse($services as $service)
                <article class="card">
                    <div class="tag">Услуга</div>
                    <h3 style="margin-top: 0.5rem;">
                        <a href="{{ route('services.show', $service->slug) }}">{{ $service->title }}</a>
                    </h3>
                    @php
                        $description = is_array($service->content ?? null)
                            ? ($service->content['description'] ?? ($service->content[0]['value'] ?? null))
                            : null;
                    @endphp
                    @if($description)
                        <p>{{ Str::limit(strip_tags($description), 150) }}</p>
                    @endif
                    <a class="btn secondary" href="{{ route('services.show', $service->slug) }}">Подробнее</a>
                </article>
            @empty
                <p>Опубликованных услуг пока нет.</p>
            @endforelse
        </div>
    </section>
@endsection
