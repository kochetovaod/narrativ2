#!/bin/bash

set -e

echo "🚀 Начало настройки проекта..."

# Проверяем наличие Docker
if ! command -v docker &> /dev/null; then
    echo "❌ Docker не установлен. Установите Docker и попробуйте снова."
    exit 1
fi

if ! command -v docker-compose &> /dev/null; then
    echo "❌ Docker Compose не установлен. Установите Docker Compose и попробуйте снова."
    exit 1
fi

# Создаем SSL сертификаты
echo "🔐 Создание SSL сертификатов..."
mkdir -p docker/nginx/ssl
if [ ! -f docker/nginx/ssl/localhost.crt ] || [ ! -f docker/nginx/ssl/localhost.key ]; then
    openssl req -x509 -nodes -days 365 -newkey rsa:2048 \
        -keyout docker/nginx/ssl/localhost.key \
        -out docker/nginx/ssl/localhost.crt \
        -subj "/C=RU/ST=Moscow/L=Moscow/O=Company/OU=IT/CN=localhost" 2>/dev/null
    echo "✅ SSL сертификаты созданы"
else
    echo "✅ SSL сертификаты уже существуют"
fi

# Копируем примеры файлов окружения
echo "📝 Настройка переменных окружения..."
if [ ! -f .env ]; then
    cp .env.example .env
    echo "✅ Файл .env создан из примера"
else
    echo "✅ Файл .env уже существует"
fi

if [ ! -f .env.testing.example ]; then
    cp .env.testing.example .env.testing
    echo "✅ Файл .env.testing создан из примера"
else
    echo "✅ Файл .env.testing уже существует"
fi

# Создаем папки для логов
echo "📁 Создание структуры папок..."
mkdir -p src/storage/logs
mkdir -p src/storage/framework/{cache,sessions,views}
mkdir -p src/bootstrap/cache
mkdir -p database/backups

# Сборка и запуск контейнеров
echo "🐳 Сборка Docker образов..."
docker-compose build

echo "🚀 Запуск контейнеров..."
docker-compose up -d

# Ждем запуска сервисов
echo "⏳ Ожидание запуска сервисов..."
sleep 10

# Проверяем работу сервисов
echo "🔍 Проверка работы сервисов..."
if docker-compose ps | grep -q "Up"; then
    echo "✅ Все сервисы запущены"
else
    echo "❌ Некоторые сервисы не запустились"
    docker-compose ps
    exit 1
fi

# Установка зависимостей Laravel
echo "📦 Установка PHP зависимостей..."
docker-compose exec -T php-fpm composer install --no-interaction --prefer-dist

echo "📦 Установка Node.js зависимостей..."
docker-compose exec -T php-fpm npm ci

# Настройка Laravel
echo "⚙️  Настройка Laravel..."
docker-compose exec -T php-fpm php artisan key:generate
docker-compose exec -T php-fpm php artisan storage:link
docker-compose exec -T php-fpm php artisan optimize:clear

# Настройка прав доступа
echo "🔧 Настройка прав доступа..."
docker-compose exec -T php-fpm chmod -R 775 storage bootstrap/cache
docker-compose exec -T php-fpm chown -R laravel:laravel storage bootstrap/cache

echo ""
echo "🎉 Настройка завершена!"
echo ""
echo "🌐 Доступные сервисы:"
echo "   • Приложение:        https://localhost"
echo "   • PHPMyAdmin:        http://localhost:8081"
echo "   • Adminer:           http://localhost:8082"
echo "   • MailHog:           http://localhost:8025"
echo "   • Meilisearch:       http://localhost:7700"
echo ""
echo "📋 Команды управления:"
echo "   • make up            - Запуск контейнеров"
echo "   • make down          - Остановка контейнеров"
echo "   • make logs          - Просмотр логов"
echo "   • make artisan       - Запуск команд Laravel"
echo ""