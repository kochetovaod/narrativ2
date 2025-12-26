#!/bin/bash

set -e

echo "🔍 Проверка GitHub Actions workflows..."

# Проверяем синтаксис YAML файлов
echo "1. Проверка синтаксиса YAML..."
yamllint .github/workflows/*.yml

# Проверяем actionlint
echo "2. Проверка actionlint..."
if command -v actionlint &> /dev/null; then
    actionlint
else
    echo "⚠️  actionlint не установлен, пропускаем проверку"
fi

# Проверяем зависимости
echo "3. Проверка зависимостей..."
if [ -f src/composer.json ]; then
    composer validate --working-dir=src --strict
fi

echo "✅ Все проверки пройдены!"
