@php
    $limit = (int) ($section['settings']['limit'] ?? 6);
    $services = \App\Models\Service::query()
        ->published()
        ->orderBy('title')
        ->limit($limit > 0 ? $limit : 6)
        ->get();
@endphp

<section class="section">
    @if(!empty($section['settings']['title']))
        <h2>{{ $section['settings']['title'] }}</h2>
    @endif

    @if(!empty($section['settings']['description']))
        <p class="meta">{{ $section['settings']['description'] }}</p>
    @endif

    <div class="grid columns-3" style="margin-top: 1rem;">
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
                    <p>{{ Str::limit(strip_tags($description), 130) }}</p>
                @endif
                <a class="btn secondary" href="{{ route('services.show', $service->slug) }}">Подробнее</a>
            </article>
        @empty
            <p>Услуги не найдены.</p>
        @endforelse
    </div>
</section>
