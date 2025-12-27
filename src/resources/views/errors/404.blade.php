@extends('public.layouts.app')

@section('title', 'Страница не найдена')

@section('content')
    <section class="section">
        <h1>404 — страница не найдена</h1>
        <p class="meta">Кажется, запрошенный адрес недоступен или был перемещен.</p>
        <div class="list-inline" style="gap: 0.75rem;">
            <a class="btn" href="{{ route('home') }}">На главную</a>
            <a class="btn secondary" href="{{ route('search') }}">Найти через поиск</a>
        </div>
    </section>
@endsection
