#!/bin/bash

set -e

BACKUP_DIR="/backups"
TIMESTAMP=$(date +%Y%m%d_%H%M%S)

echo "💾 Начало создания бэкапа..."

# Создаем бэкап базы данных
echo "📦 Создание бэкапа базы данных..."
docker-compose exec -T postgres pg_dump -U laravel laravel > "${BACKUP_DIR}/db_${TIMESTAMP}.sql"

# Создаем бэкап медиа файлов
echo "📁 Создание бэкапа медиа файлов..."
tar -czf "${BACKUP_DIR}/media_${TIMESTAMP}.tar.gz" -C src/storage/app/public .

# Создаем бэкап логов
echo "📋 Создание бэкапа логов..."
tar -czf "${BACKUP_DIR}/logs_${TIMESTAMP}.tar.gz" -C src/storage/logs .

# Очищаем старые бэкапы (храним 7 дней)
echo "🧹 Очистка старых бэкапов..."
find "${BACKUP_DIR}" -name "*.sql" -mtime +7 -delete
find "${BACKUP_DIR}" -name "*.tar.gz" -mtime +7 -delete

echo "✅ Бэкап создан:"
echo "   • База данных: ${BACKUP_DIR}/db_${TIMESTAMP}.sql"
echo "   • Медиа файлы: ${BACKUP_DIR}/media_${TIMESTAMP}.tar.gz"
echo "   • Логи:        ${BACKUP_DIR}/logs_${TIMESTAMP}.tar.gz"