<div class="section global-block">
    @if(!empty($section['settings']['block_code']))
        @php
            $blockCode = $section['settings']['block_code'];
            // Здесь будет логика загрузки глобального блока
            // Пока показываем заглушку
        @endphp
        
        <div style="background: #f8f9fa; border: 1px dashed #dee2e6; padding: 2rem; text-align: center; border-radius: 0.5rem;">
            <h3 style="color: #6c757d; margin-bottom: 1rem;">Глобальный блок</h3>
            <p style="color: #6c757d; margin-bottom: 1rem;">Код: <code>{{ $blockCode }}</code></p>
            <p style="color: #6c757d; font-size: 0.9rem;">
                <em>Здесь будет отображаться содержимое глобального блока.<br>
                Интеграция с глобальными блоками будет добавлена при публикации страницы.</em>
            </p>
        </div>
    @else
        <div style="background: #fff3cd; border: 1px solid #ffeaa7; padding: 2rem; text-align: center; border-radius: 0.5rem; color: #856404;">
            <h3>Глобальный блок не выбран</h3>
            <p>Выберите глобальный блок в настройках секции</p>
        </div>
    @endif
</div>
