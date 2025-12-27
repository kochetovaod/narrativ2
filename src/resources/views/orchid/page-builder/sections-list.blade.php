<div class="row mt-3">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header">
                <h5>{{ __('Секции страницы') }}</h5>
            </div>
            <div class="card-body">
                @if(empty($sections) || count($sections) === 0)
                    <div class="text-center text-muted py-4">
                        <i class="icon-layers" style="font-size: 48px;"></i>
                        <p class="mt-2">{{ __('Секции не добавлены') }}</p>
                        <small>{{ __('Добавьте первую секцию с помощью формы выше') }}</small>
                    </div>
                @else
                    <div id="sections-container" data-sections='@json($sections)'>
                        <!-- Секции будут загружены через JavaScript -->
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>

<!-- Шаблон для добавления секции -->
<div id="section-template" style="display: none;">
    <div class="section-item card mb-3" data-section-id="">
        <div class="card-header d-flex justify-content-between align-items-center">
            <div class="section-header">
                <h6 class="mb-0 section-title">{{ __('Новая секция') }}</h6>
                <small class="text-muted section-type">{{ __('Тип:') }}</small>
            </div>
            <div class="section-actions">
                <button type="button" class="btn btn-sm btn-outline-primary move-up">
                    <i class="icon-arrow-up"></i>
                </button>
                <button type="button" class="btn btn-sm btn-outline-primary move-down">
                    <i class="icon-arrow-down"></i>
                </button>
                <button type="button" class="btn btn-sm btn-outline-danger remove-section">
                    <i class="icon-trash"></i>
                </button>
            </div>
        </div>
        <div class="card-body section-content">
            <!-- Контент секции будет загружен динамически -->
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Инициализация page builder
    const sectionsContainer = document.getElementById('sections-container');
    const sectionsData = sectionsContainer ? JSON.parse(sectionsContainer.dataset.sections || '[]') : [];
    
    if (sectionsData.length === 0) {
        // Если секций нет, создаем пустой интерфейс
        createEmptyState();
        return;
    }
    
    // Загружаем существующие секции
    sectionsData.forEach((section, index) => {
        addSectionToUI(section, index);
    });
    
    // Инициализируем обработчики событий
    initEventHandlers();
    
    // Инициализируем drag & drop
    initDragAndDrop();
});

function createEmptyState() {
    const container = document.getElementById('sections-container');
    if (!container) return;
    
    container.innerHTML = `
        <div class="text-center text-muted py-4">
            <i class="icon-layers" style="font-size: 48px;"></i>
            <p class="mt-2">{{ __('Секции не добавлены') }}</p>
            <small>{{ __('Добавьте первую секцию с помощью формы выше') }}</small>
        </div>
    `;
}

function addSectionToUI(section, index) {
    const template = document.getElementById('section-template');
    const container = document.getElementById('sections-container');
    
    if (!template || !container) return;
    
    const clone = template.cloneNode(true);
    const sectionElement = clone.querySelector('.section-item');
    const titleElement = clone.querySelector('.section-title');
    const typeElement = clone.querySelector('.section-type');
    const contentElement = clone.querySelector('.section-content');
    
    // Устанавливаем данные секции
    const sectionId = section.id || 'new-' + Date.now();
    sectionElement.dataset.sectionId = sectionId;
    sectionElement.dataset.sectionType = section.type;
    sectionElement.dataset.sectionOrder = index;
    
    // Обновляем заголовок и тип
    titleElement.textContent = getSectionTitle(section.type, section.settings?.title);
    typeElement.textContent = '{{ __('Тип:') }} ' + getSectionTypeLabel(section.type);
    
    // Добавляем скрытые поля для идентификации секции
    const hiddenFields = `
        <input type="hidden" name="sections[${index}][id]" value="${sectionId}">
        <input type="hidden" name="sections[${index}][type]" value="${section.type}">
        <input type="hidden" name="sections[${index}][order]" value="${index}">
    `;
    
    // Добавляем контент секции
    contentElement.innerHTML = hiddenFields + generateSectionContent(section);
    
    // Удаляем ID шаблона
    clone.id = '';
    clone.style.display = '';
    
    container.appendChild(clone);
}

