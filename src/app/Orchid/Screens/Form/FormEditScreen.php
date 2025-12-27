<?php

declare(strict_types=1);

namespace App\Orchid\Screens\Form;

use App\Models\Form;
use App\Models\FormField;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Orchid\Screen\Actions\Button;
use Orchid\Screen\Actions\Link;
use Orchid\Screen\Screen;
use Orchid\Support\Facades\Alert;
use Orchid\Support\Facades\Layout;

class FormEditScreen extends Screen
{
    public ?int $formId;

    /**
     * @var string|array<int, string>
     */
    public $permission = 'platform.forms';

    /**
     * Fetch the form data for editing.
     */
    public function query(?int $formId = null): iterable
    {
        $this->formId = $formId;

        $form = $formId
            ? Form::with('fields')->findOrFail($formId)
            : new Form;

        return [
            'form' => $form,
            'formTypes' => [
                'callback' => 'Обратный звонок',
                'calc' => 'Калькулятор',
                'question' => 'Вопрос специалисту',
            ],
            'fieldTypes' => [
                'text' => 'Текстовое поле',
                'textarea' => 'Многострочный текст',
                'email' => 'Email',
                'tel' => 'Телефон',
                'select' => 'Выпадающий список',
                'checkbox' => 'Чекбокс',
                'radio' => 'Радиокнопки',
                'date' => 'Дата',
                'file' => 'Файл',
            ],
            'captchaModes' => [
                'none' => 'Без капчи',
                'recaptcha' => 'Google reCAPTCHA',
                'hcaptcha' => 'hCaptcha',
            ],
        ];
    }

    public function name(): ?string
    {
        return $this->formId ? __('Редактирование формы') : __('Создание формы');
    }

    public function description(): ?string
    {
        return __('Конструктор форм с настройкой полей и уведомлений');
    }

    public function commandBar(): iterable
    {
        return [
            Button::make(__('Сохранить'))
                ->icon('check')
                ->method('save'),

            Link::make(__('Назад к списку'))
                ->icon('action-undo')
                ->route('platform.forms.index'),
        ];
    }

    public function layout(): iterable
    {
        return [
            Layout::view('orchid.form.edit'),
        ];
    }

    public function save(Request $request, ?int $formId = null): void
    {
        $validated = $request->validate([
            'form.code' => [
                'required',
                'string',
                'max:50',
                Rule::unique('forms', 'code')->ignore($formId),
            ],
            'form.title' => 'required|string|max:255',
            'form.is_active' => 'boolean',
            'form.captcha_mode' => 'in:none,recaptcha,hcaptcha',
            'form.notification_email' => 'nullable|array',
            'form.notification_telegram' => 'nullable|array',
        ]);

        $form = $formId
            ? Form::findOrFail($formId)
            : new Form;

        $form->fill($validated['form']);
        $form->save();

        // Сохраняем поля формы
        $this->saveFormFields($form, $request->input('form_fields', []));

        Alert::success(__('Форма сохранена'));

        $this->redirect(route('platform.forms.index'));
    }

    private function saveFormFields(Form $form, array $fieldsData): void
    {
        $existingFieldIds = [];

        foreach ($fieldsData as $fieldData) {
            $fieldId = $fieldData['id'] ?? null;
            $sort = (int) ($fieldData['sort'] ?? 0);

            $field = $fieldId
                ? FormField::where('id', $fieldId)->where('form_id', $form->id)->firstOrFail()
                : new FormField;

            $field->fill([
                'form_id' => $form->id,
                'key' => $fieldData['key'] ?? '',
                'label' => $fieldData['label'] ?? '',
                'type' => $fieldData['type'] ?? 'text',
                'mask' => $fieldData['mask'] ?? '',
                'is_required' => (bool) ($fieldData['is_required'] ?? false),
                'sort' => $sort,
                'options' => ! empty($fieldData['options']) ? explode(',', $fieldData['options']) : [],
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

    public function addField(Request $request): void
    {
        $formId = $request->input('form_id');
        $fieldType = $request->input('field_type', 'text');

        $form = Form::findOrFail($formId);

        // Подсчитываем максимальный сортировочный индекс
        $maxSort = FormField::where('form_id', $formId)->max('sort') ?? -1;

        $fieldData = $this->getDefaultFieldData($fieldType);

        $field = FormField::create([
            'form_id' => $formId,
            'key' => $fieldData['key'],
            'label' => $fieldData['label'],
            'type' => $fieldType,
            'sort' => $maxSort + 1,
            'is_required' => false,
            'options' => [],
        ]);

        Alert::success(__('Поле добавлено'));

        $this->redirect(route('platform.forms.edit', $formId));
    }

    public function deleteField(Request $request): void
    {
        $fieldId = $request->input('field_id');
        $formId = $request->input('form_id');

        $field = FormField::findOrFail($fieldId);
        $field->delete();

        Alert::success(__('Поле удалено'));

        $this->redirect(route('platform.forms.edit', $formId));
    }

    public function reorderFields(Request $request): void
    {
        $formId = $request->input('form_id');
        $orders = $request->input('orders', []);

        foreach ($orders as $orderData) {
            $fieldId = $orderData['id'] ?? null;
            $newSort = (int) ($orderData['sort'] ?? 0);

            if ($fieldId) {
                FormField::where('id', $fieldId)->where('form_id', $formId)->update([
                    'sort' => $newSort,
                ]);
            }
        }

        Alert::success(__('Порядок полей обновлен'));

        $this->redirect(route('platform.forms.edit', $formId));
    }

    private function getDefaultFieldData(string $fieldType): array
    {
        return match ($fieldType) {
            'email' => ['key' => 'email', 'label' => 'Email'],
            'tel' => ['key' => 'phone', 'label' => 'Телефон'],
            'name' => ['key' => 'name', 'label' => 'Имя'],
            'text' => ['key' => 'text_'.uniqid(), 'label' => 'Текстовое поле'],
            'textarea' => ['key' => 'message_'.uniqid(), 'label' => 'Сообщение'],
            'select', 'radio' => ['key' => 'select_'.uniqid(), 'label' => 'Выбор'],
            'checkbox' => ['key' => 'checkbox_'.uniqid(), 'label' => 'Согласие'],
            'date' => ['key' => 'date_'.uniqid(), 'label' => 'Дата'],
            'file' => ['key' => 'file_'.uniqid(), 'label' => 'Файл'],
            default => ['key' => 'field_'.uniqid(), 'label' => 'Поле'],
        };
    }
}
