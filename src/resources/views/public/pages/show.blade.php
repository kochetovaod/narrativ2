@extends('public.layouts.app')

@section('title', $page->seo['title'] ?? $page->title)
@section('meta_description', $page->seo['description'] ?? '')

@section('content')
    <section class="section">
        <h1>{{ $page->seo['h1'] ?? $page->title }}</h1>
    </section>

    @if($page->sections && is_array($page->sections))
        @foreach($page->sections as $section)
            @php
                $type = $section['type'] ?? 'text';
            @endphp
            @includeIf('public.sections.'.$type, ['section' => $section])
        @endforeach
    @else
        <p>Секции страницы пока не заполнены.</p>
    @endif
@endsection
