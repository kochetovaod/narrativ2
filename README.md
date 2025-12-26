# 🏢 Сайт компании Нарратив

Корпоративный сайт-каталог и витрина экспертизы для презентации продукции/услуг, кейсов и производственных возможностей.

## 🧱 Технологический стек

- Laravel, PHP 8.3 (php-fpm)
- PostgreSQL 15
- Redis 7
- Meilisearch v1.7
- Nginx
- MailHog
- Node.js 20 (для сборки фронтенда)
- Docker + Docker Compose

## 🚀 Быстрый старт

### Предварительные требования
- Docker и Docker Compose
- Git

### Локальный запуск через `make` (рекомендуется)
1. Клонируйте репозиторий:
   ```bash
   git clone <repository-url>
   cd narrativ
   ```
2. Подготовьте окружение (копирование `.env` файлов, создание `database/backups`, сборка контейнеров, установка зависимостей, генерация `APP_KEY`):
   ```bash
   make setup
   ```
   Скрипт `scripts/setup.sh` выполнит шаги из Makefile, поэтому отдельные команды `cp src/.env.example src/.env` и т.д. запускать не нужно. Для разработки он использует `docker-compose.yml` + `docker-compose.dev.yml`, чтобы сразу поднять вспомогательные сервисы (phpMyAdmin, Adminer, Meilisearch UI) и HTTPS.
3. Запустите проект и убедитесь, что сервисы работают:
   ```bash
   make up-dev
   make ps
   ```

### Локальный запуск напрямую через Docker Compose
Если `make` недоступен, выполните команды вручную:
```bash
cp src/.env.example src/.env
cp src/.env.testing.example src/.env.testing
docker-compose -f docker-compose.yml -f docker-compose.dev.yml build
docker-compose -f docker-compose.yml -f docker-compose.dev.yml up -d
docker-compose -f docker-compose.yml -f docker-compose.dev.yml exec -T php-fpm composer install --working-dir=/var/www/html --no-interaction --prefer-dist
docker-compose -f docker-compose.yml -f docker-compose.dev.yml exec -T php-fpm npm ci
docker-compose -f docker-compose.yml -f docker-compose.dev.yml exec php-fpm php artisan key:generate
docker-compose -f docker-compose.yml -f docker-compose.dev.yml exec php-fpm php artisan storage:link
```

### Доступные команды Make

```bash
make help         # Список команд
make install      # Установка зависимостей
make build        # Сборка Docker образов
make up-dev       # Запуск контейнеров (docker-compose.yml + docker-compose.dev.yml)
make down         # Остановка контейнеров
make logs         # Просмотр логов
make test         # Запуск тестов
make backup       # Создание бэкапа (scripts/backup/backup.sh)
make deploy       # Деплой на staging (scripts/deploy/deploy.sh staging)
make deploy-prod  # Деплой на production (scripts/deploy/deploy.sh production)
```

## ⚙️ Переменные окружения

- Файлы окружения находятся в `src/.env` и `src/.env.testing`. Примеры — `src/.env.example` и `src/.env.testing.example`.
- `APP_KEY` генерируется автоматически при `make setup` или командой `docker-compose exec php-fpm php artisan key:generate`.
- Основные группы переменных:
  - Приложение: `APP_NAME`, `APP_ENV`, `APP_URL`, `APP_DEBUG`, `APP_KEY`.
  - База данных: `DB_CONNECTION=pgsql`, `DB_HOST=postgres`, `DB_PORT=5432`, `DB_DATABASE`, `DB_USERNAME`, `DB_PASSWORD`.
  - Redis/очереди: `REDIS_HOST=redis`, `REDIS_PORT=6379`, `QUEUE_CONNECTION=redis`, `REDIS_QUEUE`, `REDIS_QUEUE_RETRY_AFTER`.
  - Поиск: `SCOUT_DRIVER=meilisearch`, `MEILISEARCH_HOST=http://meilisearch:7700`, `MEILISEARCH_KEY`.
  - Почта: `MAIL_MAILER=smtp`, `MAIL_HOST=mailhog`, `MAIL_PORT=1025`, `MAIL_FROM_ADDRESS`, `MAIL_FROM_NAME`.
  - Интеграции: `TELEGRAM_BOT_TOKEN`, `TELEGRAM_CHAT_ID`, AWS/S3 при использовании внешнего хранилища.

Пример `.env` для локальной разработки (Docker):

```env
APP_NAME=Narrativ
APP_ENV=local
APP_DEBUG=true
APP_URL=https://localhost
APP_KEY=

DB_CONNECTION=pgsql
DB_HOST=postgres
DB_PORT=5432
DB_DATABASE=laravel
DB_USERNAME=laravel
DB_PASSWORD=secret

REDIS_HOST=redis
REDIS_PORT=6379
REDIS_QUEUE=default
REDIS_QUEUE_RETRY_AFTER=90

SCOUT_DRIVER=meilisearch
MEILISEARCH_HOST=http://meilisearch:7700
MEILISEARCH_KEY=masterKey

MAIL_MAILER=smtp
MAIL_HOST=mailhog
MAIL_PORT=1025
MAIL_FROM_ADDRESS="hello@example.com"
MAIL_FROM_NAME="${APP_NAME}"

TELEGRAM_BOT_TOKEN=
TELEGRAM_CHAT_ID=
```

## 📁 Структура проекта

