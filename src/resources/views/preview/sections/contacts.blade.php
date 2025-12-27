<div class="section contacts">
    @if(!empty($section['settings']['title']))
        <h2>{{ $section['settings']['title'] }}</h2>
    @endif
    
    <div class="contact-info">
        <div class="contact-item">
            <h3>📞 Телефон</h3>
            <p>+7 (999) 123-45-67</p>
        </div>
        <div class="contact-item">
            <h3>✉️ Email</h3>
            <p>info@example.com</p>
        </div>
        <div class="contact-item">
            <h3>📍 Адрес</h3>
            <p>г. Москва, ул. Примерная, д. 1</p>
        </div>
        <div class="contact-item">
            <h3>⏰ Часы работы</h3>
            <p>Пн-Пт: 9:00-18:00</p>
        </div>
    </div>
</div>
