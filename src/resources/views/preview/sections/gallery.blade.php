<div class="section gallery">
    @if(!empty($section['settings']['title']))
        <h2>{{ $section['settings']['title'] }}</h2>
    @endif
    
    <div class="gallery-grid">
        @for($i = 1; $i <= 9; $i++)
            <div class="gallery-item">
                Изображение {{ $i }}
            </div>
        @endfor
    </div>
    
    @if(!empty($section['settings']['lightbox']))
        <p style="margin-top: 1rem; color: #6c757d; text-align: center;">
            <small>Лайтбокс включен - изображения откроются в полном размере при клике</small>
        </p>
    @endif
</div>
