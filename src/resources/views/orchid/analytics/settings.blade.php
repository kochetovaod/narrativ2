<div class="platform-analytics">
    <div class="row">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <h4 class="mb-0">
                        <i class="icon-chart"></i>
                        {{ __('Настройки аналитики') }}
                    </h4>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-12">
                            <h6>{{ __('Яндекс.Метрика') }}</h6>
                        </div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="yandex_metrica_id">ID счетчика</label>
                                <input type="text" name="yandex_metrica_id" id="yandex_metrica_id" 
                                       class="form-control" 
                                       value="{{ $settings->yandex_metrica_id }}" 
                                       placeholder="12345678">
                                <small class="form-text text-muted">Номер вашего счетчика Яндекс.Метрики</small>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="yandex_metrica_api_key">API ключ</label>
                                <input type="text" name="yandex_metrica_api_key" id="yandex_metrica_api_key" 
                                       class="form-control" 
                                       value="{{ $settings->yandex_metrica_api_key }}" 
                                       placeholder="API ключ для доступа к данным">
                                <small class="form-text text-muted">Необходим для получения статистики</small>
                            </div>
                        </div>
                    </div>
                    <div class="row mb-4">
                        <div class="col-md-4">
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" 
                                       name="is_yandex_enabled" id="is_yandex_enabled" 
                                       value="1" {{ $settings->is_yandex_enabled ? 'checked' : '' }}>
                                <label class="form-check-label" for="is_yandex_enabled">
                                    Включить Яндекс.Метрику
                                </label>
                            </div>
                        </div>
                    </div>

                    <hr>

                    <div class="row">
                        <div class="col-12">
                            <h6>{{ __('Google Analytics') }}</h6>
                        </div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="google_analytics_id">ID отслеживания (GA4)</label>
                                <input type="text" name="google_analytics_id" id="google_analytics_id" 
                                       class="form-control" 
                                       value="{{ $settings->google_analytics_id }}" 
                                       placeholder="G-XXXXXXXXXX">
                                <small class="form-text text-muted">ID в формате G-XXXXXXXXXX</small>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="google_analytics_api_key">API ключ</label>
                                <input type="text" name="google_analytics_api_key" id="google_analytics_api_key" 
                                       class="form-control" 
                                       value="{{ $settings->google_analytics_api_key }}" 
                                       placeholder="API ключ для доступа к данным">
                                <small class="form-text text-muted">Необходим для получения статистики</small>
                            </div>
                        </div>
                    </div>
                    <div class="row mb-4">
                        <div class="col-md-4">
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" 
                                       name="is_google_enabled" id="is_google_enabled" 
                                       value="1" {{ $settings->is_google_enabled ? 'checked' : '' }}>
                                <label class="form-check-label" for="is_google_enabled">
                                    Включить Google Analytics
                                </label>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" 
                                       name="enhanced_ecommerce" id="enhanced_ecommerce" 
                                       value="1" {{ $settings->enhanced_ecommerce ? 'checked' : '' }}>
                                <label class="form-check-label" for="enhanced_ecommerce">
                                    Enhanced Ecommerce
                                </label>
                            </div>
                        </div>
                    </div>

                    <hr>

                    <div class="row">
                        <div class="col-12">
                            <h6>{{ __('Google Tag Manager') }}</h6>
                        </div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="google_tag_manager_id">ID контейнера GTM</label>
                                <input type="text" name="google_tag_manager_id" id="google_tag_manager_id" 
                                       class="form-control" 
                                       value="{{ $settings->google_tag_manager_id }}" 
                                       placeholder="GTM-XXXXXXX">
                                <small class="form-text text-muted">ID в формате GTM-XXXXXXX</small>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" 
                                           name="is_tag_manager_enabled" id="is_tag_manager_enabled" 
                                           value="1" {{ $settings->is_tag_manager_enabled ? 'checked' : '' }}>
                                    <label class="form-check-label" for="is_tag_manager_enabled">
                                        Включить Google Tag Manager
                                    </label>
                                </div>
                            </div>
                        </div>
                    </div>

                    <hr>

                    <div class="row">
                        <div class="col-12">
                            <h6>{{ __('Google Ads (рекламные кампании)') }}</h6>
                        </div>
                    </div>
                    <div class="row mb-4">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="adwords_conversion_id">ID конверсии</label>
                                <input type="text" name="adwords_conversion_id" id="adwords_conversion_id" 
                                       class="form-control" 
                                       value="{{ $settings->adwords_conversion_id }}" 
                                       placeholder="1234567890">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="adwords_conversion_label">Метка конверсии</label>
                                <input type="text" name="adwords_conversion_label" id="adwords_conversion_label" 
                                       class="form-control" 
                                       value="{{ $settings->adwords_conversion_label }}" 
                                       placeholder="XyzAbcDefGh">
                            </div>
                        </div>
                    </div>

                    <hr>

                    <div class="row">
                        <div class="col-12">
                            <h6>{{ __('Дополнительные настройки') }}</h6>
                        </div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <div class="form-group">
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" 
                                           name="custom_events_enabled" id="custom_events_enabled" 
                                           value="1" {{ $settings->custom_events_enabled ? 'checked' : '' }}>
                                    <label class="form-check-label" for="custom_events_enabled">
                                        Отслеживать пользовательские события
                                    </label>
                                </div>
                                <small class="form-text text-muted">Клики по телефону, отправка форм, переходы в соцсети</small>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="data_retention_days">Хранение данных (дни)</label>
                                <input type="number" name="data_retention_days" id="data_retention_days" 
                                       class="form-control" 
                                       value="{{ $settings->data_retention_days }}" 
                                       min="1" max="365">
                                <small class="form-text text-muted">Сколько дней хранить аналитические данные</small>
                            </div>
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-12">
                            <div class="form-group">
                                <label for="tracking_ip_exclusions">IP адреса для исключения</label>
                                <textarea name="tracking_ip_exclusions" id="tracking_ip_exclusions" 
                                          class="form-control" rows="3" 
                                          placeholder="192.168.1.1&#10;10.0.0.1">{{ is_array($settings->tracking_ip_exclusions) ? implode("\n", $settings->tracking_ip_exclusions) : '' }}</textarea>
                                <small class="form-text text-muted">Каждый IP с новой строки. Ваш текущий IP: {{ request()->ip() }}</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="icon-info"></i>
                        {{ __('Статус подключения') }}
                    </h5>
                </div>
                <div class="card-body">
                    <div class="connection-status">
                        <div class="status-item {{ $settings->isYandexEnabled() ? 'active' : 'inactive' }}">
                            <div class="status-indicator"></div>
                            <span>Яндекс.Метрика</span>
                        </div>
                        <div class="status-item {{ $settings->isGoogleEnabled() ? 'active' : 'inactive' }}">
                            <div class="status-indicator"></div>
                            <span>Google Analytics</span>
                        </div>
                        <div class="status-item {{ $settings->isTagManagerEnabled() ? 'active' : 'inactive' }}">
                            <div class="status-indicator"></div>
                            <span>Google Tag Manager</span>
                        </div>
                    </div>
                    
                    <div class="mt-4">
                        <button type="button" class="btn btn-outline-primary btn-sm" 
                                onclick="testConnections()">
                            <i class="icon-refresh"></i>
                            {{ __('Тест подключений') }}
                        </button>
                    </div>
                </div>
            </div>

            <div class="card mt-3">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="icon-docs"></i>
                        {{ __('Инструкции') }}
                    </h5>
                </div>
                <div class="card-body">
                    <div class="instruction-item">
                        <h6>{{ __('Яндекс.Метрика') }}</h6>
                        <p>1. Создайте счетчик в Яндекс.Метрике</p>
                        <p>2. Получите API ключ в настройках</p>
                        <p>3. Включите цели для отслеживания конверсий</p>
                    </div>
                    
                    <div class="instruction-item">
                        <h6>{{ __('Google Analytics') }}</h6>
                        <p>1. Создайте ресурс GA4</p>
                        <p>2. Настройте API доступ</p>
                        <p>3. Добавьте цели конверсии</p>
                    </div>
                    
                    <div class="instruction-item">
                        <h6>{{ __('Google Tag Manager') }}</h6>
                        <p>1. Создайте контейнер GTM</p>
                        <p>2. Настройте триггеры</p>
                        <p>3. Добавьте теги аналитики</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function testConnections() {
    // Здесь можно добавить AJAX запрос для тестирования
    alert('Функция тестирования будет доступна после реализации API сервисов');
}
</script>

<style>
.platform-analytics .card {
    margin-bottom: 20px;
}

.connection-status {
    display: flex;
    flex-direction: column;
    gap: 10px;
}

.status-item {
    display: flex;
    align-items: center;
    gap: 10px;
    padding: 8px;
    border-radius: 6px;
    background: #f8f9fa;
}

.status-item.active {
    background: #d4edda;
    border-left: 4px solid #28a745;
}

.status-item.inactive {
    background: #f8d7da;
    border-left: 4px solid #dc3545;
}

.status-indicator {
    width: 8px;
    height: 8px;
    border-radius: 50%;
    background: #dc3545;
}

.status-item.active .status-indicator {
    background: #28a745;
}

.instruction-item {
    margin-bottom: 15px;
    padding-bottom: 15px;
    border-bottom: 1px solid #e9ecef;
}

.instruction-item:last-child {
    border-bottom: none;
    margin-bottom: 0;
    padding-bottom: 0;
}

.instruction-item h6 {
    color: #495057;
    margin-bottom: 8px;
}

.instruction-item p {
    margin-bottom: 5px;
    font-size: 0.9rem;
    color: #6c757d;
}

.form-check.form-switch .form-check-input {
    width: 3rem;
    height: 1.5rem;
}
</style>
