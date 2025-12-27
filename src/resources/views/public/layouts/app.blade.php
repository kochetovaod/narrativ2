<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', config('app.name', 'Narrativ'))</title>
    @hasSection('meta_description')
        <meta name="description" content="@yield('meta_description')">
    @endif
    <style>
        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            color: #0f172a;
            background: #f8fafc;
            line-height: 1.6;
        }

        a {
            color: inherit;
            text-decoration: none;
        }

        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 1.25rem;
        }

        .site-header {
            background: #0f172a;
            color: #fff;
            padding: 1rem 0;
            position: sticky;
            top: 0;
            z-index: 20;
        }

        .nav-container {
            display: flex;
            align-items: center;
            gap: 1rem;
        }

        .logo {
            font-weight: 700;
            letter-spacing: 0.03em;
        }

        nav ul {
            list-style: none;
            display: flex;
            gap: 1rem;
            margin: 0;
            padding: 0;
            flex-wrap: wrap;
        }

        nav a {
            color: #e2e8f0;
            font-weight: 600;
        }

        nav li {
            position: relative;
        }

        nav .submenu {
            position: absolute;
            display: none;
            flex-direction: column;
            gap: 0.35rem;
            background: #0b1220;
            padding: 0.75rem;
            border-radius: 0.5rem;
            top: 120%;
            left: 0;
            min-width: 200px;
            box-shadow: 0 10px 30px rgba(15, 23, 42, 0.35);
        }

        nav li:hover .submenu {
            display: flex;
        }

        nav .submenu a {
            color: #e2e8f0;
            font-weight: 500;
            display: block;
        }

        .search {
            margin-left: auto;
            position: relative;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .search input {
            padding: 0.55rem 0.75rem;
            border-radius: 0.5rem;
            border: 1px solid #334155;
            background: #0b1220;
            color: #e2e8f0;
            min-width: 220px;
        }

        .search button {
            padding: 0.55rem 0.9rem;
            border: none;
            border-radius: 0.5rem;
            background: #38bdf8;
            color: #0f172a;
            font-weight: 700;
            cursor: pointer;
        }

        main {
            padding: 2rem 0 3rem;
        }

        .section {
            margin-bottom: 2rem;
        }

        .section h1, .section h2 {
            margin-top: 0;
            color: #0f172a;
        }

        .grid {
            display: grid;
            gap: 1.25rem;
        }

        .grid.columns-3 {
            grid-template-columns: repeat(auto-fit, minmax(260px, 1fr));
        }

        .grid.columns-2 {
            grid-template-columns: repeat(auto-fit, minmax(320px, 1fr));
        }

        .card {
            background: #fff;
            border: 1px solid #e2e8f0;
            border-radius: 0.75rem;
            padding: 1.25rem;
            box-shadow: 0 10px 30px rgba(15, 23, 42, 0.06);
            transition: transform 0.2s ease, box-shadow 0.2s ease;
        }

        .card:hover {
            transform: translateY(-2px);
            box-shadow: 0 20px 45px rgba(15, 23, 42, 0.12);
        }

        .breadcrumbs {
            display: flex;
            flex-wrap: wrap;
            gap: 0.35rem;
            margin-bottom: 1.5rem;
            font-size: 0.95rem;
            color: #475569;
        }

        .breadcrumbs a {
            color: #0ea5e9;
        }

        .tag {
            display: inline-block;
            padding: 0.25rem 0.6rem;
            border-radius: 999px;
            background: #e0f2fe;
            color: #0ea5e9;
            font-weight: 600;
            font-size: 0.85rem;
        }

        .btn {
            display: inline-block;
            padding: 0.75rem 1rem;
            border-radius: 0.75rem;
            background: #0ea5e9;
            color: #0f172a;
            font-weight: 700;
            text-align: center;
            border: none;
            cursor: pointer;
        }

        .btn.secondary {
            background: #e2e8f0;
            color: #0f172a;
        }

        .meta {
            color: #64748b;
            font-size: 0.95rem;
        }

        mark {
            background: #fff1a9;
            padding: 0 0.1rem;
        }

        .site-footer {
            background: #0b1220;
            color: #cbd5e1;
            padding: 2rem 0;
        }

        .site-footer .footer-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
            gap: 1rem;
        }

        .site-footer a {
            color: #38bdf8;
        }

        .search-suggestions {
            position: absolute;
            top: 110%;
            right: 0;
            left: 0;
            background: #fff;
            border: 1px solid #e2e8f0;
            border-radius: 0.75rem;
            box-shadow: 0 20px 45px rgba(15, 23, 42, 0.1);
            padding: 0.5rem;
            display: none;
            max-height: 260px;
            overflow: auto;
            min-width: 260px;
        }

        .search-suggestions.open {
            display: block;
        }

        .search-suggestions a {
            display: block;
            padding: 0.5rem 0.75rem;
            border-radius: 0.5rem;
            color: #0f172a;
            text-decoration: none;
        }

        .search-suggestions a:hover {
            background: #f8fafc;
        }

        .search-suggestions__title {
            font-weight: 700;
            display: block;
        }

        .search-suggestions__snippet {
            display: block;
            color: #475569;
            font-size: 0.9rem;
            margin-top: 0.1rem;
        }

        .search-suggestions__meta {
            display: block;
            color: #94a3b8;
            font-size: 0.85rem;
            margin-top: 0.1rem;
        }

        .search-suggestions__empty {
            padding: 0.75rem 0.9rem;
            color: #475569;
        }

        .section.hero {
            background: linear-gradient(135deg, #0ea5e9 0%, #0284c7 100%);
            color: #fff;
            padding: 2.5rem;
            border-radius: 1rem;
            box-shadow: 0 20px 45px rgba(14, 165, 233, 0.25);
            text-align: center;
        }

        .section.hero h1 {
            margin-bottom: 0.5rem;
            font-size: 2rem;
        }

        .section.hero p {
            color: #e0f2fe;
            margin-bottom: 1rem;
            font-size: 1.05rem;
        }

        .section.text .content {
            color: #334155;
            line-height: 1.7;
        }

        .list-inline {
            display: flex;
            flex-wrap: wrap;
            gap: 0.5rem;
        }

        .gallery-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
            gap: 0.75rem;
        }

        .gallery-item {
            background: #e2e8f0;
            border-radius: 0.75rem;
            aspect-ratio: 1;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #475569;
        }

        .pagination {
            display: flex;
            gap: 0.5rem;
            flex-wrap: wrap;
            margin-top: 1rem;
        }

        .pagination a, .pagination span {
            padding: 0.5rem 0.75rem;
            border-radius: 0.5rem;
            border: 1px solid #e2e8f0;
            background: #fff;
        }

        .pagination .active {
            background: #0ea5e9;
            color: #0f172a;
            border-color: #0ea5e9;
        }

        .map-embed iframe {
            width: 100%;
            min-height: 280px;
            border: 0;
            border-radius: 0.75rem;
            overflow: hidden;
        }

        .form-card {
            background: linear-gradient(180deg, #ffffff 0%, #f8fafc 100%);
        }

        .dynamic-form {
            display: grid;
            gap: 1rem;
        }

        .dynamic-form__field {
            display: grid;
            gap: 0.35rem;
        }

        .dynamic-form__label {
            font-weight: 600;
            color: #0f172a;
        }

        .dynamic-form__control {
            width: 100%;
            padding: 0.75rem;
            border-radius: 0.65rem;
            border: 1px solid #e2e8f0;
            background: #fff;
            font-size: 1rem;
            transition: border-color 0.15s ease, box-shadow 0.15s ease;
        }

        .dynamic-form__control:focus {
            outline: none;
            border-color: #0ea5e9;
            box-shadow: 0 0 0 3px rgba(14, 165, 233, 0.15);
        }

        .dynamic-form__consent {
            display: flex;
            gap: 0.5rem;
            align-items: flex-start;
            font-size: 0.95rem;
            color: #334155;
            background: #f8fafc;
            padding: 0.75rem;
            border-radius: 0.65rem;
            border: 1px solid #e2e8f0;
        }

        .dynamic-form__options {
            display: grid;
            gap: 0.5rem;
        }

        .dynamic-form__option {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            color: #334155;
        }

        .dynamic-form__error {
            color: #b91c1c;
            font-size: 0.9rem;
        }

        .dynamic-form__actions {
            display: flex;
            justify-content: flex-start;
            align-items: center;
            gap: 0.75rem;
        }

        .form-status {
            padding: 0.85rem 1rem;
            border-radius: 0.65rem;
            border: 1px solid #e2e8f0;
            background: #f8fafc;
            color: #0f172a;
            font-size: 0.95rem;
        }

        .form-status.is-loading {
            border-color: #cbd5e1;
            color: #334155;
        }

        .form-status.is-error {
            border-color: #fecdd3;
            background: #fef2f2;
            color: #b91c1c;
        }

        .form-status.is-success {
            border-color: #bbf7d0;
            background: #ecfdf3;
            color: #166534;
        }

        .form-status.is-hidden {
            display: none;
        }

        .btn[disabled] {
            opacity: 0.7;
            cursor: not-allowed;
        }

        @media (max-width: 900px) {
            .nav-container {
                flex-wrap: wrap;
            }

            .search {
                width: 100%;
                margin-left: 0;
            }

            .search input {
                width: 100%;
            }
        }
    </style>
</head>
<body>
@include('components.menu.header')

<main>
    <div class="container">
        @isset($breadcrumbs)
            @include('public.partials.breadcrumbs', ['breadcrumbs' => $breadcrumbs])
        @endisset

        @yield('content')
    </div>
</main>

@include('components.menu.footer')

<script>
    (function () {
        const input = document.getElementById('search-input');
        const suggestions = document.getElementById('search-suggestions');
        const suggestionsUrl = input?.dataset.suggestionsUrl;
        let debounceTimer;

        if (!input || !suggestions || !suggestionsUrl) {
            return;
        }

        const hideSuggestions = () => {
            suggestions.innerHTML = '';
            suggestions.classList.remove('open');
        };

        const renderSuggestions = (items) => {
            if (!Array.isArray(items) || items.length === 0) {
                suggestions.innerHTML = '<div class="search-suggestions__empty">Ничего не найдено</div>';
                suggestions.classList.add('open');
                return;
            }

            suggestions.innerHTML = items.map(item => {
                const title = item.highlighted_title || item.title;
                const snippet = item.snippet ? `<span class="search-suggestions__snippet">${item.snippet}</span>` : '';
                const meta = item.type_label || item.type;

                return `<a href="${item.url}">
                        <span class="search-suggestions__title">${title}</span>
                        <span class="search-suggestions__meta">${meta}</span>
                        ${snippet}
                    </a>`;
            }).join('');

            suggestions.classList.add('open');
        };

        input.addEventListener('input', () => {
            clearTimeout(debounceTimer);

            const query = input.value.trim();
            if (query.length < 2) {
                hideSuggestions();
                return;
            }

            debounceTimer = setTimeout(async () => {
                try {
                    const response = await fetch(`${suggestionsUrl}?q=${encodeURIComponent(query)}`);
                    const data = await response.json();
                    renderSuggestions(data);
                } catch (e) {
                    hideSuggestions();
                }
            }, 200);
        });

        document.addEventListener('click', (event) => {
            if (!suggestions.contains(event.target) && event.target !== input) {
                hideSuggestions();
            }
        });
    })();
</script>
<script>
    (function () {
        class RemoteForm {
            constructor(root) {
                this.root = root;
                this.previewUrl = root.dataset.previewUrl;
                this.submitUrl = root.dataset.submitUrl;
                this.consentUrl = root.dataset.consentUrl;
                this.csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || root.dataset.csrf || '';
                this.statusEl = document.createElement('div');
                this.statusEl.className = 'form-status is-loading';
                this.root.innerHTML = '';
                this.root.appendChild(this.statusEl);
                this.loadForm();
            }

            setStatus(type, message) {
                const classes = ['form-status'];
                if (type) {
                    classes.push(`is-${type}`);
                }
                this.statusEl.className = classes.join(' ');

                if (!message) {
                    this.statusEl.classList.add('is-hidden');
                    this.statusEl.textContent = '';
                    return;
                }

                this.statusEl.classList.remove('is-hidden');
                this.statusEl.textContent = message;
            }

            async loadForm() {
                this.setStatus('loading', 'Загружаем форму...');

                try {
                    const response = await fetch(this.previewUrl, {headers: {'Accept': 'application/json'}});
                    const data = await response.json();

                    if (!response.ok || !data.success || !data.form) {
                        throw new Error(data.message || 'Не удалось загрузить форму');
                    }

                    this.renderForm(data.form);
                } catch (error) {
                    console.error(error);
                    this.setStatus('error', error.message || 'Не удалось загрузить форму');
                }
            }

            renderForm(form) {
                this.formEl = document.createElement('form');
                this.formEl.className = 'dynamic-form';
                this.formEl.noValidate = true;

                const fieldsContainer = document.createElement('div');
                fieldsContainer.className = 'dynamic-form__fields';

                if (Array.isArray(form.fields)) {
                    form.fields.forEach(field => fieldsContainer.appendChild(this.createField(field)));
                }

                this.formEl.appendChild(fieldsContainer);

                this.appendHiddenInput('source_url', this.root.dataset.sourceUrl || window.location.href);
                if (this.root.dataset.pageTitle) {
                    this.appendHiddenInput('page_title', this.root.dataset.pageTitle);
                }
                if (this.consentUrl) {
                    this.appendHiddenInput('consent_doc_url', this.consentUrl);
                }
                this.appendHiddenInput('_token', this.csrfToken);

                const actions = document.createElement('div');
                actions.className = 'dynamic-form__actions';
                const submitButton = document.createElement('button');
                submitButton.type = 'submit';
                submitButton.className = 'btn';
                submitButton.textContent = this.root.dataset.submitLabel || 'Отправить';
                actions.appendChild(submitButton);

                this.formEl.appendChild(actions);

                this.statusEl.classList.add('is-hidden');
                this.root.appendChild(this.formEl);
                this.formEl.addEventListener('submit', this.handleSubmit.bind(this));
            }

            createField(field) {
                const wrapper = document.createElement('div');
                wrapper.className = 'dynamic-form__field';
                wrapper.dataset.field = field.key;

                const label = document.createElement('label');
                label.className = 'dynamic-form__label';
                label.textContent = field.label || field.key;
                label.htmlFor = `${this.root.dataset.formCode}-${field.key}`;

                const errorEl = document.createElement('div');
                errorEl.className = 'dynamic-form__error';

                if (field.key === 'consent_given') {
                    const consentWrapper = document.createElement('label');
                    consentWrapper.className = 'dynamic-form__consent';

                    const checkbox = document.createElement('input');
                    checkbox.type = 'checkbox';
                    checkbox.name = 'consent_given';
                    checkbox.id = label.htmlFor;
                    checkbox.value = '1';
                    if (field.is_required) {
                        checkbox.required = true;
                    }

                    const text = document.createElement('span');
                    const link = document.createElement('a');
                    link.href = this.consentUrl || '#';
                    link.target = '_blank';
                    link.rel = 'noopener';
                    link.textContent = 'условиями обработки персональных данных';
                    text.append('Я соглашаюсь с ', link);

                    consentWrapper.appendChild(checkbox);
                    consentWrapper.appendChild(text);
                    wrapper.appendChild(consentWrapper);
                    wrapper.appendChild(errorEl);
                    return wrapper;
                }

                wrapper.appendChild(label);
                wrapper.appendChild(this.createControl(field));
                wrapper.appendChild(errorEl);

                return wrapper;
            }

            createControl(field) {
                const fieldType = field.type || 'text';
                const controlId = `${this.root.dataset.formCode}-${field.key}`;

                switch (fieldType) {
                    case 'textarea': {
                        const textarea = document.createElement('textarea');
                        textarea.name = field.key;
                        textarea.id = controlId;
                        textarea.rows = 3;
                        textarea.className = 'dynamic-form__control';
                        if (field.is_required) textarea.required = true;
                        return textarea;
                    }
                    case 'select': {
                        const select = document.createElement('select');
                        select.name = field.key;
                        select.id = controlId;
                        select.className = 'dynamic-form__control';
                        if (field.is_required) select.required = true;

                        const placeholder = document.createElement('option');
                        placeholder.value = '';
                        placeholder.textContent = 'Выберите вариант';
                        placeholder.disabled = true;
                        placeholder.selected = true;
                        select.appendChild(placeholder);

                        this.normalizedOptions(field.options).forEach(optionValue => {
                            const option = document.createElement('option');
                            option.value = optionValue;
                            option.textContent = optionValue;
                            select.appendChild(option);
                        });
                        return select;
                    }
                    case 'radio': {
                        const optionsWrapper = document.createElement('div');
                        optionsWrapper.className = 'dynamic-form__options';
                        this.normalizedOptions(field.options).forEach((optionValue, index) => {
                            const option = document.createElement('label');
                            option.className = 'dynamic-form__option';

                            const radio = document.createElement('input');
                            radio.type = 'radio';
                            radio.name = field.key;
                            radio.value = optionValue;
                            radio.id = `${controlId}-${index}`;
                            if (field.is_required) radio.required = true;

                            const text = document.createElement('span');
                            text.textContent = optionValue;

                            option.appendChild(radio);
                            option.appendChild(text);
                            optionsWrapper.appendChild(option);
                        });
                        return optionsWrapper;
                    }
                    default: {
                        const input = document.createElement('input');
                        input.name = field.key;
                        input.id = controlId;
                        input.type = this.resolveInputType(fieldType);
                        input.className = 'dynamic-form__control';
                        if (field.is_required) input.required = true;
                        if (field.mask) input.placeholder = field.mask;
                        return input;
                    }
                }
            }

            resolveInputType(fieldType) {
                switch (fieldType) {
                    case 'email':
                        return 'email';
                    case 'tel':
                    case 'phone':
                        return 'tel';
                    case 'checkbox':
                        return 'checkbox';
                    case 'date':
                        return 'date';
                    case 'file':
                        return 'file';
                    default:
                        return 'text';
                }
            }

            normalizedOptions(options) {
                if (Array.isArray(options)) {
                    return options;
                }

                if (options && typeof options === 'object') {
                    return Object.values(options);
                }

                return [];
            }

            async handleSubmit(event) {
                event.preventDefault();
                if (!this.formEl) {
                    return;
                }

                this.clearErrors();
                this.setStatus('loading', 'Отправляем данные...');
                this.setLoading(true);

                const formData = new FormData(this.formEl);
                this.appendUtmParams(formData);

                try {
                    const response = await fetch(this.submitUrl, {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': this.csrfToken,
                            'Accept': 'application/json',
                        },
                        body: formData,
                    });

                    const data = await response.json();

                    if (response.ok && data.success) {
                        this.setStatus('success', data.message || 'Заявка успешно отправлена');
                        this.formEl.reset();
                    } else if (response.status === 422 && data.errors) {
                        this.showErrors(data.errors);
                        this.setStatus('error', data.message || 'Проверьте корректность полей');
                    } else {
                        throw new Error(data.message || 'Не удалось отправить форму');
                    }
                } catch (error) {
                    console.error(error);
                    this.setStatus('error', error.message || 'Не удалось отправить форму');
                } finally {
                    this.setLoading(false);
                }
            }

            appendUtmParams(formData) {
                const search = new URLSearchParams(window.location.search);
                ['utm_source', 'utm_medium', 'utm_campaign', 'utm_term', 'utm_content'].forEach((key) => {
                    const value = search.get(key);
                    if (value && !formData.has(key)) {
                        formData.append(key, value);
                    }
                });
            }

            clearErrors() {
                this.root.querySelectorAll('.dynamic-form__error').forEach(error => error.textContent = '');
            }

            showErrors(errors) {
                Object.entries(errors).forEach(([key, messages]) => {
                    const field = this.root.querySelector(`[data-field="${key}"] .dynamic-form__error`);
                    if (field) {
                        field.textContent = Array.isArray(messages) ? messages.join(' ') : messages;
                    }
                });
            }

            setLoading(isLoading) {
                if (!this.formEl) {
                    return;
                }

                this.formEl.querySelectorAll('input, select, textarea, button').forEach(control => {
                    control.disabled = isLoading;
                });
            }

            appendHiddenInput(name, value) {
                if (!value || !this.formEl) {
                    return;
                }

                const input = document.createElement('input');
                input.type = 'hidden';
                input.name = name;
                input.value = value;
                this.formEl.appendChild(input);
            }
        }

        document.addEventListener('DOMContentLoaded', () => {
            document.querySelectorAll('.js-remote-form').forEach((root) => new RemoteForm(root));
        });
    })();
</script>
</body>
</html>