function getSectionTitle(type, customTitle = '') {
    const titles = {
        'hero': '{{ __('Hero секция') }}',
        'text': customTitle || '{{ __('Текстовая секция') }}',
        'categories_grid': '{{ __('Сетка категорий') }}',
        'services_list': '{{ __('Список услуг') }}',
        'portfolio': '{{ __('Портфолио') }}',
        'cta_form': '{{ __('CTA секция') }}',
        'contacts': '{{ __('Контакты') }}',
        'gallery': '{{ __('Галерея') }}',
        'advantages': '{{ __('Преимущества') }}',
        'global_block': '{{ __('Глобальный блок') }}'
    };
    
    return titles[type] || '{{ __('Неизвестная секция') }}';
}

function getSectionTypeLabel(type) {
    const labels = {
        'hero': '{{ __('Hero') }}',
        'text': '{{ __('Текст') }}',
        'categories_grid': '{{ __('Категории') }}',
        'services_list': '{{ __('Услуги') }}',
        'portfolio': '{{ __('Портфолио') }}',
        'cta_form': '{{ __('CTA') }}',
        'contacts': '{{ __('Контакты') }}',
        'gallery': '{{ __('Галерея') }}',
        'advantages': '{{ __('Преимущества') }}',
        'global_block': '{{ __('Глобальный блок') }}'
    };
    
    return labels[type] || '{{ __('Неизвестный') }}';
}

function generateSectionContent(section) {
    const type = section.type;
    const settings = section.settings || {};
    
    switch (type) {
        case 'hero':
            return generateHeroContent(settings);
        case 'text':
            return generateTextContent(settings);
        case 'categories_grid':
            return generateCategoriesGridContent(settings);
        case 'services_list':
            return generateServicesListContent(settings);
        case 'portfolio':
            return generatePortfolioContent(settings);
        case 'cta_form':
            return generateCTAFormContent(settings);
        case 'contacts':
            return generateContactsContent(settings);
        case 'gallery':
            return generateGalleryContent(settings);
        case 'advantages':
            return generateAdvantagesContent(settings);
        case 'global_block':
            return generateGlobalBlockContent(settings);
        default:
            return '<div class="alert alert-warning">{{ __('Неизвестный тип секции') }}</div>';
    }
}

function generateHeroContent(settings) {
    return `
        <div class="row">
            <div class="col-md-6">
                <div class="form-group">
                    <label>{{ __('Заголовок') }}</label>
                    <input type="text" class="form-control" name="sections[${getCurrentSectionIndex()}][settings][title]" 
                           value="${settings.title || ''}" placeholder="{{ __('Главный заголовок') }}">
                </div>
                <div class="form-group">
                    <label>{{ __('Подзаголовок') }}</label>
                    <textarea class="form-control" name="sections[${getCurrentSectionIndex()}][settings][subtitle]" 
                              rows="2" placeholder="{{ __('Описание под заголовком') }}">${settings.subtitle || ''}</textarea>
                </div>
                <div class="form-group">
                    <label>{{ __('Кнопки CTA') }}</label>
                    <div class="cta-buttons">
                        ${generateCTAButtons(settings.cta_buttons || [])}
                    </div>
                    <button type="button" class="btn btn-sm btn-outline-primary add-cta-button">
                        {{ __('Добавить кнопку') }}
                    </button>
                </div>
            </div>
            <div class="col-md-6">
                <div class="form-group">
                    <label>{{ __('Фоновое изображение') }}</label>
                    <input type="text" class="form-control" name="sections[${getCurrentSectionIndex()}][settings][background_image]" 
                           value="${settings.background_image || ''}" placeholder="{{ __('ID изображения') }}">
                </div>
            </div>
        </div>
    `;
}

