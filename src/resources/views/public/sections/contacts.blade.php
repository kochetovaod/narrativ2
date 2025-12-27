@php
    $settings = $section['settings'] ?? [];
    $mapEmbed = $settings['map_embed'] ?? null;
@endphp

<section class="section">
    @if(!empty($settings['title']))
        <h2>{{ $settings['title'] }}</h2>
    @else
        <h2>Контакты</h2>
    @endif

    <div class="grid columns-2" style="margin-top: 1rem; align-items: start;">
        <div class="card">
            <div class="grid columns-2">
                <div>
                    <h3>📞 Телефон</h3>
                    <p>{{ $settings['phone'] ?? '+7 (000) 000-00-00' }}</p>
                </div>
                <div>
                    <h3>✉️ Email</h3>
                    <p>{{ $settings['email'] ?? 'info@example.com' }}</p>
                </div>
                <div>
                    <h3>📍 Адрес</h3>
                    <p>{{ $settings['address'] ?? 'Адрес уточняется' }}</p>
                </div>
                <div>
                    <h3>⏰ Часы работы</h3>
                    <p>{{ $settings['work_hours'] ?? 'Пн-Пт: 09:00-18:00' }}</p>
                </div>
            </div>
        </div>

        <div class="card">
            @if(!empty($mapEmbed))
                <div class="map-embed" style="margin-bottom: 1rem;">
                    {!! $mapEmbed !!}
                </div>
            @else
                <p class="meta" style="margin-bottom: 1rem;">Карта появится здесь.</p>
            @endif

            @if(!empty($settings['cta_title']) || !empty($settings['cta_text']) || !empty($settings['cta_button_text']))
                <div>
                    @if(!empty($settings['cta_title']))
                        <h3>{{ $settings['cta_title'] }}</h3>
                    @endif
                    @if(!empty($settings['cta_text']))
                        <p>{{ $settings['cta_text'] }}</p>
                    @endif
                    <div class="list-inline">
                        @if(!empty($settings['cta_button_text']))
                            <a class="btn" href="{{ $settings['cta_button_link'] ?? '#' }}">{{ $settings['cta_button_text'] }}</a>
                        @endif
                        @if(!empty($settings['cta_secondary_text']))
                            <a class="btn secondary" href="{{ $settings['cta_secondary_link'] ?? '#' }}">{{ $settings['cta_secondary_text'] }}</a>
                        @endif
                    </div>
                </div>
            @endif
        </div>
    </div>
</section>
