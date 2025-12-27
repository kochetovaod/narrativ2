@php
    $settings = $section['settings'] ?? [];
@endphp

<section class="section">
    @if(!empty($settings['title']))
        <h2>{{ $settings['title'] }}</h2>
    @else
        <h2>Контакты</h2>
    @endif

    <div class="grid columns-2" style="margin-top: 1rem;">
        <div class="card">
            <h3>📞 Телефон</h3>
            <p>{{ $settings['phone'] ?? '+7 (000) 000-00-00' }}</p>
        </div>
        <div class="card">
            <h3>✉️ Email</h3>
            <p>{{ $settings['email'] ?? 'info@example.com' }}</p>
        </div>
        <div class="card">
            <h3>📍 Адрес</h3>
            <p>{{ $settings['address'] ?? 'Адрес уточняется' }}</p>
        </div>
        <div class="card">
            <h3>⏰ Часы работы</h3>
            <p>{{ $settings['work_hours'] ?? 'Пн-Пт: 09:00-18:00' }}</p>
        </div>
    </div>
</section>
