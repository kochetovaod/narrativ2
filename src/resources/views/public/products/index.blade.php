@extends('public.layouts.app')

@section('title', 'Категории продукции')

@section('content')
    <section class="section">
        <h1>Категории продукции</h1>
        <p class="meta">Все активные категории, опубликованные в админке.</p>

        <div class="grid columns-3" style="margin-top: 1.25rem;">
            @forelse($categories as $category)
                <article class="card">
                    <div class="tag">Категория</div>
                    <h3 style="margin-top: 0.5rem;">
                        <a href="{{ route('products.category', $category->slug) }}">{{ $category->title }}</a>
                    </h3>
                    @if($category->intro_text)
                        <p>{{ Str::limit(strip_tags($category->intro_text), 160) }}</p>
                    @endif
                    <a class="btn secondary" href="{{ route('products.category', $category->slug) }}">Перейти</a>
                </article>
            @empty
                <p>Категории пока не добавлены.</p>
            @endforelse
        </div>
    </section>
@endsection
