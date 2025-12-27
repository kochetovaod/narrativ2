@if(!empty($breadcrumbs) && is_array($breadcrumbs))
    <nav class="breadcrumbs" aria-label="Хлебные крошки">
        @foreach($breadcrumbs as $index => $crumb)
            @if(!$loop->first)
                <span>›</span>
            @endif

            @if(!empty($crumb['is_active']))
                <span aria-current="page">{{ $crumb['title'] }}</span>
            @else
                <a href="{{ $crumb['url'] }}">{{ $crumb['title'] }}</a>
            @endif
        @endforeach
    </nav>
@endif
