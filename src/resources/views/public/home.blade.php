@extends('public.layouts.app')

@section('title', 'Главная')

@section('content')
    <section class="section hero">
        <h1>Производственные решения под ваш проект</h1>
        <p>Услуги, продукция и реализованные кейсы в одном месте. Настоящее содержимое заполняется из админ-панели.</p>
        <div class="list-inline" style="justify-content: center; gap: 0.75rem;">
            <a class="btn" href="{{ route('products.index') }}">Каталог продукции</a>
            <a class="btn secondary" href="{{ route('services.index') }}">Список услуг</a>
        </div>
    </section>

    @if($categories->isNotEmpty())
        <section class="section">
            <h2>Продукция</h2>
            <div class="grid columns-3">
                @foreach($categories as $category)
                    <article class="card">
                        <div class="tag">Категория</div>
                        <h3 style="margin-top: 0.5rem;">
                            <a href="{{ route('products.category', $category->slug) }}">{{ $category->title }}</a>
                        </h3>
                        @if($category->intro_text)
                            <p class="meta">{{ Str::limit($category->intro_text, 140) }}</p>
                        @endif
                        <a class="btn secondary" href="{{ route('products.category', $category->slug) }}">Смотреть товары</a>
                    </article>
                @endforeach
            </div>
        </section>
    @endif

    @if($services->isNotEmpty())
        <section class="section">
            <h2>Услуги</h2>
            <div class="grid columns-3">
                @foreach($services as $service)
                    <article class="card">
                        <div class="tag">Услуга</div>
                        <h3 style="margin-top: 0.5rem;">
                            <a href="{{ route('services.show', $service->slug) }}">{{ $service->title }}</a>
                        </h3>
                        @if(!empty($service->content['description']))
                            <p class="meta">{{ Str::limit($service->content['description'], 140) }}</p>
                        @endif
                        <a class="btn secondary" href="{{ route('services.show', $service->slug) }}">Подробнее</a>
                    </article>
                @endforeach
            </div>
        </section>
    @endif

    @if($portfolioCases->isNotEmpty())
        <section class="section">
            <div style="display: flex; align-items: baseline; justify-content: space-between; gap: 1rem;">
                <h2 style="margin: 0;">Свежие кейсы</h2>
                <a class="meta" href="{{ route('portfolio.index') }}">Все кейсы</a>
            </div>
            <div class="grid columns-3">
                @foreach($portfolioCases as $case)
                    <article class="card">
                        <div class="tag">Кейс</div>
                        <h3 style="margin-top: 0.5rem;">
                            <a href="{{ route('portfolio.show', $case->slug) }}">{{ $case->title }}</a>
                        </h3>
                        <p class="meta">
                            @if($case->date)
                                {{ $case->date->translatedFormat('d M Y') }}
                            @endif
                            @if($case->client_name)
                                • Клиент: {{ $case->is_nda ? $case->public_client_label ?? 'NDA' : $case->client_name }}
                            @endif
                        </p>
                        @if($case->description)
                            <p>{{ Str::limit(strip_tags($case->description), 150) }}</p>
                        @endif
                        <a class="btn secondary" href="{{ route('portfolio.show', $case->slug) }}">Открыть кейс</a>
                    </article>
                @endforeach
            </div>
        </section>
    @endif

    @if($news->isNotEmpty())
        <section class="section">
            <div style="display: flex; align-items: baseline; justify-content: space-between; gap: 1rem;">
                <h2 style="margin: 0;">Новости</h2>
                <a class="meta" href="{{ route('news.index') }}">Архив</a>
            </div>
            <div class="grid columns-3">
                @foreach($news as $post)
                    <article class="card">
                        <div class="tag">Новость</div>
                        <h3 style="margin-top: 0.5rem;">
                            <a href="{{ route('news.show', $post->slug) }}">{{ $post->title }}</a>
                        </h3>
                        <p class="meta">
                            {{ optional($post->published_at)->translatedFormat('d M Y') }}
                        </p>
                        @if($post->excerpt)
                            <p>{{ Str::limit(strip_tags($post->excerpt), 140) }}</p>
                        @endif
                        <a class="btn secondary" href="{{ route('news.show', $post->slug) }}">Читать</a>
                    </article>
                @endforeach
            </div>
        </section>
    @endif
@endsection