function generateTextContent(settings) {
    return `
        <div class="form-group">
            <label>{{ __('Заголовок секции') }}</label>
            <input type="text" class="form-control" name="sections[${getCurrentSectionIndex()}][settings][title]" 
                   value="${settings.title || ''}" placeholder="{{ __('Заголовок') }}">
        </div>
        <div class="form-group">
            <label>{{ __('Содержимое') }}</label>
            <textarea class="form-control" name="sections[${getCurrentSectionIndex()}][settings][content]" 
                      rows="6" placeholder="{{ __('Текст секции') }}">${settings.content || ''}</textarea>
        </div>
        <div class="form-group">
            <label>{{ __('Выравнивание') }}</label>
            <select class="form-control" name="sections[${getCurrentSectionIndex()}][settings][alignment]">
                <option value="left" ${settings.alignment === 'left' ? 'selected' : ''}>{{ __('Слева') }}</option>
                <option value="center" ${settings.alignment === 'center' ? 'selected' : ''}>{{ __('По центру') }}</option>
                <option value="right" ${settings.alignment === 'right' ? 'selected' : ''}>{{ __('Справа') }}</option>
            </select>
        </div>
    `;
}

function generateCategoriesGridContent(settings) {
    return `
        <div class="form-group">
            <label>{{ __('Заголовок секции') }}</label>
            <input type="text" class="form-control" name="sections[${getCurrentSectionIndex()}][settings][title]" 
                   value="${settings.title || ''}" placeholder="{{ __('Например: Наша продукция') }}">
        </div>
        <div class="form-group">
            <label>{{ __('Количество колонок') }}</label>
            <select class="form-control" name="sections[${getCurrentSectionIndex()}][settings][columns_count]">
                <option value="2" ${settings.columns_count == 2 ? 'selected' : ''}>2</option>
                <option value="3" ${settings.columns_count == 3 ? 'selected' : ''}>3</option>
                <option value="4" ${settings.columns_count == 4 ? 'selected' : ''}>4</option>
            </select>
        </div>
        <div class="form-group">
            <label>{{ __('Показать количество товаров') }}</label>
            <div class="form-check">
                <input type="checkbox" class="form-check-input" name="sections[${getCurrentSectionIndex()}][settings][show_count]" 
                       value="1" ${settings.show_count ? 'checked' : ''}>
                <label class="form-check-label">{{ __('Отображать количество товаров в категории') }}</label>
            </div>
        </div>
        <div class="form-group">
            <label>{{ __('Описание') }}</label>
            <textarea class="form-control" name="sections[${getCurrentSectionIndex()}][settings][description]" 
                      rows="3" placeholder="{{ __('Описание секции') }}">${settings.description || ''}</textarea>
        </div>
    `;
}

function generateServicesListContent(settings) {
    return `
        <div class="form-group">
            <label>{{ __('Заголовок секции') }}</label>
            <input type="text" class="form-control" name="sections[${getCurrentSectionIndex()}][settings][title]" 
                   value="${settings.title || ''}" placeholder="{{ __('Например: Наши услуги') }}">
        </div>
        <div class="form-group">
            <label>{{ __('Макет') }}</label>
            <select class="form-control" name="sections[${getCurrentSectionIndex()}][settings][layout_type]">
                <option value="grid" ${settings.layout_type === 'grid' ? 'selected' : ''}>{{ __('Сетка') }}</option>
                <option value="list" ${settings.layout_type === 'list' ? 'selected' : ''}>{{ __('Список') }}</option>
            </select>
        </div>
        <div class="form-group">
            <label>{{ __('Описание') }}</label>
            <textarea class="form-control" name="sections[${getCurrentSectionIndex()}][settings][description]" 
                      rows="3" placeholder="{{ __('Описание секции') }}">${settings.description || ''}</textarea>
        </div>
    `;
}

