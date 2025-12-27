<div class="section text @if(!empty($section['settings']['alignment']))text-{{ $section['settings']['alignment'] }}@endif">
    @if(!empty($section['settings']['title']))
        <h2>{{ $section['settings']['title'] }}</h2>
    @endif
    
    @if(!empty($section['settings']['content']))
        <div class="content">{{ nl2br($section['settings']['content']) }}</div>
    @endif
</div>
