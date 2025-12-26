
ENV ?= dev

ifeq ($(origin ENV), default)
DEPLOY_ENV = staging
else
DEPLOY_ENV = $(ENV)
endif

ifeq ($(ENV),production)
COMPOSE_FILES = -f docker-compose.yml -f docker-compose.prod.yml
ENV_FILE = src/.env.production
endif

ifeq ($(ENV),staging)
COMPOSE_FILES = -f docker-compose.yml -f docker-compose.dev.yml
ENV_FILE = src/.env.staging
endif

ifeq ($(ENV),test)
COMPOSE_FILES = -f docker-compose.yml -f docker-compose.test.yml
ENV_FILE = src/.env.testing
endif

ifeq ($(ENV),dev)
COMPOSE_FILES = -f docker-compose.yml -f docker-compose.dev.yml
ENV_FILE = src/.env
endif

ifeq ($(COMPOSE_FILES),)
$(error Неизвестное окружение $(ENV). Используйте dev, staging, test или production)
endif

ifneq (,$(wildcard $(ENV_FILE)))
COMPOSE_ENV_FILE = --env-file $(ENV_FILE)
else
$(warning Файл $(ENV_FILE) не найден. docker-compose будет использовать переменные окружения из shell.)
COMPOSE_ENV_FILE =
endif

COMPOSE = docker-compose $(COMPOSE_ENV_FILE) $(COMPOSE_FILES)

.PHONY: help install build up down restart logs test setup artisan composer npm backup deploy

help:
	@echo "Доступные команды:"
	@echo "  ENV=<dev|staging|production|test> make <target> - Запуск команд в нужном окружении (по умолчанию dev для локальной разработки)"
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
	@echo "  make deploy     - Деплой в выбранное окружение (ENV=staging|production|dev|test)"
	@echo "  make deploy-prod - Упрощенный вызов деплоя в production"
	@echo "  make ssh-php    - Вход в контейнер PHP-FPM"
	@echo "  make ssh-nginx  - Вход в контейнер Nginx"
	@echo "  make ssh-db     - Вход в контейнер PostgreSQL"
	@echo "  make ps         - Просмотр состояния контейнеров"

install:
	@echo "📦 Установка зависимостей..."
	$(COMPOSE) up -d php-fpm
	$(COMPOSE) exec -T php-fpm composer install --working-dir=/var/www/html --no-interaction --prefer-dist
	$(COMPOSE) run --rm npm install

build:
	$(COMPOSE) build

up:
	$(COMPOSE) up -d

down:
	$(COMPOSE) down

restart:
	$(COMPOSE) restart

logs:
	$(COMPOSE) logs

logs-f:
	$(COMPOSE) logs -f

test:
	$(COMPOSE) up -d php-fpm
	$(COMPOSE) exec -T php-fpm ./vendor/bin/phpunit

setup:
	@chmod +x scripts/setup.sh
	@./scripts/setup.sh

artisan:
	$(COMPOSE) exec php-fpm php artisan $(cmd)

composer:
	$(COMPOSE) up -d php-fpm
	$(COMPOSE) exec -T php-fpm composer --working-dir=/var/www/html $(cmd)

npm:
	$(COMPOSE) run --rm npm $(cmd)

backup:
	@chmod +x scripts/backup/backup.sh
	@./scripts/backup/backup.sh

deploy:
	@chmod +x scripts/deploy/deploy.sh
	ENV=$(DEPLOY_ENV) ./scripts/deploy/deploy.sh

deploy-prod:
	@chmod +x scripts/deploy/deploy.sh
	ENV=production ./scripts/deploy/deploy.sh

ssh-php:
	$(COMPOSE) exec php-fpm sh

ssh-nginx:
	$(COMPOSE) exec nginx sh

ssh-db:
	$(COMPOSE) exec postgres psql -U laravel

ps:
	$(COMPOSE) ps

# Дополнительные команды
migrate:
	$(COMPOSE) exec php-fpm php artisan migrate

migrate-fresh:
	$(COMPOSE) exec php-fpm php artisan migrate:fresh --seed

tinker:
	$(COMPOSE) exec php-fpm php artisan tinker

queue:
	$(COMPOSE) exec php-fpm php artisan queue:work

horizon:
	$(COMPOSE) exec php-fpm php artisan horizon

clear-cache:
	$(COMPOSE) exec php-fpm php artisan optimize:clear

storage-link:
	$(COMPOSE) exec php-fpm php artisan storage:link

key-generate:
	$(COMPOSE) exec php-fpm php artisan key:generate

# Запуск в разных окружениях
up-dev:
	ENV=dev $(MAKE) up

up-test:
	ENV=test $(MAKE) up

up-prod:
	ENV=production $(MAKE) up