function generatePortfolioContent(settings) {
    return `
        <div class="form-group">
            <label>{{ __('Заголовок секции') }}</label>
            <input type="text" class="form-control" name="sections[${getCurrentSectionIndex()}][settings][title]" 
                   value="${settings.title || ''}" placeholder="{{ __('Например: Наши работы') }}">
        </div>
        <div class="form-group">
            <label>{{ __('Лимит кейсов') }}</label>
            <input type="number" class="form-control" name="sections[${getCurrentSectionIndex()}][settings][limit]" 
                   value="${settings.limit || 6}" min="1" max="50">
        </div>
        <div class="form-group">
            <label>{{ __('Показывать фильтры') }}</label>
            <div class="form-check">
                <input type="checkbox" class="form-check-input" name="sections[${getCurrentSectionIndex()}][settings][show_filters]" 
                       value="1" ${settings.show_filters ? 'checked' : ''}>
                <label class="form-check-label">{{ __('Отображать фильтры по товарам/услугам') }}</label>
            </div>
        </div>
        <div class="form-group">
            <label>{{ __('Описание') }}</label>
            <textarea class="form-control" name="sections[${getCurrentSectionIndex()}][settings][description]" 
                      rows="3" placeholder="{{ __('Описание секции') }}">${settings.description || ''}</textarea>
        </div>
    `;
}

function generateCTAFormContent(settings) {
    return `
        <div class="form-group">
            <label>{{ __('Заголовок') }}</label>
            <input type="text" class="form-control" name="sections[${getCurrentSectionIndex()}][settings][title]" 
                   value="${settings.title || ''}" placeholder="{{ __('Получить консультацию') }}">
        </div>
        <div class="form-group">
            <label>{{ __('Описание') }}</label>
            <textarea class="form-control" name="sections[${getCurrentSectionIndex()}][settings][description]" 
                      rows="3" placeholder="{{ __('Оставьте заявку и мы свяжемся с вами') }}">${settings.description || ''}</textarea>
        </div>
        <div class="form-group">
            <label>{{ __('Тип формы') }}</label>
            <select class="form-control" name="sections[${getCurrentSectionIndex()}][settings][form_type]">
                <option value="call" ${settings.form_type === 'call' ? 'selected' : ''}>{{ __('Заказать звонок') }}</option>
                <option value="calculation" ${settings.form_type === 'calculation' ? 'selected' : ''}>{{ __('Получить расчет') }}</option>
                <option value="question" ${settings.form_type === 'question' ? 'selected' : ''}>{{ __('Задать вопрос') }}</option>
            </select>
        </div>
    `;
}

