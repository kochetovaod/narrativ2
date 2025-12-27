<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Form;
use App\Models\FormField;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class FormBuilderService
{
    /**
     * Создание новой формы с полями
     */
    public function createForm(array $data): Form
    {
        return DB::transaction(function () use ($data) {
            // Создаем форму
            $form = Form::create([
                'code' => $data['code'],
                'title' => $data['title'],
                'is_active' => $data['is_active'] ?? true,
                'captcha_mode' => $data['captcha_mode'] ?? 'none',
                'notification_email' => $data['notification_email'] ?? [],
                'notification_telegram' => $data['notification_telegram'] ?? [],
            ]);

            // Создаем поля формы
            if (! empty($data['fields'])) {
                $this->saveFormFields($form, $data['fields']);
            }

            return $form;
        });
    }

    /**
     * Обновление формы с полями
     */
    public function updateForm(Form $form, array $data): Form
    {
        return DB::transaction(function () use ($form, $data) {
            // Обновляем данные формы
            $form->update([
                'code' => $data['code'],
                'title' => $data['title'],
                'is_active' => $data['is_active'] ?? $form->is_active,
                'captcha_mode' => $data['captcha_mode'] ?? $form->captcha_mode,
                'notification_email' => $data['notification_email'] ?? $form->notification_email,
                'notification_telegram' => $data['notification_telegram'] ?? $form->notification_telegram,
            ]);

            // Обновляем поля формы
            if (array_key_exists('fields', $data)) {
                $this->saveFormFields($form, $data['fields']);
            }

            return $form->fresh(['fields']);
        });
    }

    /**
     * Сохранение полей формы
     */
    private function saveFormFields(Form $form, array $fieldsData): void
    {
        $existingFieldIds = [];

        foreach ($fieldsData as $index => $fieldData) {
            // Валидация данных поля
            $this->validateFieldData($fieldData);

            $fieldId = $fieldData['id'] ?? null;
            $sort = (int) ($fieldData['sort'] ?? $index);

            $field = $fieldId
                ? FormField::where('id', $fieldId)->where('form_id', $form->id)->firstOrFail()
                : new FormField;

            $field->fill([
                'form_id' => $form->id,
                'key' => $this->generateUniqueFieldKey($form, $fieldData['key'], $fieldId),
                'label' => $fieldData['label'],
                'type' => $fieldData['type'],
                'mask' => $fieldData['mask'] ?? '',
                'is_required' => (bool) ($fieldData['is_required'] ?? false),
                'sort' => $sort,
                'options' => $this->parseFieldOptions($fieldData),
                'validation_rules' => $fieldData['validation_rules'] ?? '',
            ]);

            $field->save();
            $existingFieldIds[] = $field->id;
        }

        // Удаляем поля, которых нет в сохраненных данных
        FormField::where('form_id', $form->id)
            ->whereNotIn('id', $existingFieldIds)
            ->delete();
    }

    /**
     * Валидация данных поля
     */
    private function validateFieldData(array $fieldData): void
    {
        $validator = Validator::make($fieldData, [
            'key' => 'required|string|max:50|regex:/^[a-zA-Z_][a-zA-Z0-9_]*$/',
            'label' => 'required|string|max:255',
            'type' => 'required|in:text,textarea,email,tel,select,checkbox,radio,date,file',
            'mask' => 'nullable|string|max:50',
            'is_required' => 'boolean',
            'options' => 'nullable|string',
            'validation_rules' => 'nullable|string|max:255',
        ]);

        if ($validator->fails()) {
            throw new \InvalidArgumentException('Некорректные данные поля: '.$validator->errors()->first());
        }
    }

    /**
     * Генерация уникального ключа поля
     */
    private function generateUniqueFieldKey(Form $form, string $baseKey, ?int $fieldId = null): string
    {
        $key = $baseKey;
        $counter = 1;

        while (true) {
            $query = FormField::where('form_id', $form->id)
                ->where('key', $key);

            if ($fieldId) {
                $query->where('id', '!=', $fieldId);
            }

            if (! $query->exists()) {
                return $key;
            }

            $key = $baseKey.'_'.$counter;
            $counter++;
        }
    }

    /**
     * Парсинг опций поля
     */
    private function parseFieldOptions(array $fieldData): array
    {
        if (empty($fieldData['options'])) {
            return [];
        }

        // Если опции уже в виде массива
        if (is_array($fieldData['options'])) {
            return $fieldData['options'];
        }

        // Парсим из строки (через запятую)
        $optionsString = trim($fieldData['options']);
        if (empty($optionsString)) {
            return [];
        }

        return array_map('trim', explode(',', $optionsString));
    }

    /**
     * Добавление нового поля
     */
    public function addField(Form $form, string $type, array $options = []): FormField
    {
        // Генерируем данные по умолчанию для типа поля
        $defaultData = $this->getDefaultFieldData($type);
        $fieldData = array_merge($defaultData, $options);

        // Валидация
        $this->validateFieldData($fieldData);

        // Подсчитываем максимальный сортировочный индекс
        $maxSort = FormField::where('form_id', $form->id)->max('sort') ?? -1;

        $field = FormField::create([
            'form_id' => $form->id,
            'key' => $this->generateUniqueFieldKey($form, $fieldData['key']),
            'label' => $fieldData['label'],
            'type' => $type,
            'sort' => $maxSort + 1,
            'is_required' => $fieldData['is_required'] ?? false,
            'mask' => $fieldData['mask'] ?? '',
            'options' => $this->parseFieldOptions($fieldData),
            'validation_rules' => $fieldData['validation_rules'] ?? '',
        ]);

        return $field;
    }

    /**
     * Удаление поля
     */
    public function deleteField(FormField $field): void
    {
        $field->delete();
    }

    /**
     * Переупорядочивание полей
     */
    public function reorderFields(Form $form, array $orders): void
    {
        foreach ($orders as $orderData) {
            $fieldId = $orderData['id'] ?? null;
            $newSort = (int) ($orderData['sort'] ?? 0);

            if ($fieldId) {
                FormField::where('id', $fieldId)
                    ->where('form_id', $form->id)
                    ->update(['sort' => $newSort]);
            }
        }
    }

    /**
     * Получение данных по умолчанию для типа поля
     */
    private function getDefaultFieldData(string $type): array
    {
        $defaults = [
            'text' => ['key' => 'text', 'label' => 'Текстовое поле', 'is_required' => false],
            'textarea' => ['key' => 'message', 'label' => 'Сообщение', 'is_required' => false],
            'email' => ['key' => 'email', 'label' => 'Email', 'is_required' => true, 'validation_rules' => 'email'],
            'tel' => ['key' => 'phone', 'label' => 'Телефон', 'is_required' => true, 'mask' => '+7 (999) 999-99-99'],
            'select' => ['key' => 'select', 'label' => 'Выберите значение', 'is_required' => false, 'options' => 'Вариант 1, Вариант 2, Вариант 3'],
            'checkbox' => ['key' => 'consent', 'label' => 'Согласие', 'is_required' => true],
            'radio' => ['key' => 'radio', 'label' => 'Выберите один вариант', 'is_required' => false, 'options' => 'Вариант 1, Вариант 2'],
            'date' => ['key' => 'date', 'label' => 'Дата', 'is_required' => false, 'mask' => '99.99.9999'],
            'file' => ['key' => 'file', 'label' => 'Файл', 'is_required' => false],
        ];

        return $defaults[$type] ?? ['key' => 'field', 'label' => 'Поле', 'is_required' => false];
    }

    /**
     * Генерация HTML разметки формы для публичного отображения
     */
    public function generateFormHtml(Form $form): string
    {
        $html = "<form class='dynamic-form' data-form-code='{$form->code}'>\n";
        $html .= "    <input type='hidden' name='form_code' value='{$form->code}'>\n";
        $html .= "    <input type='hidden' name='source_url' value=''>\n";
        $html .= "    <input type='hidden' name='page_title' value=''>\n\n";

        foreach ($form->fields->sortBy('sort') as $field) {
            $html .= $this->generateFieldHtml($field);
        }

        // Поля для UTM параметров
        $html .= "    <!-- UTM поля -->\n";
        $utmFields = ['utm_source', 'utm_medium', 'utm_campaign', 'utm_term', 'utm_content'];
        foreach ($utmFields as $utmField) {
            $html .= "    <input type='hidden' name='{$utmField}' value=''>\n";
        }

        $html .= "\n    <button type='submit' class='btn btn-primary'>Отправить</button>\n";
        $html .= '</form>';

        return $html;
    }

    /**
     * Генерация HTML разметки для одного поля
     */
    private function generateFieldHtml(FormField $field): string
    {
        $required = $field->is_required ? 'required' : '';
        $mask = $field->mask ? "data-mask='{$field->mask}'" : '';

        $html = "    <!-- {$field->label} -->\n";

        switch ($field->type) {
            case 'text':
            case 'email':
            case 'tel':
            case 'date':
                $html .= "    <div class='form-group'>\n";
                $html .= "        <label for='{$field->key}'>{$field->label}".($field->is_required ? ' *' : '')."</label>\n";
                $html .= "        <input type='{$field->type}' name='{$field->key}' id='{$field->key}' class='form-control' {$required} {$mask}>\n";
                $html .= "    </div>\n\n";
                break;

            case 'textarea':
                $html .= "    <div class='form-group'>\n";
                $html .= "        <label for='{$field->key}'>{$field->label}".($field->is_required ? ' *' : '')."</label>\n";
                $html .= "        <textarea name='{$field->key}' id='{$field->key}' class='form-control' rows='4' {$required}></textarea>\n";
                $html .= "    </div>\n\n";
                break;

            case 'select':
                $html .= "    <div class='form-group'>\n";
                $html .= "        <label for='{$field->key}'>{$field->label}".($field->is_required ? ' *' : '')."</label>\n";
                $html .= "        <select name='{$field->key}' id='{$field->key}' class='form-control' {$required}>\n";
                $html .= "            <option value=''>Выберите значение</option>\n";
                foreach ($field->options as $option) {
                    $html .= "            <option value='{$option}'>{$option}</option>\n";
                }
                $html .= "        </select>\n";
                $html .= "    </div>\n\n";
                break;

            case 'checkbox':
                $html .= "    <div class='form-check'>\n";
                $html .= "        <input type='checkbox' name='{$field->key}' id='{$field->key}' class='form-check-input' {$required}>\n";
                $html .= "        <label class='form-check-label' for='{$field->key}'>{$field->label}".($field->is_required ? ' *' : '')."</label>\n";
                $html .= "    </div>\n\n";
                break;

            case 'radio':
                $html .= "    <div class='form-group'>\n";
                $html .= "        <label>{$field->label}".($field->is_required ? ' *' : '')."</label>\n";
                foreach ($field->options as $index => $option) {
                    $checked = $index === 0 ? 'checked' : '';
                    $html .= "        <div class='form-check'>\n";
                    $html .= "            <input type='radio' name='{$field->key}' id='{$field->key}_{$index}' value='{$option}' class='form-check-input' {$required} {$checked}>\n";
                    $html .= "            <label class='form-check-label' for='{$field->key}_{$index}'>{$option}</label>\n";
                    $html .= "        </div>\n";
                }
                $html .= "    </div>\n\n";
                break;

            case 'file':
                $html .= "    <div class='form-group'>\n";
                $html .= "        <label for='{$field->key}'>{$field->label}".($field->is_required ? ' *' : '')."</label>\n";
                $html .= "        <input type='file' name='{$field->key}' id='{$field->key}' class='form-control-file' {$required}>\n";
                $html .= "    </div>\n\n";
                break;
        }

        return $html;
    }

    /**
     * Клонирование формы
     */
    public function cloneForm(Form $form, string $newCode, string $newTitle): Form
    {
        return DB::transaction(function () use ($form, $newCode, $newTitle) {
            $clonedForm = $form->replicate();
            $clonedForm->code = $newCode;
            $clonedForm->title = $newTitle;
            $clonedForm->push();

            // Клонируем поля
            foreach ($form->fields as $originalField) {
                $clonedField = $originalField->replicate();
                $clonedField->form_id = $clonedForm->id;
                // Генерируем новый ключ для поля
                $clonedField->key = $this->generateUniqueFieldKey($clonedForm, $originalField->key);
                $clonedField->push();
            }

            return $clonedForm->fresh(['fields']);
        });
    }

    /**
     * Экспорт формы в JSON
     */
    public function exportFormToJson(Form $form): array
    {
        return [
            'form' => [
                'code' => $form->code,
                'title' => $form->title,
                'is_active' => $form->is_active,
                'captcha_mode' => $form->captcha_mode,
                'notification_email' => $form->notification_email,
                'notification_telegram' => $form->notification_telegram,
            ],
            'fields' => $form->fields->sortBy('sort')->map(function ($field) {
                return [
                    'key' => $field->key,
                    'label' => $field->label,
                    'type' => $field->type,
                    'mask' => $field->mask,
                    'is_required' => $field->is_required,
                    'options' => $field->options,
                    'validation_rules' => $field->validation_rules,
                ];
            })->toArray(),
        ];
    }

    /**
     * Импорт формы из JSON
     */
    public function importFormFromJson(array $data): Form
    {
        if (! isset($data['form']) || ! isset($data['fields'])) {
            throw new \InvalidArgumentException('Некорректный формат данных для импорта');
        }

        // Проверяем уникальность кода формы
        if (Form::where('code', $data['form']['code'])->exists()) {
            throw new \InvalidArgumentException('Форма с таким кодом уже существует');
        }

        return $this->createForm($data);
    }
}
