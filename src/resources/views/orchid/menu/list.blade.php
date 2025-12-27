<div class="platform-menu">
    <div class="row">
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h4 class="mb-0">
                        <i class="icon-menu"></i>
                        {{ __('Верхнее меню') }}
                    </h4>
                </div>
                <div class="card-body">
                    @if($headerMenu && $headerMenu->items->count() > 0)
                        <ul class="menu-items-list">
                            @foreach($headerMenu->items as $item)
                                <li class="menu-item">
                                    <span class="item-title">{{ $item->title }}</span>
                                    @if($item->url)
                                        <small class="text-muted">({{ $item->url }})</small>
                                    @endif
                                    @if($item->children->count() > 0)
                                        <ul class="submenu-items">
                                            @foreach($item->children as $child)
                                                <li class="menu-item submenu-item">
                                                    <span class="item-title">{{ $child->title }}</span>
                                                    @if($child->url)
                                                        <small class="text-muted">({{ $child->url }})</small>
                                                    @endif
                                                </li>
                                            @endforeach
                                        </ul>
                                    @endif
                                </li>
                            @endforeach
                        </ul>
                    @else
                        <p class="text-muted">{{ __('Верхнее меню пустое') }}</p>
                    @endif
                </div>
            </div>
        </div>

        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h4 class="mb-0">
                        <i class="icon-menu"></i>
                        {{ __('Нижнее меню') }}
                    </h4>
                </div>
                <div class="card-body">
                    @if($footerMenu && $footerMenu->items->count() > 0)
                        <ul class="menu-items-list">
                            @foreach($footerMenu->items as $item)
                                <li class="menu-item">
                                    <span class="item-title">{{ $item->title }}</span>
                                    @if($item->url)
                                        <small class="text-muted">({{ $item->url }})</small>
                                    @endif
                                    @if($item->children->count() > 0)
                                        <ul class="submenu-items">
                                            @foreach($item->children as $child)
                                                <li class="menu-item submenu-item">
                                                    <span class="item-title">{{ $child->title }}</span>
                                                    @if($child->url)
                                                        <small class="text-muted">({{ $child->url }})</small>
                                                    @endif
                                                </li>
                                            @endforeach
                                        </ul>
                                    @endif
                                </li>
                            @endforeach
                        </ul>
                    @else
                        <p class="text-muted">{{ __('Нижнее меню пустое') }}</p>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.platform-menu .card {
    margin-bottom: 20px;
}

.menu-items-list {
    list-style: none;
    padding: 0;
    margin: 0;
}

.menu-item {
    padding: 8px 0;
    border-bottom: 1px solid #eee;
}

.menu-item:last-child {
    border-bottom: none;
}

.submenu-items {
    list-style: none;
    padding-left: 20px;
    margin-top: 8px;
}

.submenu-item {
    border-left: 2px solid #e9ecef;
    padding-left: 12px;
    margin-top: 4px;
}

.item-title {
    font-weight: 500;
}
</style>
