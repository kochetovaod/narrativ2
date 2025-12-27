<?php

namespace Database\Seeders;

use App\Models\Form;
use App\Models\FormField;
use Illuminate\Database\Seeder;

class FormSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Создаем формы если их нет
        $this->createCallbackForm();
        $this->createCalcForm();
        $this->createQuestionForm();
    }

    /**
     * Создает форму обратного звонка
     */
    private function createCallbackForm(): void
    {
        $form = Form::firstOrCreate(
            ['code' => 'callback'],
            [
                'title' => 'Обратный звонок',
                'is_active' => true,
                'captcha_mode' => 'recaptcha',
                'notification_email' => ['admin@example.com'],
                'notification_telegram' => ['123456789'],
            ]
        );

        // Если у формы уже есть поля, пропускаем
        if ($form->fields()->count() > 0) {
            return;
        }

        // Создаем поля формы обратного звонка
        $fields = [
            [
                'key' => 'name',
                'label' => 'Ваше имя',
                'type' => 'text',
                'mask' => '',
                'is_required' => true,
                'sort' => 0,
                'options' => [],
                'validation_rules' => 'required|min:2',
            ],
            [
                'key' => 'phone',
                'label' => 'Телефон',
                'type' => 'tel',
                'mask' => '+7 (999) 999-99-99',
                'is_required' => true,
                'sort' => 1,
                'options' => [],
                'validation_rules' => 'required|regex:/^[\+]?[0-9\s\-\(\)]+$/',
            ],
            [
                'key' => 'comment',
                'label' => 'Комментарий (необязательно)',
                'type' => 'textarea',
                'mask' => '',
                'is_required' => false,
                'sort' => 2,
                'options' => [],
                'validation_rules' => 'max:500',
            ],
            [
                'key' => 'consent_given',
                'label' => 'Согласие на обработку персональных данных',
                'type' => 'checkbox',
                'mask' => '',
                'is_required' => true,
                'sort' => 3,
                'options' => [],
                'validation_rules' => 'required|accepted',
            ],
        ];

        foreach ($fields as $fieldData) {
            FormField::create(array_merge($fieldData, ['form_id' => $form->id]));
        }
    }

    /**
     * Создает форму калькулятора
     */
    private function createCalcForm(): void
    {
        $form = Form::firstOrCreate(
            ['code' => 'calc'],
            [
                'title' => 'Калькулятор стоимости',
                'is_active' => true,
                'captcha_mode' => 'hcaptcha',
                'notification_email' => ['manager@example.com'],
                'notification_telegram' => ['987654321'],
            ]
        );

        // Если у формы уже есть поля, пропускаем
        if ($form->fields()->count() > 0) {
            return;
        }

        // Создаем поля формы калькулятора
        $fields = [
            [
                'key' => 'name',
                'label' => 'Имя',
                'type' => 'text',
                'mask' => '',
                'is_required' => true,
                'sort' => 0,
                'options' => [],
                'validation_rules' => 'required|min:2',
            ],
            [
                'key' => 'email',
                'label' => 'Email',
                'type' => 'email',
                'mask' => '',
                'is_required' => true,
                'sort' => 1,
                'options' => [],
                'validation_rules' => 'required|email',
            ],
            [
                'key' => 'phone',
                'label' => 'Телефон',
                'type' => 'tel',
                'mask' => '+7 (999) 999-99-99',
                'is_required' => true,
                'sort' => 2,
                'options' => [],
                'validation_rules' => 'required|regex:/^[\+]?[0-9\s\-\(\)]+$/',
            ],
            [
                'key' => 'service_type',
                'label' => 'Тип услуги',
                'type' => 'select',
                'mask' => '',
                'is_required' => true,
                'sort' => 3,
                'options' => ['Консультация', 'Разработка', 'Поддержка', 'Аудит'],
                'validation_rules' => 'required',
            ],
            [
                'key' => 'budget',
                'label' => 'Предполагаемый бюджет',
                'type' => 'radio',
                'mask' => '',
                'is_required' => true,
                'sort' => 4,
                'options' => ['До 50 000 руб.', '50 000 - 100 000 руб.', '100 000 - 500 000 руб.', 'Свыше 500 000 руб.'],
                'validation_rules' => 'required',
            ],
            [
                'key' => 'description',
                'label' => 'Описание проекта',
                'type' => 'textarea',
                'mask' => '',
                'is_required' => false,
                'sort' => 5,
                'options' => [],
                'validation_rules' => 'max:1000',
            ],
            [
                'key' => 'deadline',
                'label' => 'Желаемый срок выполнения',
                'type' => 'date',
                'mask' => '99.99.9999',
                'is_required' => false,
                'sort' => 6,
                'options' => [],
                'validation_rules' => 'date|after:today',
            ],
            [
                'key' => 'consent_given',
                'label' => 'Согласие на обработку персональных данных',
                'type' => 'checkbox',
                'mask' => '',
                'is_required' => true,
                'sort' => 7,
                'options' => [],
                'validation_rules' => 'required|accepted',
            ],
        ];

        foreach ($fields as $fieldData) {
            FormField::create(array_merge($fieldData, ['form_id' => $form->id]));
        }
    }

    /**
     * Создает форму вопроса специалисту
     */
    private function createQuestionForm(): void
    {
        $form = Form::firstOrCreate(
            ['code' => 'question'],
            [
                'title' => 'Вопрос специалисту',
                'is_active' => true,
                'captcha_mode' => 'none',
                'notification_email' => ['support@example.com'],
                'notification_telegram' => ['555666777'],
            ]
        );

        // Если у формы уже есть поля, пропускаем
        if ($form->fields()->count() > 0) {
            return;
        }

        // Создаем поля формы вопроса
        $fields = [
            [
                'key' => 'name',
                'label' => 'Ваше имя',
                'type' => 'text',
                'mask' => '',
                'is_required' => true,
                'sort' => 0,
                'options' => [],
                'validation_rules' => 'required|min:2',
            ],
            [
                'key' => 'email',
                'label' => 'Email для ответа',
                'type' => 'email',
                'mask' => '',
                'is_required' => true,
                'sort' => 1,
                'options' => [],
                'validation_rules' => 'required|email',
            ],
            [
                'key' => 'question_category',
                'label' => 'Категория вопроса',
                'type' => 'select',
                'mask' => '',
                'is_required' => true,
                'sort' => 2,
                'options' => ['Общие вопросы', 'Техническая поддержка', 'Цены и услуги', 'Партнерство', 'Другое'],
                'validation_rules' => 'required',
            ],
            [
                'key' => 'subject',
                'label' => 'Тема вопроса',
                'type' => 'text',
                'mask' => '',
                'is_required' => true,
                'sort' => 3,
                'options' => [],
                'validation_rules' => 'required|max:200',
            ],
            [
                'key' => 'message',
                'label' => 'Ваш вопрос',
                'type' => 'textarea',
                'mask' => '',
                'is_required' => true,
                'sort' => 4,
                'options' => [],
                'validation_rules' => 'required|min:10|max:2000',
            ],
            [
                'key' => 'attachment',
                'label' => 'Приложить файл (необязательно)',
                'type' => 'file',
                'mask' => '',
                'is_required' => false,
                'sort' => 5,
                'options' => [],
                'validation_rules' => 'file|max:5120|mimes:pdf,doc,docx,txt,jpg,jpeg,png',
            ],
            [
                'key' => 'consent_given',
                'label' => 'Согласие на обработку персональных данных',
                'type' => 'checkbox',
                'mask' => '',
                'is_required' => true,
                'sort' => 6,
                'options' => [],
                'validation_rules' => 'required|accepted',
            ],
        ];

        foreach ($fields as $fieldData) {
            FormField::create(array_merge($fieldData, ['form_id' => $form->id]));
        }
    }
}
