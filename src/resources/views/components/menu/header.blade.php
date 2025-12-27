@php
    $headerMenu = \App\Models\Menu::where('code', 'header')
        ->with(['items' => function($query) {
            $query->where('is_visible', true)
                  ->orderBy('sort');
        }, 'items.children' => function($query) {
            $query->where('is_visible', true)
                  ->orderBy('sort');
        }])
        ->first();
@endphp

@if($headerMenu && $headerMenu->items->count() > 0)
<nav class="navbar navbar-expand-lg navbar-light bg-white">
    <div class="container">
        <a class="navbar-brand" href="/">
            <img src="/images/logo.png" alt="Логотип" height="40" class="d-inline-block align-text-top">
        </a>
        
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#headerMenu" 
                aria-controls="headerMenu" aria-expanded="false" aria-label="Переключение навигации">
            <span class="navbar-toggler-icon"></span>
        </button>
        
        <div class="collapse navbar-collapse" id="headerMenu">
            <ul class="navbar-nav me-auto mb-2 mb-lg-0">
                @foreach($headerMenu->items as $item)
                    @if($item->children->count() > 0)
                        {{-- Выпадающее меню --}}
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle" href="{{ $item->url }}" 
                               id="navbarDropdown{{ $item->id }}" role="button" 
                               data-bs-toggle="dropdown" aria-expanded="false">
                                {{ $item->title }}
                            </a>
                            <ul class="dropdown-menu" aria-labelledby="navbarDropdown{{ $item->id }}">
                                @foreach($item->children as $child)
                                    <li>
                                        <a class="dropdown-item" href="{{ $child->url }}">
                                            {{ $child->title }}
                                        </a>
                                    </li>
                                @endforeach
                            </ul>
                        </li>
                    @else
                        {{-- Обычная ссылка --}}
                        <li class="nav-item">
                            <a class="nav-link" href="{{ $item->url }}">
                                {{ $item->title }}
                            </a>
                        </li>
                    @endif
                @endforeach
            </ul>
            
            {{-- Поиск в header --}}
            <form class="d-flex" role="search" action="/search" method="GET">
                <input class="form-control me-2" type="search" placeholder="Поиск..." 
                       aria-label="Поиск" name="q" value="{{ request('q') }}">
                <button class="btn btn-outline-success" type="submit">
                    <i class="fas fa-search"></i>
                </button>
            </form>
        </div>
    </div>
</nav>
@endif
