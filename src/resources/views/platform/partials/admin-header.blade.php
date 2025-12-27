@props([
    'title',
    'description' => null,
])

<header class="d-flex align-items-start align-items-md-center justify-content-between flex-wrap gap-3 mb-4">
    <div>
        <div class="text-uppercase text-muted small fw-semibold mb-1">
            {{ config('app.name') }}
        </div>
        <h1 class="h4 mb-2">{{ $title }}</h1>

        @if ($description)
            <p class="text-muted mb-0">{{ $description }}</p>
        @endif
    </div>

    <div class="ms-md-auto">
        @if (view()->exists('platform::partials.breadcrumbs'))
            @include('platform::partials.breadcrumbs')
        @endif
    </div>
</header>
