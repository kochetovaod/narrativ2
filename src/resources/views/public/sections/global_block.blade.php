@php
    $blockCode = $section['settings']['block_code'] ?? null;
    $block = $blockCode
        ? \App\Models\GlobalBlock::query()->where('code', $blockCode)->where('is_active', true)->first()
        : null;
@endphp

<section class="section">
    <h2>{{ $section['settings']['title'] ?? 'Глобальный блок' }}</h2>

    @if($block)
        <div class="card">
            <h3 style="margin-top: 0;">{{ $block->title }}</h3>
            @if(is_string($block->content))
                {!! $block->content !!}
            @elseif(is_array($block->content))
                <pre style="white-space: pre-wrap; word-break: break-word;">{{ json_encode($block->content, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT) }}</pre>
            @else
                <p>Содержимое блока пока пустое.</p>
            @endif
        </div>
    @else
        <div class="card">
            <p>Блок с кодом <strong>{{ $blockCode ?: 'не указан' }}</strong> не найден или отключен.</p>
        </div>
    @endif
</section>
