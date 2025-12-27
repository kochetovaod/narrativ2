@php
    if (empty($placement?->form) || ! $placement->form->is_active) {
        return;
    }

    $settings = $placement->settings ?? [];
    $title = $title ?? $settings['title'] ?? $placement->form->title;
    $description = $description ?? $settings['description'] ?? null;
    $buttonLabel = $buttonLabel ?? $settings['button_text'] ?? $settings['button'] ?? __('Отправить');
@endphp

@include('public.partials.form-block', [
    'buttonLabel' => $buttonLabel,
    'consentDocUrl' => $consentDocUrl ?? null,
    'description' => $description,
    'formCode' => $placement->form->code,
    'pageTitle' => $pageTitle ?? null,
    'placementType' => $placement->placement,
    'title' => $title,
])
