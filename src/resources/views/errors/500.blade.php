@extends('public.layouts.app')

@section('title', 'Ошибка сервера')

@section('content')
    <section class="section">
        <h1>Что-то пошло не так</h1>
        <p class="meta">Мы уже работаем над устранением проблемы. Попробуйте обновить страницу чуть позже.</p>
        <div class="list-inline" style="gap: 0.75rem;">
            <a class="btn" href="{{ route('home') }}">На главную</a>
            <a class="btn secondary" href="mailto:hello@example.com">Сообщить об ошибке</a>
        </div>
    </section>
@endsection
