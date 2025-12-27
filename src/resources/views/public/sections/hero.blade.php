<section class="section hero">
    @if(!empty($section['settings']['title']))
        <h1>{{ $section['settings']['title'] }}</h1>
    @endif

    @if(!empty($section['settings']['subtitle']))
        <p>{{ $section['settings']['subtitle'] }}</p>
    @endif

    @if(!empty($section['settings']['cta_buttons']) && is_array($section['settings']['cta_buttons']))
        <div class="list-inline" style="justify-content: center; gap: 0.75rem;">
            @foreach($section['settings']['cta_buttons'] as $button)
                @if(!empty($button['text']) && !empty($button['link']))
                    <a class="btn" href="{{ $button['link'] }}">{{ $button['text'] }}</a>
                @endif
            @endforeach
        </div>
    @endif
</section>
