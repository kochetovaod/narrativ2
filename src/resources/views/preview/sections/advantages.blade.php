<div class="section advantages">
    @if(!empty($section['settings']['title']))
        <h2>{{ $section['settings']['title'] }}</h2>
    @endif
    
    <div class="advantages-list">
        @if(!empty($section['settings']['advantages']) && is_array($section['settings']['advantages']))
            @foreach($section['settings']['advantages'] as $advantage)
                <div class="advantage-item">
                    @if(!empty($advantage['title']))
                        <h3>{{ $advantage['title'] }}</h3>
                    @endif
                    @if(!empty($advantage['description']))
                        <p>{{ $advantage['description'] }}</p>
                    @endif
                </div>
            @endforeach
        @else
            @for($i = 1; $i <= 4; $i++)
                <div class="advantage-item">
                    <h3>Преимущество {{ $i }}</h3>
                    <p>Описание преимущества {{ $i }} с детальным объяснением</p>
                </div>
            @endfor
        @endif
    </div>
</div>
