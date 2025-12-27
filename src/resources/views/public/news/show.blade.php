@extends('public.layouts.app')

@section('title', $news->seo['title'] ?? $news->title)
@section('meta_description', $news->seo['description'] ?? '')

@section('content')
    <section class="section">
        <div class="tag">Новость</div>
        <h1 style="margin-top: 0.5rem;">{{ $news->seo['h1'] ?? $news->title }}</h1>
        <p class="meta">{{ optional($news->published_at)->translatedFormat('d M Y') }}</p>

        <div class="card" style="margin-top: 1rem;">
            @if($news->excerpt)
                <p class="meta">{{ $news->excerpt }}</p>
            @endif

            @if($news->content)
                <div class="content" style="margin-top: 1rem;">
                    {!! nl2br(e($news->content)) !!}
                </div>
            @else
                <p>Текст новости будет добавлен позднее.</p>
            @endif
        </div>
    </section>

    @if($relatedNews->isNotEmpty())
        <section class="section">
            <h2>Читайте также</h2>
            <div class="grid columns-3">
                @foreach($relatedNews as $post)
                    <article class="card">
                        <div class="tag">Новость</div>
                        <h3 style="margin-top: 0.5rem;">
                            <a href="{{ route('news.show', $post->slug) }}">{{ $post->title }}</a>
                        </h3>
                        <p class="meta">{{ optional($post->published_at)->translatedFormat('d M Y') }}</p>
                        @if($post->excerpt)
                            <p>{{ Str::limit(strip_tags($post->excerpt), 130) }}</p>
                        @endif
                    </article>
                @endforeach
            </div>
        </section>
    @endif
@endsection
