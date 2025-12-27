<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $page->seo['title'] ?? $page->title }} - Предпросмотр</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            line-height: 1.6;
            color: #333;
        }

        .preview-header {
            background: #f8f9fa;
            border-bottom: 1px solid #dee2e6;
            padding: 1rem;
            position: sticky;
            top: 0;
            z-index: 1000;
        }

        .preview-banner {
            background: #007bff;
            color: white;
            padding: 0.5rem 1rem;
            text-align: center;
            font-size: 0.875rem;
        }

        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 2rem 1rem;
        }

        .page-content {
            margin-top: 1rem;
        }

        .section {
            margin-bottom: 3rem;
        }

        .section.hero {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 4rem 2rem;
            border-radius: 0.5rem;
            text-align: center;
        }

        .section.hero h1 {
            font-size: 2.5rem;
            margin-bottom: 1rem;
        }

        .section.hero .subtitle {
            font-size: 1.25rem;
            margin-bottom: 2rem;
            opacity: 0.9;
        }

        .section.text h2 {
            font-size: 2rem;
            margin-bottom: 1rem;
            color: #333;
        }

        .section.text .content {
            font-size: 1.1rem;
            line-height: 1.8;
        }

        .section.categories-grid {
            text-align: center;
        }

        .section.categories-grid h2 {
            font-size: 2rem;
            margin-bottom: 2rem;
        }

        .categories-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 2rem;
            margin-top: 2rem;
        }

        .category-card {
            background: white;
            border: 1px solid #e9ecef;
            border-radius: 0.5rem;
            padding: 1.5rem;
            text-align: center;
            transition: transform 0.2s, box-shadow 0.2s;
        }

        .category-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(0,0,0,0.1);
        }

        .section.services-list {
            text-align: center;
        }

        .section.services-list h2 {
            font-size: 2rem;
            margin-bottom: 2rem;
        }

        .services-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 2rem;
            margin-top: 2rem;
        }

        .service-card {
            background: white;
            border: 1px solid #e9ecef;
            border-radius: 0.5rem;
            padding: 2rem;
            text-align: center;
        }

        .section.portfolio {
            text-align: center;
        }

        .section.portfolio h2 {
            font-size: 2rem;
            margin-bottom: 2rem;
        }

        .portfolio-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 2rem;
            margin-top: 2rem;
        }

        .portfolio-card {
            background: white;
            border: 1px solid #e9ecef;
            border-radius: 0.5rem;
            overflow: hidden;
            transition: transform 0.2s;
        }

        .portfolio-card:hover {
            transform: translateY(-2px);
        }

        .portfolio-image {
            height: 200px;
            background: #f8f9fa;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #6c757d;
        }

        .portfolio-content {
            padding: 1.5rem;
        }

        .section.cta {
            background: #f8f9fa;
            padding: 3rem 2rem;
            border-radius: 0.5rem;
            text-align: center;
        }

        .section.cta h2 {
            font-size: 2rem;
            margin-bottom: 1rem;
        }

        .section.contacts {
            background: #343a40;
            color: white;
            padding: 3rem 2rem;
            border-radius: 0.5rem;
            text-align: center;
        }

        .section.contacts h2 {
            font-size: 2rem;
            margin-bottom: 2rem;
        }

        .contact-info {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 2rem;
            margin-top: 2rem;
        }

        .contact-item {
            text-align: center;
        }

        .section.gallery {
            text-align: center;
        }

        .section.gallery h2 {
            font-size: 2rem;
            margin-bottom: 2rem;
        }

        .gallery-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 1rem;
            margin-top: 2rem;
        }

        .gallery-item {
            aspect-ratio: 1;
            background: #f8f9fa;
            border-radius: 0.5rem;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #6c757d;
        }

        .section.advantages {
            text-align: center;
        }

        .section.advantages h2 {
            font-size: 2rem;
            margin-bottom: 2rem;
        }

        .advantages-list {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 2rem;
            margin-top: 2rem;
        }

        .advantage-item {
            background: white;
            border: 1px solid #e9ecef;
            border-radius: 0.5rem;
            padding: 2rem;
        }

        .advantage-item h3 {
            font-size: 1.25rem;
            margin-bottom: 1rem;
            color: #007bff;
        }

        .btn {
            display: inline-block;
            padding: 0.75rem 1.5rem;
            background: #007bff;
            color: white;
            text-decoration: none;
            border-radius: 0.375rem;
            border: none;
            cursor: pointer;
            font-size: 1rem;
            transition: background-color 0.2s;
        }

        .btn:hover {
            background: #0056b3;
        }

        .btn-secondary {
            background: #6c757d;
        }

        .btn-secondary:hover {
            background: #545b62;
        }

        .cta-buttons {
            display: flex;
            gap: 1rem;
            justify-content: center;
            margin-top: 2rem;
            flex-wrap: wrap;
        }

        @media (max-width: 768px) {
            .container {
                padding: 1rem;
            }

            .section.hero {
                padding: 2rem 1rem;
            }

            .section.hero h1 {
                font-size: 2rem;
            }

            .categories-grid,
            .services-grid,
            .portfolio-grid,
            .advantages-list {
                grid-template-columns: 1fr;
            }

            .cta-buttons {
                flex-direction: column;
                align-items: center;
            }
        }
    </style>
</head>
<body>
    <div class="preview-header">
        <div class="preview-banner">
            <strong>ПРЕДПРОСМОТР</strong> - Это страница в режиме предпросмотра. Изменения еще не опубликованы.
        </div>
    </div>

    <div class="container">
        <div class="page-content">
            <h1 style="margin-bottom: 2rem; text-align: center;">{{ $page->title }}</h1>

            @if($page->sections && is_array($page->sections))
                @foreach($page->sections as $section)
                    @include('preview.sections.' . $section['type'], ['section' => $section])
                @endforeach
            @else
                <div class="text-center text-muted">
                    <p>Содержимое страницы будет отображаться здесь</p>
                </div>
            @endif
        </div>
    </div>
</body>
</html>
