<div class="section categories-grid">
    @if(!empty($section['settings']['title']))
        <h2>{{ $section['settings']['title'] }}</h2>
    @endif
    
    @if(!empty($section['settings']['description']))
        <p style="margin-bottom: 2rem; font-size: 1.1rem;">{{ $section['settings']['description'] }}</p>
    @endif
    
    <div class="categories-grid">
        @for($i = 1; $i <= 6; $i++)
            <div class="category-card">
                <h3>Категория {{ $i }}</h3>
                <p>Описание категории {{ $i }}</p>
                @if(!empty($section['settings']['show_count']))
                    <p><small>Товаров: {{ rand(10, 50) }}</small></p>
                @endif
            </div>
        @endfor
    </div>
</div>
