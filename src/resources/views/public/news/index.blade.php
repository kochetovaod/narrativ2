@extends('public.layouts.app')

@section('title', 'Новости')

@section('content')
    <section class="section">
        <h1>Новости</h1>
        <p class="meta">Последние публикации компании.</p>

        <div class="grid columns-3" style="margin-top: 1.25rem;">
            @forelse($news as $post)
                <article class="card">
                    <div class="tag">Новость</div>
                    <h3 style="margin-top: 0.5rem;">
                        <a href="{{ route('news.show', $post->slug) }}">{{ $post->title }}</a>
                    </h3>
                    <p class="meta">{{ optional($post->published_at)->translatedFormat('d M Y') }}</p>
                    @if($post->excerpt)
                        <p>{{ Str::limit(strip_tags($post->excerpt), 150) }}</p>
                    @endif
                    <a class="btn secondary" href="{{ route('news.show', $post->slug) }}">Читать</a>
                </article>
            @empty
                <p>Новостей пока нет.</p>
            @endforelse
        </div>

        @include('public.partials.pagination', ['paginator' => $news])
    </section>
@endsection
