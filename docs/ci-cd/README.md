# CI/CD Документация

## GitHub Actions Workflows

### 1. Tests Workflow

**Файл:** `.github/workflows/tests.yml`
**Триггеры:** push в main/develop, pull request
**Действия:**

- Линтинг и проверка стиля кода
- Запуск тестов на PHP 8.2 и 8.3
- Сборка Docker образа
- Отправка уведомлений в Telegram

### 2. Deploy to Staging

**Файл:** `.github/workflows/deploy-staging.yml`
**Триггеры:** push в develop, manual
**Действия:**

- Сборка Docker образа для staging
- Деплой на staging сервер
- Выполнение миграций
- Индексация поиска
- Уведомления

### 3. Deploy to Production

**Файл:** `.github/workflows/deploy-production.yml`
**Триггеры:** push тегов v*, manual
**Действия:**

- Сборка Docker образа для production
- Деплой на production сервер
- Создание бэкапа
- Выполнение миграций
- Очистка кэша
- Создание GitHub Release
- Уведомления

## Необходимые Secrets

### Телеграм уведомления

- `TELEGRAM_BOT_TOKEN` - токен бота
- `TELEGRAM_CHAT_ID` - ID чата для уведомлений

### Staging окружение

```bash
STAGING_SSH_HOST=staging.server.com
STAGING_SSH_USER=deploy
STAGING_SSH_KEY=# приватный SSH ключ
STAGING_APP_URL=https://staging.catalog.local
STAGING_DB_DATABASE=catalog_staging
STAGING_DB_USERNAME=catalog_user
STAGING_DB_PASSWORD=# пароль БД
STAGING_MEILISEARCH_KEY=# ключ Meilisearch
```

### Production окружение

```bash
PRODUCTION_SSH_HOST=production.server.com
PRODUCTION_SSH_USER=deploy
PRODUCTION_SSH_KEY=# приватный SSH ключ
PRODUCTION_APP_URL=https://catalog.ru
PRODUCTION_DB_DATABASE=catalog_prod
PRODUCTION_DB_USERNAME=catalog_user
PRODUCTION_DB_PASSWORD=# пароль БД
PRODUCTION_MEILISEARCH_KEY=# ключ Meilisearch
PRODUCTION_MAIL_MAILER=smtp
PRODUCTION_MAIL_HOST=smtp.yandex.ru
PRODUCTION_MAIL_PORT=465
PRODUCTION_MAIL_USERNAME=noreply@catalog.ru
PRODUCTION_MAIL_PASSWORD=# пароль почты
```

## Локальное тестирование

### Установка Act

```bash
# Ubuntu/Debian
sudo apt-get install act

# macOS
brew install act
```

### Запуск workflows локально

```bash
# Тестируем workflow тестов
act -j lint

# Тестируем полный workflow
act push -W .github/workflows/tests.yml
```

## Мониторинг

### GitHub Actions Status

- <https://github.com/[owner]/[repo]/actions>

### Docker Images

- <https://ghcr.io/[owner]/[repo>]

### Production Health Check

- <https://catalog.ru/health>

## Устранение неполадок

### Workflow не запускается

1. Проверьте триггеры в файле workflow
2. Проверьте наличие необходимых Secrets
3. Проверьте права доступа

### Ошибки при деплое

1. Проверьте SSH ключи
2. Проверьте доступность сервера
3. Проверьте логи в Actions

### Docker build fails

1. Проверьте Dockerfile
2. Проверьте контекст сборки
3. Проверьте доступ к реестру
