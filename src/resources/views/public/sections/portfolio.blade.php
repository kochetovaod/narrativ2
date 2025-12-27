@php
    $limit = (int) ($section['settings']['limit'] ?? 6);
    $cases = \App\Models\PortfolioCase::query()
        ->with(['products:id,title,slug', 'services:id,title,slug'])
        ->published()
        ->orderByDesc('date')
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

    @if(!empty($section['settings']['show_filters']))
        <div class="list-inline" style="margin-top: 0.5rem;">
            <span class="tag">Все кейсы</span>
            <span class="tag" style="background: #ecfeff; color: #0ea5e9;">По товарам</span>
            <span class="tag" style="background: #fef9c3; color: #854d0e;">По услугам</span>
        </div>
    @endif

    <div class="grid columns-3" style="margin-top: 1.25rem;">
        @forelse($cases as $case)
            <article class="card">
                <div class="tag">Кейс</div>
                <h3 style="margin-top: 0.5rem;">
                    <a href="{{ route('portfolio.show', $case->slug) }}">{{ $case->title }}</a>
                </h3>
                <p class="meta">
                    {{ optional($case->date)->translatedFormat('d M Y') }}
                    @if($case->client_name)
                        • {{ $case->is_nda ? $case->public_client_label ?? 'NDA' : $case->client_name }}
                    @endif
                </p>
                @if($case->description)
                    <p>{{ Str::limit(strip_tags($case->description), 130) }}</p>
                @endif
                @if($case->products->isNotEmpty() || $case->services->isNotEmpty())
                    <div class="meta" style="margin-top: 0.75rem;">
                        @foreach($case->products as $product)
                            <span class="tag" style="background: #f1f5f9; color: #0f172a;">{{ $product->title }}</span>
                        @endforeach
                        @foreach($case->services as $service)
                            <span class="tag" style="background: #ecfeff; color: #0ea5e9;">{{ $service->title }}</span>
                        @endforeach
                    </div>
                @endif
            </article>
        @empty
            <p>Кейсы пока не добавлены.</p>
        @endforelse
    </div>
</section>
