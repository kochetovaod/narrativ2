<div class="platform-forms">
    <div class="card">
        <div class="card-header">
            <h4 class="mb-0">
                <i class="icon-grid"></i>
                {{ __('Формы обратной связи') }}
            </h4>
        </div>
        <div class="card-body">
            <p class="text-muted">{{ __('Здесь вы можете управлять формами обратной связи и просматривать заявки от пользователей.') }}</p>
            
            <div class="form-types-info">
                <h6>{{ __('Типы форм:') }}</h6>
                <ul class="list-unstyled">
                    <li><strong>Обратный звонок</strong> - форма для заказа звонка</li>
                    <li><strong>Калькулятор</strong> - форма для расчета стоимости услуги</li>
                    <li><strong>Вопрос специалисту</strong> - форма для отправки вопроса</li>
                </ul>
            </div>
        </div>
    </div>
</div>

<style>
.platform-forms .card {
    margin-bottom: 20px;
}

.form-types-info {
    background: #f8f9fa;
    padding: 15px;
    border-radius: 8px;
    margin-top: 15px;
}

.form-types-info ul li {
    padding: 5px 0;
    border-bottom: 1px solid #e9ecef;
}

.form-types-info ul li:last-child {
    border-bottom: none;
}

.form-types-info strong {
    color: #495057;
}
</style>
