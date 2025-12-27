<div class="section portfolio">
    @if(!empty($section['settings']['title']))
        <h2>{{ $section['settings']['title'] }}</h2>
    @endif
    
    @if(!empty($section['settings']['description']))
        <p style="margin-bottom: 2rem; font-size: 1.1rem;">{{ $section['settings']['description'] }}</p>
    @endif
    
    @if(!empty($section['settings']['show_filters']))
        <div style="margin-bottom: 2rem;">
            <button class="btn btn-secondary" style="margin-right: 0.5rem;">Все</button>
            <button class="btn btn-secondary" style="margin-right: 0.5rem;">По товарам</button>
            <button class="btn btn-secondary">По услугам</button>
        </div>
    @endif
    
    <div class="portfolio-grid">
        @for($i = 1; $i <= ($section['settings']['limit'] ?? 6); $i++)
            <div class="portfolio-card">
                <div class="portfolio-image">
                    Изображение проекта {{ $i }}
                </div>
                <div class="portfolio-content">
                    <h3>Проект {{ $i }}</h3>
                    <p>Описание проекта {{ $i }} с результатами и достижениями</p>
                    <p><small>Клиент: Компания {{ chr(64 + $i) }}</small></p>
                </div>
            </div>
        @endfor
    </div>
</div>
