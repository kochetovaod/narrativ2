@php
    $footerMenu = \App\Models\Menu::where('code', 'footer')
        ->with(['items' => function($query) {
            $query->where('is_visible', true)
                  ->orderBy('sort');
        }, 'items.children' => function($query) {
            $query->where('is_visible', true)
                  ->orderBy('sort');
        }])
        ->first();
@endphp

@if($footerMenu && $footerMenu->items->count() > 0)
<footer class="bg-dark text-light py-5">
    <div class="container">
        <div class="row">
            <div class="col-lg-8">
                <div class="row">
                    @foreach($footerMenu->items as $item)
                        @if($item->children->count() > 0)
                            <div class="col-md-4 mb-4">
                                <h6 class="text-uppercase fw-bold">{{ $item->title }}</h6>
                                <ul class="list-unstyled">
                                    @foreach($item->children as $child)
                                        <li class="mb-2">
                                            <a href="{{ $child->url }}" class="text-light text-decoration-none">
                                                {{ $child->title }}
                                            </a>
                                        </li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif
                    @endforeach
                </div>
            </div>
            
            <div class="col-lg-4">
                <h6 class="text-uppercase fw-bold mb-4">Контакты</h6>
                <p>
                    <i class="fas fa-map-marker-alt me-2"></i>
                    Москва, ул. Примерная, д. 1
                </p>
                <p>
                    <i class="fas fa-phone me-2"></i>
                    <a href="tel:+74951234567" class="text-light text-decoration-none">
                        +7 (495) 123-45-67
                    </a>
                </p>
                <p>
                    <i class="fas fa-envelope me-2"></i>
                    <a href="mailto:info@example.com" class="text-light text-decoration-none">
                        info@example.com
                    </a>
                </p>
                
                <div class="mt-4">
                    <h6 class="text-uppercase fw-bold mb-3">Мы в социальных сетях</h6>
                    <a href="#" class="text-light me-3" aria-label="Facebook">
                        <i class="fab fa-facebook-f fa-lg"></i>
                    </a>
                    <a href="#" class="text-light me-3" aria-label="Instagram">
                        <i class="fab fa-instagram fa-lg"></i>
                    </a>
                    <a href="#" class="text-light me-3" aria-label="LinkedIn">
                        <i class="fab fa-linkedin-in fa-lg"></i>
                    </a>
                    <a href="#" class="text-light" aria-label="Telegram">
                        <i class="fab fa-telegram-plane fa-lg"></i>
                    </a>
                </div>
            </div>
        </div>
        
        <hr class="my-4">
        
        <div class="row align-items-center">
            <div class="col-md-6">
                <p class="mb-0">
                    &copy; {{ date('Y') }} Компания. Все права защищены.
                </p>
            </div>
            
            <div class="col-md-6 text-md-end">
                <div class="footer-links">
                    @foreach($footerMenu->items as $item)
                        @if($item->children->count() == 0)
                            <a href="{{ $item->url }}" class="text-light text-decoration-none me-3">
                                {{ $item->title }}
                            </a>
                        @endif
                    @endforeach
                </div>
            </div>
        </div>
    </div>
</footer>
@endif
