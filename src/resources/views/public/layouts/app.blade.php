<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
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
        }

        .search-suggestions a:hover {
            background: #f8fafc;
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
<header class="site-header">
    <div class="container nav-container">
        <a href="{{ route('home') }}" class="logo">{{ config('app.name', 'Narrativ') }}</a>

        <nav aria-label="Основная навигация">
            <ul>
                <li><a href="{{ route('products.index') }}">Продукция</a></li>
                <li><a href="{{ route('services.index') }}">Услуги</a></li>
                <li><a href="{{ route('portfolio.index') }}">Портфолио</a></li>
                <li><a href="{{ route('news.index') }}">Новости</a></li>
            </ul>
        </nav>

        <form class="search" action="{{ route('search') }}" method="get" role="search">
            <input id="search-input" name="q" value="{{ request('q') }}" type="search"
                   placeholder="Поиск по сайту"
                   aria-label="Поиск по сайту"
                   data-suggestions-url="{{ route('search.suggestions') }}">
            <button type="submit">Поиск</button>
            <div class="search-suggestions" id="search-suggestions"></div>
        </form>
    </div>
</header>

<main>
    <div class="container">
        @isset($breadcrumbs)
            @include('public.partials.breadcrumbs', ['breadcrumbs' => $breadcrumbs])
        @endisset

        @yield('content')
    </div>
</main>

<footer class="site-footer">
    <div class="container footer-grid">
        <div>
            <div class="logo">{{ config('app.name', 'Narrativ') }}</div>
            <p>Дизайн, производство и внедрение решений под ключ.</p>
        </div>
        <div>
            <div class="meta" style="margin-bottom: 0.5rem;">Контакты</div>
            <p>Тел.: <a href="tel:+7">+7 (000) 000-00-00</a></p>
            <p>Email: <a href="mailto:hello@example.com">hello@example.com</a></p>
        </div>
        <div>
            <div class="meta" style="margin-bottom: 0.5rem;">Соцсети</div>
            <div class="list-inline">
                <a href="#" aria-label="Telegram">Telegram</a>
                <a href="#" aria-label="WhatsApp">WhatsApp</a>
                <a href="#" aria-label="VK">VK</a>
            </div>
        </div>
    </div>
</footer>

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
                hideSuggestions();
                return;
            }

            suggestions.innerHTML = items.map(item => {
                return `<a href="${item.url}">
                        <strong>${item.title}</strong>
                        <div class="meta">${item.type}</div>
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
</body>
</html>
