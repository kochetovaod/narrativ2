# План реализации системы импорт/экспорт

## Информация, собранная из анализа

### Изученные модели и их структура

1. **Product** - товары
   - Основные поля: title, slug, description, specs (JSON), status
   - Связи: belongsTo ProductCategory, belongsToMany PortfolioCase
   - SEO: seo (JSON), schema_json (JSON)

2. **Service** - услуги  
   - Основные поля: title, slug, content (JSON), status
   - Связи: belongsToMany PortfolioCase
   - SEO: seo (JSON), schema_json (JSON)
   - Специальные: show_cases (boolean)

3. **PortfolioCase** - кейсы портфолио
   - Основные поля: title, slug, description, client_name, is_nda (boolean), date, status
   - Связи: belongsToMany Product, belongsToMany Service
   - SEO: seo (JSON)

4. **Lead** - заявки
   - Основные поля: form_code, status, phone, email, payload (JSON), source_url, page_title
   - UTM: utm (JSON)
   - Согласия: consent_given (boolean), consent_at (datetime)
   - Связи: hasOne LeadDedupIndex

5. **ProductCategory** - категории товаров
   - Основные поля: title, slug, intro_text, body (JSON), status
   - SEO: seo (JSON), schema_json (JSON)

6. **MediaLink** - связи с медиафайлами (morphMany для всех сущностей)

## План реализации

### Этап 1: Базовая архитектура импорт/экспорт

**1.1. Создать модель ImportLog**

- Поля: entity_type, operation_type, status, total_records, processed_records, error_records, file_path, error_log, started_at, finished_at, user_id

**1.2. Создать базовые интерфейсы и классы**

- ImportExportService (абстрактный базовый)
- ImportExportInterface
- CSVProcessor (для работы с CSV)

**1.3. Создать миграцию для таблицы import_logs**

- Миграция: `2025_01_01_000000_create_import_logs_table.php`

**1.4. Создать FileStorageService**

- Загрузка/скачивание файлов
- Валидация CSV форматов

### Этап 2: Специализированные импортеры

**2.1. ProductImporter**

- Валидация полей: title, slug, category_id, description, status
- Обработка specs (JSON характеристики)
- Связи с категориями и кейсами
- Генерация preview_token

**2.2. ServiceImporter**

- Валидация полей: title, slug, content, status, show_cases
- Связи с кейсами
- JSON контент

**2.3. PortfolioCaseImporter**

- Валидация полей: title, slug, client_name, is_nda, date, status
- Обработка boolean is_nda
- Связи с продуктами и услугами

**2.4. LeadImporter**

- Валидация полей: form_code, status, phone, email, source_url
- Обработка UTM параметров
- Дедупликация по email/phone
- JSON payload

**2.5. ProductCategoryImporter**

- Валидация полей: title, slug, intro_text, body, status
- JSON body контент

### Этап 3: Экспорт функциональность

**3.1. Создать CSVExporter**

- Экспорт с фильтрами
- Настраиваемые колонки
- UTF-8 BOM для Excel

**3.2. Экспорт заявок с фильтрами**

- По дате, статусу, типу, источнику
- Включение UTM параметров

**3.3. Экспорт контента**

- Товары с категориями
- Услуги с кейсами
- Кейсы с продуктами/услугами

### Этап 4: Админский интерфейс

**4.1. Создать ImportExportScreen**

- Форма загрузки CSV
- Выбор типа сущности
- Режим импорта (CREATE/UPDATE/UPSERT)
- Прогресс-бар

**4.2. Создать ImportResultScreen**

- Отчет об импорте
- Список ошибок
- Статистика

**4.3. Создать ExportScreen**

- Форма экспорта
- Настройка фильтров
- Скачивание файла

### Этап 5: Интеграция в систему

**5.1. Добавить маршруты**

- `/platform/import-export` - главная страница
- `/platform/import-export/import/{entity}` - импорт
- `/platform/import-export/export/{entity}` - экспорт
- `/platform/import-export/logs` - логи

**5.2. Добавить права доступа**

- `import_export.manage` - полное управление
- `import_export.import` - только импорт
- `import_export.export` - только экспорт

