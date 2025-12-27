@php
    $formType = $section['settings']['form_type'] ?? 'call';
    $formName = [
        'calculation' => 'Получить расчет',
        'question' => 'Задать вопрос',
        'call' => 'Заказать звонок',
    ][$formType] ?? 'Оставить заявку';
@endphp

<div class="section cta">
    @if(!empty($section['settings']['title']))
        <h2>{{ $section['settings']['title'] }}</h2>
    @endif
    
    @if(!empty($section['settings']['description']))
        <p style="margin-bottom: 2rem; font-size: 1.1rem;">{{ $section['settings']['description'] }}</p>
    @endif
    
    <div style="background: white; padding: 2rem; border-radius: 0.5rem; max-width: 480px; margin: 0 auto;">
        <p style="color: #6c757d; text-align: center; margin-bottom: 1rem;">
            {{ $formName }}
        </p>
        <div style="display: grid; gap: 0.75rem; text-align: left;">
            <label style="font-size: 0.95rem; color: #6c757d;">
                Поля формы будут загружены с сервера для кода, выбранного в админке.
            </label>
            <div style="padding: 0.75rem; border: 1px dashed #dee2e6; border-radius: 0.5rem; color: #6c757d;">
                Пример: имя, телефон, комментарий, чекбокс согласия
            </div>
            <button type="button" style="padding: 0.9rem 1.1rem; border-radius: 0.5rem; border: none; background: #007bff; color: white; font-weight: 600;">Отправить заявку</button>
            <small style="color: #6c757d; text-align: center; display: block;">При публикации формы будут работать статусы загрузки, успеха и ошибок.</small>
        </div>
    </div>
</div>
