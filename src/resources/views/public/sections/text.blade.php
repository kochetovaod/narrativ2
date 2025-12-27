<section class="section text">
    @if(!empty($section['settings']['title']))
        <h2>{{ $section['settings']['title'] }}</h2>
    @endif
    @php
        $content = $section['settings']['content'] ?? ($section['value'] ?? '');
        if (is_array($content)) {
            $content = json_encode($content, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
        }
    @endphp
    <div class="content">
        {!! nl2br(e($content)) !!}
    </div>
</section>
