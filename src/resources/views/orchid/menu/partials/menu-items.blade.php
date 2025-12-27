@if(isset($items) && $items->count() > 0)
    <ul class="menu-items" data-menu-code="{{ $menuCode }}">
        @foreach($items as $item)
            <li class="menu-item" data-item-id="{{ $item->id }}">
                <div class="menu-item-header">
                    <span class="drag-handle" title="{{ __('Перетащить для изменения порядка') }}">
                        <i class="icon-move"></i>
                    </span>
                    
                    <div class="menu-item-content">
                        <div class="menu-item-title">
                            @if(!$item->is_visible)
                                <i class="icon-eye-off text-muted" title="{{ __('Скрытый элемент') }}"></i>
                            @else
                                <i class="icon-eye text-success" title="{{ __('Видимый элемент') }}"></i>
                            @endif
                            <span class="item-title-text">{{ $item->title }}</span>
                        </div>
                        
                        @if($item->url)
                            <div class="menu-item-url">
                                <small>{{ $item->url }}</small>
                                @if($item->entity_type)
                                    <span class="badge badge-secondary ml-1">{{ $item->entity_type }}</span>
                                @endif
                            </div>
                        @endif
                    </div>

                    <div class="menu-item-actions">
                        <button type="button" 
                                class="btn btn-xs btn-outline-primary btn-edit-item"
                                data-item-id="{{ $item->id }}"
                                data-item-title="{{ $item->title }}"
                                data-item-url="{{ $item->url }}"
                                data-item-visible="{{ $item->is_visible ? 'true' : 'false' }}"
                                title="{{ __('Редактировать') }}">
                            <i class="icon-pencil"></i>
                        </button>

                        <button type="button" 
                                class="btn btn-xs btn-outline-danger btn-delete-item"
                                data-item-id="{{ $item->id }}"
                                data-item-title="{{ $item->title }}"
                                title="{{ __('Удалить') }}">
                            <i class="icon-trash"></i>
                        </button>

                        <button type="button" 
                                class="btn btn-xs btn-outline-success btn-add-submenu"
                                data-item-id="{{ $item->id }}"
                                data-parent-id="{{ $item->id }}"
                                title="{{ __('Добавить подменю') }}">
                            <i class="icon-plus"></i>
                        </button>
                    </div>
                </div>

                {{-- Рекурсивно включаем дочерние элементы --}}
                @if($item->children->count() > 0)
                    @include('orchid.menu.partials.menu-items', [
                        'items' => $item->children, 
                        'menuCode' => $menuCode
                    ])
                @endif
            </li>
        @endforeach
    </ul>
@endif