```
narrativ/
├── src/                    # Laravel приложение
├── docker/                 # Docker конфигурации
│   ├── nginx/             # Nginx конфиги
│   └── php-fpm/           # PHP-FPM конфиги
├── docs/                  # Документация (CI/CD)
├── tests/                 # Тесты
├── scripts/               # Вспомогательные скрипты
│   ├── backup/backup.sh   # Создание бэкапов
│   └── deploy/deploy.sh   # Деплой staging/prod
├── .github/workflows/     # GitHub Actions
└── database/backups/      # Хранилище бэкапов (монтируется в /backups в контейнере postgres)
```

## 🌐 Доступ к сервисам (локальная разработка)

- Приложение: http://localhost и https://localhost
- PostgreSQL: localhost:5432
- Redis: localhost:6379
- Meilisearch API: http://localhost:7700
- Meilisearch UI: http://localhost:7701
- MailHog UI: http://localhost:8025
- phpMyAdmin: http://localhost:8081
- Adminer: http://localhost:8082

## 🐳 Docker сервисы

- **php-fpm**: PHP 8.3 с необходимыми расширениями
- **nginx**: Веб-сервер
- **postgres**: PostgreSQL 15
- **redis**: Redis 7
- **meilisearch**: Поисковый движок
- **mailhog**: Тестовый SMTP сервер

## 💾 Бэкапы и восстановление

- Создание бэкапа: `make backup` (запускает `scripts/backup/backup.sh`). Бэкапы складываются в `database/backups` (монтируется как `/backups` в контейнере `postgres`) и хранятся 7 дней. Файлы: `db_<timestamp>.sql`, `media_<timestamp>.tar.gz`, `logs_<timestamp>.tar.gz`.
- Восстановление:
  1. Скопируйте нужные архивы/дампы в `database/backups`.
  2. База данных:
     ```bash
     docker-compose exec -T postgres psql -U ${DB_USERNAME:-laravel} -d ${DB_DATABASE:-laravel} < database/backups/db_YYYYMMDD_HHMMSS.sql
     ```
  3. Медиа-файлы:
     ```bash
     tar -xzf database/backups/media_YYYYMMDD_HHMMSS.tar.gz -C src/storage/app/public
     ```
  4. (Опционально) логи:
     ```bash
     tar -xzf database/backups/logs_YYYYMMDD_HHMMSS.tar.gz -C src/storage/logs
     ```
  5. Очистите кеши после восстановления: `docker-compose exec -T php-fpm php artisan optimize:clear`.

## 🚢 Деплой (staging/production)

- Скрипт: `scripts/deploy/deploy.sh <staging|production>`.
- Быстрый запуск: `make deploy` (staging) или `make deploy-prod` (production).
- Скрипт обновляет код (`git pull origin main`), ставит зависимости `composer install --no-dev` и `npm ci --only=production`, собирает фронтенд (`npm run build`), выполняет миграции с `--force`, очищает кеши, перезапускает очереди и переиндексирует поиск (`scout:import` для `Product`, `PortfolioCase`, `Service`).
- Запускайте на целевом сервере с корректно настроенными `.env` и доступом к Docker Compose; используйте тот же набор Compose файлов, что и для приложения.

## 🔧 Разработка

### Локальная разработка
1. Внесите изменения в код
2. Запустите тесты: `make test`
3. Просмотрите логи: `make logs`

### Контейнерные команды
```bash
# Запуск миграций
docker-compose exec php-fpm php artisan migrate

# Запуск сидов
docker-compose exec php-fpm php artisan db:seed

# Генерация ключа приложения
docker-compose exec php-fpm php artisan key:generate

# Очистка кеша
docker-compose exec php-fpm php artisan optimize:clear
```

## 📝 Документация

Полная документация проекта находится в папке `docs/`:
- [CI/CD](docs/ci-cd/README.md)

## 🐛 Отладка

Для отладки включите XDebug в файле `docker/php-fpm/xdebug.ini` и пересоберите образ:
```bash
docker-compose build php-fpm
docker-compose up -d
```

## 📄 Лицензия

Проект разрабатывается для внутреннего использования.
```

### Шаг 1.8: Проверка структуры

```bash
# Вернемся в корень проекта
cd ..

# Проверим созданную структуру
tree -L 3 -I 'node_modules|vendor'
```

**Ожидаемая структура:**
```
narrativ/
├── .env.docker
├── .gitignore
├── Makefile
├── README.md
├── artisan
├── docker-compose.override.yml
├── docker-compose.prod.yml
├── docker-compose.yml
├── docker/
│   ├── nginx/
│   │   ├── nginx-ssl.conf
│   │   └── nginx.conf
│   └── php-fpm/
│       ├── Dockerfile
│       ├── php.ini
│       └── xdebug.ini
├── docs/
│   ├── api/
│   └── deployment/
├── scripts/
│   ├── backup/
│   └── deploy/
├── src/
│   ├── .env.example
│   ├── .env.testing.example
│   ├── app/
│   ├── database/
│   ├── public/
│   ├── resources/
│   ├── routes/
│   └── storage/
├── tests/
│   ├── Feature/
│   ├── Integration/
│   └── Unit/
└── .github/
    └── workflows/
```

### Шаг 1.9: Инициализация Git репозитория

```bash
# Инициализируем Git репозиторий
git init

# Добавляем все файлы
git add .

# Создаем первый коммит
git commit -m "Initial commit: Project structure and Docker setup"

# Создаем основную ветку
git branch -M main

# Проверяем статус
git status