function generateContactsContent(settings) {
    return `
        <div class="form-group">
            <label>{{ __('Заголовок') }}</label>
            <input type="text" class="form-control" name="sections[${getCurrentSectionIndex()}][settings][title]" 
                   value="${settings.title || ''}" placeholder="{{ __('Свяжитесь с нами') }}">
        </div>
        <div class="form-group">
            <label>{{ __('Тип контактов') }}</label>
            <select class="form-control" name="sections[${getCurrentSectionIndex()}][settings][contact_type]">
                <option value="embedded" ${settings.contact_type === 'embedded' ? 'selected' : ''}>{{ __('Встроенные контакты') }}</option>
                <option value="global_block" ${settings.contact_type === 'global_block' ? 'selected' : ''}>{{ __('Глобальный блок') }}</option>
            </select>
        </div>
        <div class="row">
            <div class="col-md-6">
                <div class="form-group">
                    <label>{{ __('Телефон') }}</label>
                    <input type="text" class="form-control" name="sections[${getCurrentSectionIndex()}][settings][phone]" 
                           value="${settings.phone || ''}" placeholder="+7 (000) 000-00-00">
                </div>
            </div>
            <div class="col-md-6">
                <div class="form-group">
                    <label>{{ __('Email') }}</label>
                    <input type="text" class="form-control" name="sections[${getCurrentSectionIndex()}][settings][email]" 
                           value="${settings.email || ''}" placeholder="info@example.com">
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-md-6">
                <div class="form-group">
                    <label>{{ __('Адрес') }}</label>
                    <input type="text" class="form-control" name="sections[${getCurrentSectionIndex()}][settings][address]" 
                           value="${settings.address || ''}" placeholder="{{ __('г. Москва, ул. Примерная, д. 1') }}">
                </div>
            </div>
            <div class="col-md-6">
                <div class="form-group">
                    <label>{{ __('Часы работы') }}</label>
                    <input type="text" class="form-control" name="sections[${getCurrentSectionIndex()}][settings][work_hours]" 
                           value="${settings.work_hours || ''}" placeholder="{{ __('Пн-Пт: 09:00-18:00') }}">
                </div>
            </div>
        </div>
        <div class="form-group">
            <label>{{ __('Карта (iframe или embed)') }}</label>
            <textarea class="form-control" rows="3" name="sections[${getCurrentSectionIndex()}][settings][map_embed]" placeholder="{{ __('Вставьте код карты') }}">${settings.map_embed || ''}</textarea>
        </div>
        <div class="form-group">
            <label>{{ __('CTA блок') }}</label>
            <input type="text" class="form-control mb-2" name="sections[${getCurrentSectionIndex()}][settings][cta_title]" 
                   value="${settings.cta_title || ''}" placeholder="{{ __('Заголовок CTA') }}">
            <textarea class="form-control mb-2" rows="2" name="sections[${getCurrentSectionIndex()}][settings][cta_text]" placeholder="{{ __('Описание CTA') }}">${settings.cta_text || ''}</textarea>
            <div class="row">
                <div class="col-md-6">
                    <input type="text" class="form-control mb-2" name="sections[${getCurrentSectionIndex()}][settings][cta_button_text]" 
                           value="${settings.cta_button_text || ''}" placeholder="{{ __('Текст кнопки') }}">
                    <input type="text" class="form-control" name="sections[${getCurrentSectionIndex()}][settings][cta_button_link]" 
                           value="${settings.cta_button_link || ''}" placeholder="{{ __('Ссылка кнопки') }}">
                </div>
                <div class="col-md-6">
                    <input type="text" class="form-control mb-2" name="sections[${getCurrentSectionIndex()}][settings][cta_secondary_text]" 
                           value="${settings.cta_secondary_text || ''}" placeholder="{{ __('Вторичная кнопка (текст)') }}">
                    <input type="text" class="form-control" name="sections[${getCurrentSectionIndex()}][settings][cta_secondary_link]" 
                           value="${settings.cta_secondary_link || ''}" placeholder="{{ __('Ссылка вторичной кнопки') }}">
                </div>
            </div>
        </div>
    `;
}

function generateGalleryContent(settings) {
    return `
        <div class="form-group">
            <label>{{ __('Заголовок секции') }}</label>
            <input type="text" class="form-control" name="sections[${getCurrentSectionIndex()}][settings][title]" 
                   value="${settings.title || ''}" placeholder="{{ __('Например: Галерея работ') }}">
        </div>
        <div class="form-group">
            <label>{{ __('Количество колонок') }}</label>
            <select class="form-control" name="sections[${getCurrentSectionIndex()}][settings][columns_count]">
                <option value="2" ${settings.columns_count == 2 ? 'selected' : ''}>2</option>
                <option value="3" ${settings.columns_count == 3 ? 'selected' : ''}>3</option>
                <option value="4" ${settings.columns_count == 4 ? 'selected' : ''}>4</option>
            </select>
        </div>
        <div class="form-group">
            <label>{{ __('Лайтбокс') }}</label>
            <div class="form-check">
                <input type="checkbox" class="form-check-input" name="sections[${getCurrentSectionIndex()}][settings][lightbox]" 
                       value="1" ${settings.lightbox ? 'checked' : ''}>
                <label class="form-check-label">{{ __('Включить лайтбокс для просмотра изображений') }}</label>
            </div>
        </div>
    `;
}

function generateAdvantagesContent(settings) {
    return `
        <div class="form-group">
            <label>{{ __('Заголовок секции') }}</label>
            <input type="text" class="form-control" name="sections[${getCurrentSectionIndex()}][settings][title]" 
                   value="${settings.title || ''}" placeholder="{{ __('Наши преимущества') }}">
        </div>
        <div class="form-group">
            <label>{{ __('Преимущества') }}</label>
            <div class="advantages-list">
                ${generateAdvantagesList(settings.advantages || [])}
            </div>
            <button type="button" class="btn btn-sm btn-outline-primary add-advantage">
                {{ __('Добавить преимущество') }}
            </button>
        </div>
    `;
}

