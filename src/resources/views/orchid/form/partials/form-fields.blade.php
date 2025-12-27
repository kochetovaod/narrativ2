@if(isset($fields) && $fields->count() > 0)
    <ul class="form-fields-list">
        @foreach($fields as $field)
            <li class="form-field" data-field-id="{{ $field->id }}" data-field-sort="{{ $field->sort }}">
                <div class="form-field-header">
                    <span class="drag-handle" title="{{ __('Перетащить для изменения порядка') }}">
                        <i class="icon-move"></i>
                    </span>
                    
                    <div class="form-field-content">
                        <div class="form-field-title">
                            @if($field->is_required)
                                <span class="form-field-required" title="{{ __('Обязательное поле') }}">*</span>
                            @endif
                            <span class="field-title-text">{{ $field->label }}</span>
                        </div>
                        
                        <div class="form-field-info">
                            <small>
                                <span class="field-key">{{ $field->key }}</span>
                                <span class="badge badge-secondary ml-1">{{ $field->type }}</span>
                                @if($field->mask)
                                    <span class="badge badge-info ml-1">{{ $field->mask }}</span>
                                @endif
                                @if(!empty($field->options))
                                    <span class="badge badge-warning ml-1">{{ count($field->options) }} опций</span>
                                @endif
                            </small>
                        </div>
                        
                        @if($field->validation_rules)
                            <div class="form-field-validation">
                                <small class="text-muted">Валидация: {{ $field->validation_rules }}</small>
                            </div>
                        @endif
                    </div>

                    <div class="form-field-actions">
                        <button type="button" 
                                class="btn btn-xs btn-outline-primary btn-edit-field"
                                data-field-id="{{ $field->id }}"
                                data-field-label="{{ $field->label }}"
                                data-field-key="{{ $field->key }}"
                                data-field-type="{{ $field->type }}"
                                data-field-mask="{{ $field->mask }}"
                                data-field-options="{{ json_encode($field->options) }}"
                                data-field-validation="{{ $field->validation_rules }}"
                                data-field-required="{{ $field->is_required ? 'true' : 'false' }}"
                                data-field-data='{{ json_encode([
                                    "key" => $field->key,
                                    "label" => $field->label,
                                    "type" => $field->type,
                                    "mask" => $field->mask,
                                    "options" => $field->options,
                                    "validation_rules" => $field->validation_rules,
                                    "is_required" => $field->is_required
                                ]) }}'
                                title="{{ __('Редактировать поле') }}">
                            <i class="icon-pencil"></i>
                        </button>

                        <button type="button" 
                                class="btn btn-xs btn-outline-danger btn-delete-field"
                                data-field-id="{{ $field->id }}"
                                data-field-label="{{ $field->label }}"
                                title="{{ __('Удалить поле') }}">
                            <i class="icon-trash"></i>
                        </button>
                    </div>
                </div>
            </li>
        @endforeach
    </ul>
@endif
