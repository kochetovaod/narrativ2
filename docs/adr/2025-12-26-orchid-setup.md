# ADR: Настройка панели администрирования Orchid

## Контекст

В проект добавлена библиотека [Orchid Platform](https://orchid.software) для построения административной панели. Требуется опубликовать конфигурацию, подключить экраны, маршруты и права доступа, а также создать пользователя-администратора.

## Решение

1. Опубликована конфигурация Orchid в `config/platform.php` с указанием префикса (`ORCHID_PREFIX`, по умолчанию `admin`), домена (`ORCHID_DOMAIN`), middleware и параметров аутентификации.
2. Добавлен `App\Orchid\PlatformProvider`, регистрирующий меню панели, базовые права (`platform.systems.users`, `platform.systems.roles`) и ссылку на главный экран `DashboardScreen`.
3. Создан файл маршрутов `routes/platform.php` с подключением экрана панели и встроенных экранов пользователей/ролей.
4. Добавлены миграции `roles`, `role_users`, а также поля `permissions` и `last_login_at` в таблицу `users`.
5. Пользовательская модель расширена от `Orchid\Platform\Models\User` и подготовлена фабрика с пустыми правами по умолчанию.
6. Создан экран `DashboardScreen` и представление `resources/views/platform/dashboard.blade.php` для стартовой страницы панели.
7. Создан сидер `AdminUserSeeder`, который поднимает роль `admin` и пользователя с правами администратора.

## Использование

1. Установить зависимости и опубликовать ресурсы Orchid:

   ```bash
   composer install
   php artisan vendor:publish --provider="Orchid\\Platform\\Providers\\OrchidServiceProvider"
   ```

2. Выполнить миграции и сидирование администратора:

   ```bash
   php artisan migrate
   php artisan db:seed --class=Database\\Seeders\\AdminUserSeeder
   ```

   Данные администратора берутся из переменных окружения:

   - `ORCHID_ADMIN_EMAIL` (по умолчанию `admin@example.com`)
   - `ORCHID_ADMIN_PASSWORD` (по умолчанию `password`)
   - `ORCHID_ADMIN_NAME` (по умолчанию `Administrator`)

3. Перейти в панель по адресу `/{ORCHID_PREFIX}` (по умолчанию `/admin`), авторизоваться указанными учетными данными и управлять пользователями/ролями через меню.

