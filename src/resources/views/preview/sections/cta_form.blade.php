<div class="section cta">
    @if(!empty($section['settings']['title']))
        <h2>{{ $section['settings']['title'] }}</h2>
    @endif
    
    @if(!empty($section['settings']['description']))
        <p style="margin-bottom: 2rem; font-size: 1.1rem;">{{ $section['settings']['description'] }}</p>
    @endif
    
    <div style="background: white; padding: 2rem; border-radius: 0.5rem; max-width: 400px; margin: 0 auto;">
        <p style="color: #6c757d; text-align: center; margin-bottom: 1rem;">
            @switch($section['settings']['form_type'] ?? 'call')
                @case('calculation')
                    Форма "Получить расчет"
                    @break
                @case('question')
                    Форма "Задать вопрос"
                    @break
                @default
                    Форма "Заказать звонок"
            @endswitch
        </p>
        <p style="color: #6c757d; text-align: center;">
            Здесь будет форма заявки<br>
            <small>(интеграция с формами будет добавлена позже)</small>
        </p>
    </div>
</div>
