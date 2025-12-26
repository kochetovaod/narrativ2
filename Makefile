.PHONY: help install build up down restart logs test setup artisan composer npm backup deploy

help:
	@echo "Доступные команды:"
	@echo "  make install    - Установка зависимостей"
	@echo "  make build      - Сборка Docker образов"
	@echo "  make up         - Запуск контейнеров"
	@echo "  make down       - Остановка контейнеров"
	@echo "  make restart    - Перезапуск контейнеров"
	@echo "  make logs       - Просмотр логов"
	@echo "  make logs-f     - Просмотр логов в реальном времени"
	@echo "  make test       - Запуск тестов"
	@echo "  make setup      - Первоначальная настройка"
	@echo "  make artisan    - Запуск команд Laravel (например: make artisan cmd=\"migrate\")"
	@echo "  make composer   - Запуск Composer команд (например: make composer cmd=\"install\")"
	@echo "  make npm        - Запуск NPM команд (например: make npm cmd=\"run dev\")"
	@echo "  make backup     - Создание бэкапа"
	@echo "  make deploy     - Деплой в staging"
	@echo "  make deploy-prod - Деплой в production"
	@echo "  make ssh-php    - Вход в контейнер PHP-FPM"
	@echo "  make ssh-nginx  - Вход в контейнер Nginx"
	@echo "  make ssh-db     - Вход в контейнер PostgreSQL"
	@echo "  make ps         - Просмотр состояния контейнеров"

install:
	@echo "📦 Установка зависимостей..."
	docker-compose up -d php-fpm
	docker-compose exec -T php-fpm composer install --working-dir=/var/www/html --no-interaction --prefer-dist
	docker-compose run --rm npm install

build:
	docker-compose build

up:
	docker-compose up -d

down:
	docker-compose down

restart:
	docker-compose restart

logs:
	docker-compose logs

logs-f:
	docker-compose logs -f

test:
	docker-compose up -d php-fpm
	docker-compose exec -T php-fpm ./vendor/bin/phpunit

setup:
	@chmod +x scripts/setup.sh
	@./scripts/setup.sh

artisan:
	docker-compose exec php-fpm php artisan $(cmd)

composer:
	docker-compose up -d php-fpm
	docker-compose exec -T php-fpm composer --working-dir=/var/www/html $(cmd)

npm:
	docker-compose run --rm npm $(cmd)

backup:
	@chmod +x scripts/backup/backup.sh
	@./scripts/backup/backup.sh

deploy:
	@chmod +x scripts/deploy/deploy.sh
	@./scripts/deploy/deploy.sh staging

deploy-prod:
	@chmod +x scripts/deploy/deploy.sh
	@./scripts/deploy/deploy.sh production

ssh-php:
	docker-compose exec php-fpm sh

ssh-nginx:
	docker-compose exec nginx sh

ssh-db:
	docker-compose exec postgres psql -U laravel

ps:
	docker-compose ps

# Дополнительные команды
migrate:
	docker-compose exec php-fpm php artisan migrate

migrate-fresh:
	docker-compose exec php-fpm php artisan migrate:fresh --seed

tinker:
	docker-compose exec php-fpm php artisan tinker

queue:
	docker-compose exec php-fpm php artisan queue:work

horizon:
	docker-compose exec php-fpm php artisan horizon

clear-cache:
	docker-compose exec php-fpm php artisan optimize:clear

storage-link:
	docker-compose exec php-fpm php artisan storage:link

key-generate:
	docker-compose exec php-fpm php artisan key:generate

# Запуск в разных окружениях
up-dev:
	docker-compose -f docker-compose.yml -f docker-compose.dev.yml up -d

up-test:
	docker-compose -f docker-compose.yml -f docker-compose.test.yml up -d

up-prod:
	docker-compose -f docker-compose.yml -f docker-compose.prod.yml up -d
