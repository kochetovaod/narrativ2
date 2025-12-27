@php
    $limit = (int) ($section['settings']['limit'] ?? 6);
    $categories = \App\Models\ProductCategory::query()
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
        @forelse($categories as $category)
            <article class="card">
                <div class="tag">Категория</div>
                <h3 style="margin-top: 0.5rem;">
                    <a href="{{ route('products.category', $category->slug) }}">{{ $category->title }}</a>
                </h3>
                @if($category->intro_text)
                    <p>{{ Str::limit(strip_tags($category->intro_text), 130) }}</p>
                @endif
            </article>
        @empty
            <p>Категории не найдены.</p>
        @endforelse
    </div>
</section>
