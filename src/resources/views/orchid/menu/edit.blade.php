<div class="menu-editor">
    <div class="row">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <h4 class="mb-0">
                        <i class="icon-list"></i>
                        {{ __('Элементы меню') }}
                    </h4>
                </div>
                <div class="card-body">
                    <div id="menu-tree" class="menu-tree">
                        @if($menu && $menu->items->count() > 0)
                            @include('orchid.menu.partials.menu-items', ['items' => $menu->items, 'menuCode' => $menu->code])
                        @else
                            <p class="text-muted">{{ __('Меню пустое') }}</p>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card">
                <div class="card-header">
                    <h4 class="mb-0">
                        <i class="icon-plus"></i>
                        {{ __('Добавить элемент') }}
                    </h4>
                </div>
                <div class="card-body">
                    <form id="add-menu-item-form" method="POST" action="{{ route('platform.systems.menu.add-item') }}">
                        @csrf
                        <input type="hidden" name="menu_code" value="{{ $menu->code ?? '' }}">
                        <input type="hidden" name="parent_id" value="">

                        <div class="form-group">
                            <label for="item_type">{{ __('Тип элемента') }}</label>
                            <select name="item_type" id="item_type" class="form-control" required>
                                <option value="">{{ __('Выберите тип') }}</option>
                                <option value="custom_url">{{ __('Произвольная ссылка') }}</option>
                                <option value="page">{{ __('Страница') }}</option>
                                <option value="service">{{ __('Услуга') }}</option>
                                <option value="product_category">{{ __('Категория товаров') }}</option>
                            </select>
                        </div>

                        <div class="form-group" id="entity-select" style="display: none;">
                            <label for="entity_id">{{ __('Выберите элемент') }}</label>
                            <select name="entity_id" id="entity_id" class="form-control">
                                <option value="">{{ __('Выберите из списка') }}</option>
                            </select>
                        </div>

                        <div class="form-group" id="custom-url-fields" style="display: none;">
                            <label for="custom_title">{{ __('Название') }}</label>
                            <input type="text" name="custom_title" id="custom_title" class="form-control" placeholder="{{ __('Название пункта меню') }}">
                            
                            <label for="custom_url">{{ __('URL') }}</label>
                            <input type="text" name="custom_url" id="custom_url" class="form-control" placeholder="/contact">
                        </div>

                        <button type="submit" class="btn btn-primary btn-block">
                            <i class="icon-plus"></i>
                            {{ __('Добавить') }}
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal для редактирования элемента -->
<div class="modal fade" id="editItemModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">{{ __('Редактирование элемента') }}</h5>
                <button type="button" class="close" data-dismiss="modal">
                    <span>&times;</span>
                </button>
            </div>
            <form id="edit-menu-item-form" method="POST">
                @csrf
                @method('PUT')
                <div class="modal-body">
                    <div class="form-group">
                        <label for="edit_title">{{ __('Название') }}</label>
                        <input type="text" name="title" id="edit_title" class="form-control" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="edit_url">{{ __('URL') }}</label>
                        <input type="text" name="url" id="edit_url" class="form-control">
                    </div>

                    <div class="form-group">
                        <div class="form-check">
                            <input type="checkbox" name="is_visible" id="edit_is_visible" class="form-check-input" checked>
                            <label class="form-check-label" for="edit_is_visible">
                                {{ __('Видимый в меню') }}
                            </label>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">{{ __('Отмена') }}</button>
                    <button type="submit" class="btn btn-primary">{{ __('Сохранить') }}</button>
                </div>
            </form>
        </div>
    </div>
</div>

<style>
.menu-editor .card {
    margin-bottom: 20px;
}

.menu-tree {
    min-height: 200px;
}

.menu-item {
    background: #f8f9fa;
    border: 1px solid #dee2e6;
    border-radius: 4px;
    margin: 8px 0;
    padding: 12px;
}

.menu-item.dragging {
    opacity: 0.5;
    transform: rotate(5deg);
}

