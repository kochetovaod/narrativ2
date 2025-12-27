<div class="section contacts">
    @if(!empty($section['settings']['title']))
        <h2>{{ $section['settings']['title'] }}</h2>
    @endif
    
    <div class="contact-info">
        <div class="contact-item">
            <h3>📞 Телефон</h3>
            <p>{{ $section['settings']['phone'] ?? '+7 (999) 123-45-67' }}</p>
        </div>
        <div class="contact-item">
            <h3>✉️ Email</h3>
            <p>{{ $section['settings']['email'] ?? 'info@example.com' }}</p>
        </div>
        <div class="contact-item">
            <h3>📍 Адрес</h3>
            <p>{{ $section['settings']['address'] ?? 'г. Москва, ул. Примерная, д. 1' }}</p>
        </div>
        <div class="contact-item">
            <h3>⏰ Часы работы</h3>
            <p>{{ $section['settings']['work_hours'] ?? 'Пн-Пт: 9:00-18:00' }}</p>
        </div>
    </div>

    <div class="contact-cta">
        @if(!empty($section['settings']['map_embed']))
            <div class="map-embed">
                {!! $section['settings']['map_embed'] !!}
            </div>
        @endif

        @if(!empty($section['settings']['cta_title']) || !empty($section['settings']['cta_button_text']))
            <div class="cta-block">
                <h3>{{ $section['settings']['cta_title'] ?? 'Свяжитесь с нами' }}</h3>
                @if(!empty($section['settings']['cta_text']))
                    <p>{{ $section['settings']['cta_text'] }}</p>
                @endif
                <div style="display: flex; gap: 0.5rem;">
                    @if(!empty($section['settings']['cta_button_text']))
                        <a href="{{ $section['settings']['cta_button_link'] ?? '#' }}" class="btn">
                            {{ $section['settings']['cta_button_text'] }}
                        </a>
                    @endif
                    @if(!empty($section['settings']['cta_secondary_text']))
                        <a href="{{ $section['settings']['cta_secondary_link'] ?? '#' }}" class="btn secondary">
                            {{ $section['settings']['cta_secondary_text'] }}
                        </a>
                    @endif
                </div>
            </div>
        @endif
    </div>
</div>
