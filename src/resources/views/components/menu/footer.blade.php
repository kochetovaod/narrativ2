@php
    use App\Models\Menu;

    $footerMenu = Menu::query()
        ->where('code', 'footer')
        ->with([
            'items' => fn ($query) => $query->visible()->whereNull('parent_id')->orderBy('sort'),
            'items.children' => fn ($query) => $query->visible()->orderBy('sort'),
        ])
        ->first();
@endphp

<footer class="site-footer">
    <div class="container footer-grid">
        <div>
            <div class="logo">{{ config('app.name', 'Narrativ') }}</div>
            <p>Дизайн, производство и внедрение решений под ключ.</p>
        </div>

        @if($footerMenu && $footerMenu->items->isNotEmpty())
            @foreach($footerMenu->items as $item)
                @if($item->children->isNotEmpty())
                    <div>
                        <div class="meta" style="margin-bottom: 0.5rem;">{{ $item->title }}</div>
                        <div class="list-inline" style="flex-direction: column; align-items: flex-start;">
                            @foreach($item->children as $child)
                                <a href="{{ $child->resolvedUrl() }}">{{ $child->title }}</a>
                            @endforeach
                        </div>
                    </div>
                @endif
            @endforeach

            @foreach($footerMenu->items->filter(fn ($item) => $item->children->isEmpty()) as $single)
                <div>
                    <a href="{{ $single->resolvedUrl() }}">{{ $single->title }}</a>
                </div>
            @endforeach
        @endif

        <div>
            <div class="meta" style="margin-bottom: 0.5rem;">Контакты</div>
            <p>Тел.: <a href="tel:+7">+7 (000) 000-00-00</a></p>
            <p>Email: <a href="mailto:hello@example.com">hello@example.com</a></p>
        </div>
        <div>
            <div class="meta" style="margin-bottom: 0.5rem;">Соцсети</div>
            <div class="list-inline">
                <a href="#" aria-label="Telegram">Telegram</a>
                <a href="#" aria-label="WhatsApp">WhatsApp</a>
                <a href="#" aria-label="VK">VK</a>
            </div>
        </div>
    </div>
</footer>
