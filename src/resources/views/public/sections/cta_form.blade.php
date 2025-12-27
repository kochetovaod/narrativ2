@php
    $formType = $section['settings']['form_type'] ?? 'call';
    $formCode = [
        'call' => 'callback',
        'calculation' => 'calc',
        'question' => 'question',
    ][$formType] ?? 'callback';
@endphp

@include('public.partials.form-block', [
    'formCode' => $formCode,
    'title' => $section['settings']['title'] ?? __('Оставьте заявку'),
    'description' => $section['settings']['description'] ?? null,
    'buttonLabel' => $section['settings']['button_text'] ?? __('Отправить'),
    'pageTitle' => $section['settings']['title'] ?? null,
])
