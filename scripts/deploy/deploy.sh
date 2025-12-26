#!/bin/bash

set -e

ENV=${1:-staging}

echo "🚀 Начало деплоя в окружение: $ENV"

# Обновляем код
echo "🔄 Обновление кода..."
git pull origin main

# Устанавливаем зависимости
echo "📦 Установка зависимостей..."
docker-compose exec -T php-fpm composer install --no-dev --optimize-autoloader
docker-compose exec -T php-fpm npm ci --only=production

# Собираем фронтенд
echo "🎨 Сборка фронтенда..."
docker-compose exec -T php-fpm npm run build

# Запускаем миграции
echo "🗄️  Выполнение миграций..."
docker-compose exec -T php-fpm php artisan migrate --force

# Очищаем кэш
echo "🧹 Очистка кэша..."
docker-compose exec -T php-fpm php artisan optimize:clear
docker-compose exec -T php-fpm php artisan config:cache
docker-compose exec -T php-fpm php artisan route:cache
docker-compose exec -T php-fpm php artisan view:cache

# Перезапускаем очереди
echo "🔄 Перезапуск очередей..."
docker-compose exec -T php-fpm php artisan queue:restart

# Индексация поиска
echo "🔍 Индексация данных для поиска..."
docker-compose exec -T php-fpm php artisan scout:import "App\\Models\\Product"
docker-compose exec -T php-fpm php artisan scout:import "App\\Models\\PortfolioCase"
docker-compose exec -T php-fpm php artisan scout:import "App\\Models\\Service"

echo "✅ Деплой завершен!"