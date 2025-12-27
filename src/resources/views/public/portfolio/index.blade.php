@extends('public.layouts.app')

@section('title', 'Портфолио')

@section('content')
    <section class="section">
        <h1>Портфолио</h1>
        <p class="meta">Фильтруйте кейсы по связанным товарам и услугам.</p>

        <form method="get" class="card" style="display: grid; gap: 1rem; margin-top: 1rem;">
            <div style="display: grid; gap: 0.75rem; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));">
                <label>
                    <div class="meta">Товар</div>
                    <select name="product" style="width: 100%; padding: 0.6rem; border-radius: 0.5rem; border: 1px solid #cbd5e1;">
                        <option value="">Все товары</option>
                        @foreach($products as $product)
                            <option value="{{ $product->slug }}" @selected($selectedProduct === $product->slug)>{{ $product->title }}</option>
                        @endforeach
                    </select>
                </label>

                <label>
                    <div class="meta">Услуга</div>
                    <select name="service" style="width: 100%; padding: 0.6rem; border-radius: 0.5rem; border: 1px solid #cbd5e1;">
                        <option value="">Все услуги</option>
                        @foreach($services as $service)
                            <option value="{{ $service->slug }}" @selected($selectedService === $service->slug)>{{ $service->title }}</option>
                        @endforeach
                    </select>
                </label>
            </div>

            <div class="list-inline" style="justify-content: flex-end;">
                <a class="btn secondary" href="{{ route('portfolio.index') }}">Сбросить</a>
                <button class="btn" type="submit">Применить</button>
            </div>
        </form>
    </section>

    <section class="section">
        <div class="grid columns-3">
            @forelse($cases as $case)
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
                            • {{ $case->is_nda ? $case->public_client_label ?? 'NDA' : $case->client_name }}
                        @endif
                    </p>
                    @if($case->description)
                        <p>{{ Str::limit(strip_tags($case->description), 140) }}</p>
                    @endif

                    @if($case->products->isNotEmpty() || $case->services->isNotEmpty())
                        <div class="meta" style="margin-top: 0.75rem;">
                            @foreach($case->products as $product)
                                <span class="tag" style="background: #f1f5f9; color: #0f172a;">{{ $product->title }}</span>
                            @endforeach
                            @foreach($case->services as $service)
                                <span class="tag" style="background: #ecfeff; color: #0ea5e9;">{{ $service->title }}</span>
                            @endforeach
                        </div>
                    @endif
                    <a class="btn secondary" style="margin-top: 0.75rem;" href="{{ route('portfolio.show', $case->slug) }}">Открыть кейс</a>
                </article>
            @empty
                <p>Кейсов по выбранным условиям нет.</p>
            @endforelse
        </div>

        @include('public.partials.pagination', ['paginator' => $cases])
    </section>
@endsection
