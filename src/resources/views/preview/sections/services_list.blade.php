<div class="section services-list">
    @if(!empty($section['settings']['title']))
        <h2>{{ $section['settings']['title'] }}</h2>
    @endif
    
    @if(!empty($section['settings']['description']))
        <p style="margin-bottom: 2rem; font-size: 1.1rem;">{{ $section['settings']['description'] }}</p>
    @endif
    
    <div class="services-grid">
        @for($i = 1; $i <= 4; $i++)
            <div class="service-card">
                <h3>Услуга {{ $i }}</h3>
                <p>Описание услуги {{ $i }} с деталями и преимуществами</p>
                <a href="#" class="btn btn-secondary" style="margin-top: 1rem;">Подробнее</a>
            </div>
        @endfor
    </div>
</div>
