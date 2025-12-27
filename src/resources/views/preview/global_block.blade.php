<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $block->title ?? 'Глобальный блок' }} - Предпросмотр</title>
    <style>
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            margin: 0;
            padding: 20px;
            background-color: #f8f9fa;
        }
        .preview-header {
            background: #007bff;
            color: white;
            padding: 10px 20px;
            margin: -20px -20px 20px -20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .preview-badge {
            background: rgba(255,255,255,0.2);
            padding: 5px 10px;
            border-radius: 4px;
            font-size: 12px;
        }
        .block-content {
            background: white;
            border-radius: 8px;
            padding: 20px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        .block-title {
            margin-top: 0;
            margin-bottom: 15px;
            color: #333;
        }
        .json-content {
            background: #f8f9fa;
            border: 1px solid #e9ecef;
            border-radius: 4px;
            padding: 15px;
            font-family: monospace;
            font-size: 14px;
            white-space: pre-wrap;
        }
    </style>
</head>
<body>
    <div class="preview-header">
        <div>
            <strong>Предпросмотр глобального блока</strong>
        </div>
        <div class="preview-badge">
            Код: {{ $block->code }}
        </div>
    </div>

    <div class="block-content">
        <h2 class="block-title">{{ $block->title }}</h2>
        
        @if($block->content)
            @if(is_array($block->content))
                <h4>Содержимое блока (JSON):</h4>
                <div class="json-content">{{ json_encode($block->content, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</div>
            @else
                <div>{!! $block->content !!}</div>
            @endif
        @else
            <p><em>Содержимое блока не задано</em></p>
        @endif
        
        @if(!$block->is_active)
            <div style="background: #fff3cd; border: 1px solid #ffeaa7; color: #856404; padding: 10px; border-radius: 4px; margin-top: 15px;">
                <strong>Внимание:</strong> Этот блок неактивен и не будет отображаться на сайте.
            </div>
        @endif
    </div>

    <script>
        // Закрыть окно предпросмотра
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                window.close();
            }
        });
    </script>
</body>
</html>
