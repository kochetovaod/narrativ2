<section class="section">
    @if(!empty($section['settings']['title']))
        <h2>{{ $section['settings']['title'] }}</h2>
    @endif

    <div class="grid columns-3" style="margin-top: 1rem;">
        @if(!empty($section['settings']['advantages']) && is_array($section['settings']['advantages']))
            @foreach($section['settings']['advantages'] as $advantage)
                <article class="card">
                    @if(!empty($advantage['title']))
                        <h3 style="margin-top: 0;">{{ $advantage['title'] }}</h3>
                    @endif
                    @if(!empty($advantage['description']))
                        <p>{{ $advantage['description'] }}</p>
                    @endif
                </article>
            @endforeach
        @else
            @for($i = 1; $i <= 3; $i++)
                <article class="card">
                    <h3 style="margin-top: 0;">Преимущество {{ $i }}</h3>
                    <p>Добавьте описание преимущества, чтобы объяснить ценность.</p>
                </article>
            @endfor
        @endif
    </div>
</section>
