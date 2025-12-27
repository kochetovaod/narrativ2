@if(isset($breadcrumbs) && count($breadcrumbs) > 0)
<nav aria-label="breadcrumb">
    <ol class="breadcrumb">
        @foreach($breadcrumbs as $index => $breadcrumb)
            @if($breadcrumb['is_active'])
                <li class="breadcrumb-item active" aria-current="page">
                    {{ $breadcrumb['title'] }}
                </li>
            @else
                <li class="breadcrumb-item">
                    <a href="{{ $breadcrumb['url'] }}" class="text-decoration-none">
                        {{ $breadcrumb['title'] }}
                    </a>
                </li>
            @endif
        @endforeach
    </ol>
</nav>
@endif
