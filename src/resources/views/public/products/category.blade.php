@extends('public.layouts.app')

@section('title', $category->title)

@section('content')
    <section class="section">
        <h1>{{ $category->title }}</h1>
        @if($category->intro_text)
            <p class="meta">{{ $category->intro_text }}</p>
        @endif

        @if(!empty($category->body))
            <div class="section text" style="margin-top: 1rem;">
                <div class="content">
                    @if(is_array($category->body))
                        @foreach($category->body as $block)
                            <p>{{ is_array($block) ? json_encode($block, JSON_UNESCAPED_UNICODE) : $block }}</p>
                        @endforeach
                    @else
                        <p>{{ $category->body }}</p>
                    @endif
                </div>
            </div>
        @endif
    </section>

    <section class="section">
        <div style="display: flex; align-items: baseline; justify-content: space-between; gap: 1rem;">
            <h2 style="margin: 0;">Товары</h2>
            <a class="meta" href="{{ route('products.index') }}">Все категории</a>
        </div>

        <div class="grid columns-3" style="margin-top: 1.25rem;">
            @forelse($products as $product)
                <article class="card">
                    <div class="tag">Товар</div>
                    <h3 style="margin-top: 0.5rem;">
                        <a href="{{ route('products.show', [$category->slug, $product->slug]) }}">{{ $product->title }}</a>
                    </h3>
                    @if($product->short_text)
                        <p>{{ Str::limit(strip_tags($product->short_text), 140) }}</p>
                    @endif
                    <a class="btn secondary" href="{{ route('products.show', [$category->slug, $product->slug]) }}">Подробнее</a>
                </article>
            @empty
                <p>В этой категории пока нет опубликованных товаров.</p>
            @endforelse
        </div>

        @include('public.partials.pagination', ['paginator' => $products])
    </section>
@endsection