.menu-item-header {
    display: flex;
    justify-content: between;
    align-items: center;
    cursor: move;
}

.menu-item-title {
    font-weight: 500;
    flex-grow: 1;
}

.menu-item-actions {
    display: flex;
    gap: 8px;
}

.menu-item-url {
    font-size: 0.85em;
    color: #6c757d;
    margin-top: 4px;
}

.submenu-items {
    margin-left: 24px;
    margin-top: 8px;
}

.drag-handle {
    cursor: move;
    padding: 4px;
    color: #6c757d;
}

.drag-handle:hover {
    color: #495057;
}

.btn-xs {
    padding: 2px 6px;
    font-size: 12px;
}

.form-group {
    margin-bottom: 12px;
}

#custom-url-fields input {
    margin-bottom: 8px;
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Обработка смены типа элемента
    const itemTypeSelect = document.getElementById('item_type');
    const entitySelect = document.getElementById('entity-select');
    const customUrlFields = document.getElementById('custom-url-fields');
    const entityIdSelect = document.getElementById('entity_id');

    itemTypeSelect.addEventListener('change', function() {
        const selectedType = this.value;
        
        // Скрываем все дополнительные поля
        entitySelect.style.display = 'none';
        customUrlFields.style.display = 'none';
        
        if (selectedType === 'custom_url') {
            customUrlFields.style.display = 'block';
        } else if (selectedType && selectedType !== '') {
            entitySelect.style.display = 'block';
            loadEntityOptions(selectedType);
        }
    });

    function loadEntityOptions(type) {
        // Загружаем опции через AJAX
        fetch(`/admin/menu/get-entities/${type}`)
            .then(response => response.json())
            .then(data => {
                entityIdSelect.innerHTML = '<option value="">Выберите из списка</option>';
                data.forEach(entity => {
                    const option = document.createElement('option');
                    option.value = entity.id;
                    option.textContent = entity.title;
                    entityIdSelect.appendChild(option);
                });
            });
    }

    // Drag & Drop функциональность
    const menuItems = document.querySelectorAll('.menu-item');
    
    menuItems.forEach(item => {
        makeDraggable(item);
    });

    function makeDraggable(item) {
        const dragHandle = item.querySelector('.drag-handle');
        
        dragHandle.addEventListener('mousedown', startDrag);
        
        function startDrag(e) {
            e.preventDefault();
            item.classList.add('dragging');
            
            document.addEventListener('mousemove', drag);
            document.addEventListener('mouseup', stopDrag);
        }
        
        function drag(e) {
            // Логика перетаскивания
            console.log('Dragging...');
        }
        
        function stopDrag(e) {
            item.classList.remove('dragging');
            document.removeEventListener('mousemove', drag);
            document.removeEventListener('mouseup', stopDrag);
        }
    }

    // Кнопки действий с элементами
    document.addEventListener('click', function(e) {
        if (e.target.classList.contains('btn-edit-item')) {
            e.preventDefault();
            const itemId = e.target.dataset.itemId;
            const itemTitle = e.target.dataset.itemTitle;
            const itemUrl = e.target.dataset.itemUrl;
            const itemVisible = e.target.dataset.itemVisible === 'true';
            
            // Заполняем форму редактирования
            document.getElementById('edit_title').value = itemTitle;
            document.getElementById('edit_url').value = itemUrl || '';
            document.getElementById('edit_is_visible').checked = itemVisible;
            
            const form = document.getElementById('edit-menu-item-form');
            form.action = `/admin/menu/items/${itemId}`;
            
            $('#editItemModal').modal('show');
        }
        
        if (e.target.classList.contains('btn-delete-item')) {
            e.preventDefault();
            const itemId = e.target.dataset.itemId;
            const itemTitle = e.target.dataset.itemTitle;
            
            if (confirm(`Удалить элемент "${itemTitle}"?`)) {
                fetch(`/admin/menu/items/${itemId}`, {
                    method: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                        'Content-Type': 'application/json',
                    }
                }).then(() => {
                    location.reload();
                });
            }
        }
    });
});
</script>