function generateGlobalBlockContent(settings) {
    const selectedCode = settings.block_code || '';
    let options = '<option value="">{{ __('Выберите блок') }}</option>';
    
    // Список глобальных блоков (должен быть передан из PHP)
    const globalBlocks = @json($globalBlocks ?? []);
    
    globalBlocks.forEach(block => {
        const selected = selectedCode === block.code ? 'selected' : '';
        options += `<option value="${block.code}" ${selected}>${block.title} (${block.code})</option>`;
    });
    
    return `
        <div class="form-group">
            <label>{{ __('Глобальный блок') }}</label>
            <select class="form-control" name="sections[${getCurrentSectionIndex()}][settings][block_code]">
                ${options}
            </select>
        </div>
    `;
}

function generateCTAButtons(buttons) {
    let html = '';
    buttons.forEach((button, index) => {
        html += `
            <div class="input-group mb-2">
                <input type="text" class="form-control" name="sections[${getCurrentSectionIndex()}][settings][cta_buttons][${index}][text]" 
                       value="${button.text || ''}" placeholder="{{ __('Текст кнопки') }}">
                <input type="text" class="form-control" name="sections[${getCurrentSectionIndex()}][settings][cta_buttons][${index}][link]" 
                       value="${button.link || ''}" placeholder="{{ __('Ссылка') }}">
                <div class="input-group-append">
                    <button type="button" class="btn btn-outline-danger remove-cta-button">
                        <i class="icon-trash"></i>
                    </button>
                </div>
            </div>
        `;
    });
    return html;
}

function generateAdvantagesList(advantages) {
    let html = '';
    advantages.forEach((advantage, index) => {
        html += `
            <div class="input-group mb-2">
                <input type="text" class="form-control" name="sections[${getCurrentSectionIndex()}][settings][advantages][${index}][title]" 
                       value="${advantage.title || ''}" placeholder="{{ __('Название преимущества') }}">
                <input type="text" class="form-control" name="sections[${getCurrentSectionIndex()}][settings][advantages][${index}][description]" 
                       value="${advantage.description || ''}" placeholder="{{ __('Описание') }}">
                <div class="input-group-append">
                    <button type="button" class="btn btn-outline-danger remove-advantage-item">
                        <i class="icon-trash"></i>
                    </button>
                </div>
            </div>
        `;
    });
    return html;
}

function getCurrentSectionIndex() {
    const sections = document.querySelectorAll('.section-item');
    return sections.length - 1;
}

function initEventHandlers() {
    const container = document.getElementById('sections-container');
    if (!container) return;
    
    // Обработчики для кнопок управления секциями
    container.addEventListener('click', function(e) {
        const sectionItem = e.target.closest('.section-item');
        if (!sectionItem) return;
        
        const sectionId = sectionItem.dataset.sectionId;
        
        if (e.target.closest('.move-up')) {
            e.preventDefault();
            moveSectionUp(sectionItem);
        } else if (e.target.closest('.move-down')) {
            e.preventDefault();
            moveSectionDown(sectionItem);
        } else if (e.target.closest('.remove-section')) {
            e.preventDefault();
            removeSection(sectionItem, sectionId);
        }
    });
    
    // Обработчики для добавления элементов в секции
    container.addEventListener('click', function(e) {
        if (e.target.closest('.add-cta-button')) {
            e.preventDefault();
            addCTAButton(e.target.closest('.section-item'));
        } else if (e.target.closest('.remove-cta-button')) {
            e.preventDefault();
            removeCTAButton(e.target.closest('.input-group'));
        } else if (e.target.closest('.add-advantage')) {
            e.preventDefault();
            addAdvantage(e.target.closest('.section-item'));
        } else if (e.target.closest('.remove-advantage-item')) {
            e.preventDefault();
            removeAdvantageItem(e.target.closest('.input-group'));
        }
    });
}

function initDragAndDrop() {
    // Инициализация drag & drop для переупорядочивания секций
    const container = document.getElementById('sections-container');
    if (!container) return;
    
    // Простая инициализация - можно расширить с помощью Sortable.js
    // Пока используем только кнопки вверх/вниз
}

