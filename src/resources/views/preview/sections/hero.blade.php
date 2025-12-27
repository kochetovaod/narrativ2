<div class="section hero">
    @if(!empty($section['settings']['title']))
        <h1>{{ $section['settings']['title'] }}</h1>
    @endif
    
    @if(!empty($section['settings']['subtitle']))
        <div class="subtitle">{{ $section['settings']['subtitle'] }}</div>
    @endif
    
    @if(!empty($section['settings']['cta_buttons']) && is_array($section['settings']['cta_buttons']))
        <div class="cta-buttons">
            @foreach($section['settings']['cta_buttons'] as $button)
                @if(!empty($button['text']) && !empty($button['link']))
                    <a href="{{ $button['link'] }}" class="btn">{{ $button['text'] }}</a>
                @endif
            @endforeach
        </div>
    @endif
</div>
