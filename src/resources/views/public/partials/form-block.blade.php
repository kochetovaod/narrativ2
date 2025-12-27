@php
    $consentDocUrl = $consentDocUrl ?? route('documents.consent');
    $buttonLabel = $buttonLabel ?? __('Отправить заявку');
    $placementType = $placementType ?? 'inline';
    $pageTitle = $pageTitle ?? null;
@endphp

<section class="section form-section" data-placement="{{ $placementType }}">
    <div class="card form-card">
        @if(!empty($title))
            <h2 style="margin-top: 0;">{{ $title }}</h2>
        @endif

        @if(!empty($description))
            <p class="meta">{{ $description }}</p>
        @endif

        <div
            class="js-remote-form"
            data-form-code="{{ $formCode }}"
            data-preview-url="{{ route('forms.preview', ['formCode' => $formCode]) }}"
            data-submit-url="{{ route('forms.submit', ['formCode' => $formCode]) }}"
            data-consent-url="{{ $consentDocUrl }}"
            data-source-url="{{ request()->fullUrl() }}"
            data-page-title="{{ $pageTitle ?? ($title ?? config('app.name')) }}"
            data-submit-label="{{ $buttonLabel }}"
            data-placement="{{ $placementType }}"
            data-csrf="{{ csrf_token() }}"
        >
            <div class="form-status is-loading">Загружаем форму...</div>
        </div>
    </div>
</section>