function moveSectionUp(sectionElement) {
    const previousElement = sectionElement.previousElementSibling;
    if (previousElement) {
        sectionElement.parentNode.insertBefore(sectionElement, previousElement);
        updateSectionOrder();
    }
}

function moveSectionDown(sectionElement) {
    const nextElement = sectionElement.nextElementSibling;
    if (nextElement) {
        sectionElement.parentNode.insertBefore(nextElement, sectionElement);
        updateSectionOrder();
    }
}

function removeSection(sectionElement, sectionId) {
    if (confirm('{{ __('Удалить секцию?') }}')) {
        // Добавляем скрытое поле для удаления секции
        const deleteField = document.createElement('input');
        deleteField.type = 'hidden';
        deleteField.name = 'delete_sections[]';
        deleteField.value = sectionId;
        sectionElement.appendChild(deleteField);
        
        // Скрываем элемент вместо удаления
        sectionElement.style.display = 'none';
        
        updateSectionOrder();
        
        // Если секций не осталось, показываем пустое состояние
        const visibleSections = document.querySelectorAll('.section-item:not([style*="display: none"])');
        if (visibleSections.length === 0) {
            createEmptyState();
        }
    }
}

function addCTAButton(sectionElement) {
    const container = sectionElement.querySelector('.cta-buttons');
    const index = container.querySelectorAll('.input-group').length;
    
    const html = `
        <div class="input-group mb-2">
            <input type="text" class="form-control" name="sections[${getSectionIndex(sectionElement)}][settings][cta_buttons][${index}][text]" 
                   value="" placeholder="{{ __('Текст кнопки') }}">
            <input type="text" class="form-control" name="sections[${getSectionIndex(sectionElement)}][settings][cta_buttons][${index}][link]" 
                   value="" placeholder="{{ __('Ссылка') }}">
            <div class="input-group-append">
                <button type="button" class="btn btn-outline-danger remove-cta-button">
                    <i class="icon-trash"></i>
                </button>
            </div>
        </div>
    `;
    
    container.insertAdjacentHTML('beforeend', html);
}

function removeCTAButton(buttonGroup) {
    buttonGroup.remove();
}

function addAdvantage(sectionElement) {
    const container = sectionElement.querySelector('.advantages-list');
    const index = container.querySelectorAll('.input-group').length;
    
    const html = `
        <div class="input-group mb-2">
            <input type="text" class="form-control" name="sections[${getSectionIndex(sectionElement)}][settings][advantages][${index}][title]" 
                   value="" placeholder="{{ __('Название преимущества') }}">
            <input type="text" class="form-control" name="sections[${getSectionIndex(sectionElement)}][settings][advantages][${index}][description]" 
                   value="" placeholder="{{ __('Описание') }}">
            <div class="input-group-append">
                <button type="button" class="btn btn-outline-danger remove-advantage-item">
                    <i class="icon-trash"></i>
                </button>
            </div>
        </div>
    `;
    
    container.insertAdjacentHTML('beforeend', html);
}

function removeAdvantageItem(advantageGroup) {
    advantageGroup.remove();
}

function getSectionIndex(sectionElement) {
    return Array.from(document.querySelectorAll('.section-item')).indexOf(sectionElement);
}

function updateSectionOrder() {
    const sections = document.querySelectorAll('.section-item');
    sections.forEach((section, index) => {
        section.dataset.sectionOrder = index;
    });
}
</script>

<style>
.section-item {
    border: 1px solid #e9ecef;
    border-radius: 0.375rem;
    margin-bottom: 1rem;
}

.section-item .card-header {
    background-color: #f8f9fa;
    border-bottom: 1px solid #e9ecef;
}

.section-actions .btn {
    margin-left: 0.25rem;
}

.cta-buttons .input-group,
.advantages-list .input-group {
    margin-bottom: 0.5rem;
}

#sections-container:empty::after {
    content: "Секции не добавлены";
    display: block;
    text-align: center;
    color: #6c757d;
    font-style: italic;
    padding: 2rem;
}
</style>
