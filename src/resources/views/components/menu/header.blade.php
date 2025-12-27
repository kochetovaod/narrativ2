@php
    use App\Models\Menu;

    $headerMenu = Menu::query()
        ->where('code', 'header')
        ->with([
            'items' => fn ($query) => $query->visible()->whereNull('parent_id')->orderBy('sort'),
            'items.children' => fn ($query) => $query->visible()->orderBy('sort'),
        ])
        ->first();
@endphp

<header class="site-header">
    <div class="container nav-container">
        <a href="{{ route('home') }}" class="logo">{{ config('app.name', 'Narrativ') }}</a>

        @if($headerMenu && $headerMenu->items->isNotEmpty())
            <nav aria-label="Основная навигация">
                <ul>
                    @foreach($headerMenu->items as $item)
                        <li>
                            <a href="{{ $item->resolvedUrl() }}">{{ $item->title }}</a>
                            @if($item->children->isNotEmpty())
                                <ul class="submenu" aria-label="{{ $item->title }}">
                                    @foreach($item->children as $child)
                                        <li>
                                            <a href="{{ $child->resolvedUrl() }}">{{ $child->title }}</a>
                                        </li>
                                    @endforeach
                                </ul>
                            @endif
                        </li>
                    @endforeach
                </ul>
            </nav>
        @endif

        <form class="search" action="{{ route('search') }}" method="get" role="search">
            <input id="search-input" name="q" value="{{ request('q') }}" type="search"
                   placeholder="Поиск по сайту"
                   aria-label="Поиск по сайту"
                   data-suggestions-url="{{ route('search.suggestions') }}">
            <button type="submit">Поиск</button>
            <div class="search-suggestions" id="search-suggestions"></div>
        </form>
    </div>
</header>
