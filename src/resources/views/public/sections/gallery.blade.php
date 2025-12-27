@php
    $images = $section['settings']['images'] ?? [];
    $items = is_array($images) && count($images) > 0 ? $images : range(1, $section['settings']['limit'] ?? 6);
@endphp

<section class="section">
    @if(!empty($section['settings']['title']))
        <h2>{{ $section['settings']['title'] }}</h2>
    @endif

    <div class="gallery-grid" style="margin-top: 1rem;">
        @foreach($items as $index => $image)
            <div class="gallery-item">
                @if(is_string($image))
                    <span>{{ $image }}</span>
                @else
                    <span>Изображение {{ $index + 1 }}</span>
                @endif
            </div>
        @endforeach
    </div>

    @if(!empty($section['settings']['lightbox']))
        <p class="meta" style="text-align: center; margin-top: 0.75rem;">Изображения можно открыть в полном размере.</p>
    @endif
</section>
