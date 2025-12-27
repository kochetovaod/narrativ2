<div class="form-editor">
    <div class="row">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <h4 class="mb-0">
                        <i class="icon-list"></i>
                        {{ __('Конструктор полей') }}
                    </h4>
                </div>
                <div class="card-body">
                    <div id="form-fields" class="form-fields">
                        @if($form && $form->fields->count() > 0)
                            @include('orchid.form.partials.form-fields', ['fields' => $form->fields])
                        @else
                            <p class="text-muted">{{ __('Форма не содержит полей') }}</p>
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
                        {{ __('Добавить поле') }}
                    </h4>
                </div>
                <div class="card-body">
                    <form id="add-field-form" method="POST" action="{{ route('platform.forms.add-field') }}">
                        @csrf
                        <input type="hidden" name="form_id" value="{{ $form->id ?? '' }}">

                        <div class="form-group">
                            <label for="field_type">{{ __('Тип поля') }}</label>
                            <select name="field_type" id="field_type" class="form-control" required>
                                @foreach($fieldTypes as $value => $label)
                                    <option value="{{ $value }}">{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>

                        <button type="submit" class="btn btn-primary btn-block">
                            <i class="icon-plus"></i>
                            {{ __('Добавить поле') }}
                        </button>
                    </form>
                </div>
            </div>

            <div class="card">
                <div class="card-header">
                    <h4 class="mb-0">
                        <i class="icon-info"></i>
                        {{ __('Типы полей') }}
                    </h4>
                </div>
                <div class="card-body">
                    <div class="field-types-info">
                        <ul class="list-unstyled">
                            <li><strong>Текст</strong> - однострочное поле</li>
                            <li><strong>Email</strong> - поле для email</li>
                            <li><strong>Телефон</strong> - поле с маской</li>
                            <li><strong>Выпадающий список</strong> - выбор из вариантов</li>
                            <li><strong>Чекбокс</strong> - согласие</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal для редактирования поля -->
<div class="modal fade" id="editFieldModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">{{ __('Редактирование поля') }}</h5>
                <button type="button" class="close" data-dismiss="modal">
                    <span>&times;</span>
                </button>
            </div>
            <form id="edit-field-form" method="POST">
                @csrf
                @method('PUT')
                <div class="modal-body">
                    <div class="form-group">
                        <label for="edit_key">{{ __('Ключ поля') }}</label>
                        <input type="text" name="key" id="edit_key" class="form-control" required>
                        <small class="form-text text-muted">{{ __('Используется для получения значения') }}</small>
                    </div>
                    
                    <div class="form-group">
                        <label for="edit_label">{{ __('Подпись') }}</label>
                        <input type="text" name="label" id="edit_label" class="form-control" required>
                    </div>

                    <div class="form-group">
                        <label for="edit_type">{{ __('Тип поля') }}</label>
                        <select name="type" id="edit_type" class="form-control" required>
                            @foreach($fieldTypes as $value => $label)
                                <option value="{{ $value }}">{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="edit_mask">{{ __('Маска') }}</label>
                        <input type="text" name="mask" id="edit_mask" class="form-control" placeholder="+7 (999) 999-99-99">
                        <small class="form-text text-muted">{{ __('Для полей телефон и дата') }}</small>
                    </div>

                    <div class="form-group">
                        <label for="edit_options">{{ __('Опции') }}</label>
                        <textarea name="options" id="edit_options" class="form-control" rows="3" placeholder="Вариант 1, Вариант 2, Вариант 3"></textarea>
                        <small class="form-text text-muted">{{ __('Через запятую, для select и radio') }}</small>
                    </div>

                    <div class="form-group">
                        <label for="edit_validation">{{ __('Правила валидации') }}</label>
                        <input type="text" name="validation_rules" id="edit_validation" class="form-control" placeholder="required|min:3">
                    </div>

                    <div class="form-group">
                        <div class="form-check">
                            <input type="checkbox" name="is_required" id="edit_is_required" class="form-check-input">
                            <label class="form-check-label" for="edit_is_required">
                                {{ __('Обязательное поле') }}
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
.form-editor .card {
    margin-bottom: 20px;
}

.form-fields {
    min-height: 200px;
}

.form-field {
    background: #f8f9fa;
    border: 1px solid #dee2e6;
    border-radius: 4px;
    margin: 8px 0;
    padding: 12px;
}

.form-field.dragging {
    opacity: 0.5;
    transform: rotate(2deg);
}

.form-field-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    cursor: move;
}

.form-field-title {
    font-weight: 500;
    flex-grow: 1;
}

.form-field-actions {
    display: flex;
    gap: 8px;
}

.form-field-type {
    font-size: 0.85em;
    color: #6c757d;
    margin-top: 4px;
}

.form-field-required {
    color: #dc3545;
    font-size: 0.8em;
    margin-left: 8px;
}

.drag-handle {
    cursor: move;
    padding: 4px;
    color: #6c757d;
}

.drag-handle:hover {
    color: #495057;
}

.field-types-info ul li {
    padding: 5px 0;
    border-bottom: 1px solid #e9ecef;
    font-size: 0.9em;
}

.field-types-info ul li:last-child {
    border-bottom: none;
}

.btn-xs {
    padding: 2px 6px;
    font-size: 12px;
}

.form-group {
    margin-bottom: 12px;
}

#edit_options {
    font-family: monospace;
    font-size: 0.9em;
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Обработка формы добавления поля
    document.getElementById('add-field-form').addEventListener('submit', function(e) {
        e.preventDefault();
        
        const formData = new FormData(this);
        
        fetch(this.action, {
            method: 'POST',
            body: formData,
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            }
        }).then(response => {
            if (response.ok) {
                location.reload();
            }
        });
    });

    // Drag & Drop функциональность для полей
    const formFields = document.querySelectorAll('.form-field');
    
    formFields.forEach(field => {
        makeDraggable(field);
    });

    function makeDraggable(field) {
        const dragHandle = field.querySelector('.drag-handle');
        
        dragHandle.addEventListener('mousedown', startDrag);
        
        function startDrag(e) {
            e.preventDefault();
            field.classList.add('dragging');
            
            document.addEventListener('mousemove', drag);
            document.addEventListener('mouseup', stopDrag);
        }
        
        function drag(e) {
            // Логика перетаскивания
            console.log('Dragging field...');
        }
        
        function stopDrag(e) {
            field.classList.remove('dragging');
            document.removeEventListener('mousemove', drag);
            document.removeEventListener('mouseup', stopDrag);
        }
    }

    // Кнопки действий с полями
    document.addEventListener('click', function(e) {
        if (e.target.classList.contains('btn-edit-field')) {
            e.preventDefault();
            const fieldId = e.target.dataset.fieldId;
            const fieldData = JSON.parse(e.target.dataset.fieldData);
            
            // Заполняем форму редактирования
            document.getElementById('edit_key').value = fieldData.key || '';
            document.getElementById('edit_label').value = fieldData.label || '';
            document.getElementById('edit_type').value = fieldData.type || 'text';
            document.getElementById('edit_mask').value = fieldData.mask || '';
            document.getElementById('edit_options').value = (fieldData.options || []).join(', ');
            document.getElementById('edit_validation').value = fieldData.validation_rules || '';
            document.getElementById('edit_is_required').checked = fieldData.is_required || false;
            
            const form = document.getElementById('edit-field-form');
            form.action = `/admin/forms/fields/${fieldId}`;
            
            $('#editFieldModal').modal('show');
        }
        
        if (e.target.classList.contains('btn-delete-field')) {
            e.preventDefault();
            const fieldId = e.target.dataset.fieldId;
            const fieldLabel = e.target.dataset.fieldLabel;
            
            if (confirm(`Удалить поле "${fieldLabel}"?`)) {
                fetch(`/admin/forms/fields/${fieldId}`, {
                    method: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                        'Content-Type': 'application/json',
                    }
                }).then(response => {
                    if (response.ok) {
                        location.reload();
                    }
                });
            }
        }
    });
});
</script>
