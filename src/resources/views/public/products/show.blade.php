@extends('public.layouts.app')

@section('title', $product->seo['title'] ?? $product->title)
@section('meta_description', $product->seo['description'] ?? '')

@section('content')
    <section class="section">
        <div class="tag">Товар</div>
        <h1 style="margin-top: 0.5rem;">{{ $product->seo['h1'] ?? $product->title }}</h1>
        @if($product->short_text)
            <p class="meta">{{ $product->short_text }}</p>
        @endif

        <div class="card" style="margin-top: 1rem;">
            <h3>Описание</h3>
            <p>{{ $product->description ?: 'Описание будет добавлено позже.' }}</p>

            @if(!empty($product->specs) && is_array($product->specs))
                <div style="margin-top: 1rem;">
                    <h4>Характеристики</h4>
                    <dl style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 0.75rem;">
                        @foreach($product->specs as $key => $value)
                            <div>
                                <dt class="meta">{{ is_string($key) ? $key : 'Параметр' }}</dt>
                                <dd style="margin: 0;">{{ is_array($value) ? json_encode($value, JSON_UNESCAPED_UNICODE) : $value }}</dd>
                            </div>
                        @endforeach
                    </dl>
                </div>
            @endif
        </div>
    </section>

    @if(!empty($formPlacements) && $formPlacements->isNotEmpty())
        @foreach($formPlacements as $placement)
            @include('public.partials.form-placement', [
                'placement' => $placement,
                'pageTitle' => $pageTitle ?? $product->title,
            ])
        @endforeach
    @endif

    @if($relatedProducts->isNotEmpty())
        <section class="section">
            <h2>Похожие товары</h2>
            <div class="grid columns-3">
                @foreach($relatedProducts as $related)
                    <article class="card">
                        <div class="tag">Товар</div>
                        <h3 style="margin-top: 0.5rem;">
                            <a href="{{ route('products.show', [$category->slug, $related->slug]) }}">{{ $related->title }}</a>
                        </h3>
                        @if($related->short_text)
                            <p>{{ Str::limit(strip_tags($related->short_text), 120) }}</p>
                        @endif
                    </article>
                @endforeach
            </div>
        </section>
    @endif
@endsection
