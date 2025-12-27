@push('head')
    <meta name="robots" content="noindex">
@endpush

@include('platform.partials.admin-header', [
    'title' => __('Панель управления'),
    'description' => __('Стартовая страница административной панели'),
])

<div class="row">
    <div class="col-12 col-lg-8">
        <div class="card mb-3">
            <div class="card-body">
                <h5 class="card-title mb-3">{{ __('Добро пожаловать в административную панель') }}</h5>
                <p class="text-muted mb-0">
                    {{ __('Используйте меню слева, чтобы управлять пользователями, ролями и экранами Orchid.') }}
                </p>
            </div>
        </div>
    </div>
</div>
