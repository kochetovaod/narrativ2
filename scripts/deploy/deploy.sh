#!/bin/bash

set -e

ENVIRONMENT="${ENV:-${1:-staging}}"

case "${ENVIRONMENT}" in
    production)
        BRANCH="${BRANCH:-main}"
        COMPOSE_FILES="-f docker-compose.yml -f docker-compose.prod.yml"
        ENV_FILE="src/.env.production"
        ;;
    staging)
        BRANCH="${BRANCH:-develop}"
        COMPOSE_FILES="-f docker-compose.yml -f docker-compose.dev.yml"
        ENV_FILE="src/.env.staging"
        ;;
    test|testing)
        BRANCH="${BRANCH:-develop}"
        COMPOSE_FILES="-f docker-compose.yml -f docker-compose.test.yml"
        ENV_FILE="src/.env.testing"
        ;;
    dev|development|local)
        BRANCH="${BRANCH:-develop}"
        COMPOSE_FILES="-f docker-compose.yml -f docker-compose.dev.yml"
        ENV_FILE="src/.env"
        ;;
    *)
        echo "❌ Неизвестное окружение ${ENVIRONMENT}. Используйте dev, staging, test или production."
        exit 1
        ;;
esac

if [ -f "${ENV_FILE}" ]; then
    COMPOSE_ENV_FILE="--env-file ${ENV_FILE}"
else
    echo "⚠️  Файл ${ENV_FILE} не найден, docker-compose использует переменные окружения из shell."
    COMPOSE_ENV_FILE=""
fi

COMPOSE_CMD="docker-compose ${COMPOSE_ENV_FILE} ${COMPOSE_FILES}"

echo "🚀 Начало деплоя"
echo "   • Окружение: ${ENVIRONMENT}"
echo "   • Ветка: ${BRANCH}"
echo "   • Compose: ${COMPOSE_FILES}"
echo "   • Env file: ${ENV_FILE}"

# Обновляем код
echo "🔄 Обновление кода..."
git fetch origin
git checkout "${BRANCH}"
git pull origin "${BRANCH}"

# Сборка/загрузка образов
echo "🐳 Сборка/загрузка Docker образов..."
${COMPOSE_CMD} pull php-fpm nginx || true
${COMPOSE_CMD} build php-fpm nginx

# Запуск контейнеров
echo "🚀 Запуск контейнеров..."
${COMPOSE_CMD} up -d --remove-orphans

# Устанавливаем зависимости
echo "📦 Установка зависимостей..."
${COMPOSE_CMD} exec -T php-fpm composer install --no-dev --optimize-autoloader
${COMPOSE_CMD} exec -T php-fpm npm ci --only=production

# Собираем фронтенд
echo "🎨 Сборка фронтенда..."
${COMPOSE_CMD} exec -T php-fpm npm run build

# Обновляем симлинк на хранилище
echo "🔗 Обновление публичного хранилища..."
${COMPOSE_CMD} exec -T php-fpm php artisan storage:link

# Запускаем миграции
echo "🗄️  Выполнение миграций..."
${COMPOSE_CMD} exec -T php-fpm php artisan migrate --force

# Очищаем кэш
echo "🧹 Очистка кэша..."
${COMPOSE_CMD} exec -T php-fpm php artisan optimize:clear
${COMPOSE_CMD} exec -T php-fpm php artisan config:cache
${COMPOSE_CMD} exec -T php-fpm php artisan route:cache
${COMPOSE_CMD} exec -T php-fpm php artisan view:cache

# Перезапускаем очереди
echo "🔄 Перезапуск очередей..."
${COMPOSE_CMD} exec -T php-fpm php artisan queue:restart

# Индексация поиска
echo "🔍 Индексация данных для поиска..."
${COMPOSE_CMD} exec -T php-fpm php artisan scout:import "App\\Models\\Product"
${COMPOSE_CMD} exec -T php-fpm php artisan scout:import "App\\Models\\PortfolioCase"
${COMPOSE_CMD} exec -T php-fpm php artisan scout:import "App\\Models\\Service"

echo "✅ Деплой завершен!"