**5.3. Добавить пункт меню**

- В PlatformProvider: "Импорт/Экспорт"

### Этап 6: Тестирование и примеры

**6.1. Создать примеры CSV файлов**

- products.csv - пример товаров
- services.csv - пример услуг
- portfolio_cases.csv - пример кейсов
- leads.csv - пример заявок
- product_categories.csv - пример категорий

**6.2. Создать сидеры с тестовыми данными**

- ImportExportSeeder

## Структура CSV файлов

### Products CSV

```
id,title,slug,description,specs,category_id,portfolio_cases,status,seo_title,seo_description,seo_h1,published_at
1,"Товар 1","product-1","Описание товара","{""weight"":""10kg"",""color"":""red""}",1,"1,2,3",published,"SEO Title","SEO Description","H1",2025-01-01
```

### Services CSV

```
id,title,slug,content,show_cases,portfolio_cases,status,seo_title,seo_description,seo_h1,published_at
1,"Услуга 1","service-1","{""blocks"":[{""type"":""text"",""content"":""Текст""}]}",1,"1,2,3",published,"SEO Title","SEO Description","H1",2025-01-01
```

### Portfolio Cases CSV

```
id,title,slug,description,client_name,is_nda,date,products,services,status,seo_title,seo_description,seo_h1,published_at
1,"Кейс 1","case-1","Описание кейса","ООО Рога",1,2025-01-01,"1,2","1,2",published,"SEO Title","SEO Description","H1",2025-01-01
```

### Leads CSV

```
id,form_code,status,phone,email,payload,source_url,page_title,utm_source,utm_medium,utm_campaign,utm_term,utm_content,consent_given,created_at
1,contact_form,new,+79001234567,test@example.com,"{""name"":""Иван"",""message"":""Привет""}","https://site.ru","Главная","google","cpc","campaign1","keyword1","content1",1,2025-01-01 12:00:00
```

### Product Categories CSV

```
id,title,slug,intro_text,body,status,seo_title,seo_description,seo_h1,published_at
1,"Категория 1","category-1","Вводный текст","{""blocks"":[{""type"":""text"",""content"":""Текст""}]}",published,"SEO Title","SEO Description","H1",2025-01-01
```

## Зависимые файлы для редактирования

1. **Новые файлы для создания:**
   - `src/app/Models/ImportLog.php`
   - `src/app/Services/ImportExport/ImportExportService.php`
   - `src/app/Services/ImportExport/FileStorageService.php`
   - `src/app/Services/ImportExport/CSVProcessor.php`
   - `src/app/Services/ImportExport/ProductImporter.php`
   - `src/app/Services/ImportExport/ServiceImporter.php`
   - `src/app/Services/ImportExport/PortfolioCaseImporter.php`
   - `src/app/Services/ImportExport/LeadImporter.php`
   - `src/app/Services/ImportExport/ProductCategoryImporter.php`
   - `src/app/Services/ImportExport/CSVExporter.php`
   - `src/app/Orchid/Screens/ImportExport/ImportExportScreen.php`
   - `src/app/Orchid/Screens/ImportExport/ImportResultScreen.php`
   - `src/app/Orchid/Screens/ImportExport/ExportScreen.php`
   - `src/database/migrations/2025_01_01_000000_create_import_logs_table.php`
   - `src/database/seeders/ImportExportSeeder.php`

2. **Существующие файлы для изменения:**
   - `src/routes/platform.php` - добавить маршруты
   - `src/app/Orchid/PlatformProvider.php` - добавить меню и права доступа

3. **Файлы примеров для создания:**
   - `storage/import/examples/products.csv`
   - `storage/import/examples/services.csv`
   - `storage/import/examples/portfolio_cases.csv`
   - `storage/import/examples/leads.csv`
   - `storage/import/examples/product_categories.csv`

## Следующие шаги

1. Подтверждение плана пользователем
2. Начать с создания базовой архитектуры (модель ImportLog, базовые сервисы)
3. Реализовать импортеры по одному
4. Создать админский интерфейс
5. Интегрировать в систему
6. Создать примеры и протестировать
