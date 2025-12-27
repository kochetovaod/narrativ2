<?php

namespace App\Orchid\Permissions;

use Illuminate\Support\Collection;

class Rbac
{
    public const ROLE_SUPER_ADMIN = 'super_admin';

    public const ROLE_ADMIN = 'admin';

    public const ROLE_CONTENT_MANAGER = 'content_manager';

    public const PERMISSION_ACCESS = 'platform.index';

    public const PERMISSION_CONTENT = 'platform.content';

    public const PERMISSION_FORMS = 'platform.forms';

    public const PERMISSION_MENU = 'platform.menu';

    public const PERMISSION_PAGE_BUILDER = 'platform.page_builder';

    public const PERMISSION_SEO = 'platform.seo';

    public const PERMISSION_IMPORTS = 'platform.imports';

    public const PERMISSION_AUDIT = 'platform.audit';

    public const PERMISSION_SYSTEM_SETTINGS = 'platform.systems.settings';

    public const PERMISSION_ROLES = 'platform.systems.roles';

    public const PERMISSION_USERS = 'platform.systems.users';

    public const PERMISSION_PRODUCT_CATEGORIES = 'platform.systems.product_categories';

    public const PERMISSION_PRODUCTS = 'platform.systems.products';

    public const PERMISSION_SERVICES = 'platform.systems.services';

    public const PERMISSION_PORTFOLIO_CASES = 'platform.systems.portfolio_cases';

    public const PERMISSION_NEWS_POSTS = 'platform.systems.news_posts';

    public const PERMISSION_PAGES = 'platform.systems.pages';

    /**
     * Доступные группы прав и их ключи.
     *
     * @return array<string, array<string, string>>
     */
    public static function permissionGroups(): array
    {
        return [
            'Навигация' => [
                self::PERMISSION_ACCESS => 'Доступ в панель управления',
            ],
            'Контент' => [
                self::PERMISSION_CONTENT => 'Контент и медиа',
                self::PERMISSION_PAGE_BUILDER => 'Page Builder',
                self::PERMISSION_MENU => 'Меню и навигация',
                self::PERMISSION_PRODUCT_CATEGORIES => 'Категории продукции',
                self::PERMISSION_PRODUCTS => 'Товары',
                self::PERMISSION_SERVICES => 'Услуги',
                self::PERMISSION_PORTFOLIO_CASES => 'Портфолио',
                self::PERMISSION_NEWS_POSTS => 'Новости',
                self::PERMISSION_PAGES => 'Страницы',
            ],
            'Заявки и коммуникации' => [
                self::PERMISSION_FORMS => 'Формы и заявки',
            ],
            'Маркетинг и интеграции' => [
                self::PERMISSION_SEO => 'SEO и метаданные',
                self::PERMISSION_IMPORTS => 'Импорт/экспорт',
            ],
            'Мониторинг' => [
                self::PERMISSION_AUDIT => 'Лог аудита и действия',
            ],
            'Система' => [
                self::PERMISSION_ROLES => 'Управление ролями',
                self::PERMISSION_USERS => 'Управление пользователями',
                self::PERMISSION_SYSTEM_SETTINGS => 'Системные настройки',
            ],
        ];
    }

    /**
     * Предустановленные роли и их права.
     *
     * @return array<string, array{name: string, permissions: array<string, bool>}>
     */
    public static function rolePresets(): array
    {
        $allPermissions = array_fill_keys(self::allPermissions(), true);

        return [
            self::ROLE_SUPER_ADMIN => [
                'name' => 'Супер-админ',
                'permissions' => $allPermissions,
            ],
            self::ROLE_ADMIN => [
                'name' => 'Админ',
                'permissions' => array_fill_keys([
                    self::PERMISSION_ACCESS,
                    self::PERMISSION_CONTENT,
                    self::PERMISSION_PAGE_BUILDER,
                    self::PERMISSION_MENU,
                    self::PERMISSION_FORMS,
                    self::PERMISSION_SEO,
                    self::PERMISSION_IMPORTS,
                    self::PERMISSION_AUDIT,
                    self::PERMISSION_PRODUCT_CATEGORIES,
                    self::PERMISSION_PRODUCTS,
                    self::PERMISSION_SERVICES,
                    self::PERMISSION_PORTFOLIO_CASES,
                    self::PERMISSION_NEWS_POSTS,
                    self::PERMISSION_PAGES,
                ], true),
            ],
            self::ROLE_CONTENT_MANAGER => [
                'name' => 'Контент-менеджер',
                'permissions' => array_fill_keys([
                    self::PERMISSION_ACCESS,
                    self::PERMISSION_CONTENT,
                    self::PERMISSION_PAGE_BUILDER,
                    self::PERMISSION_MENU,
                    self::PERMISSION_PRODUCT_CATEGORIES,
                    self::PERMISSION_PRODUCTS,
                    self::PERMISSION_SERVICES,
                    self::PERMISSION_PORTFOLIO_CASES,
                    self::PERMISSION_NEWS_POSTS,
                    self::PERMISSION_PAGES,
                ], true),
            ],
        ];
    }

    /**
     * Список ключей всех прав.
     *
     * @return array<int, string>
     */
    public static function allPermissions(): array
    {
        return self::permissionCollection()
            ->flatMap(fn (array $permissions): array => array_keys($permissions))
            ->values()
            ->all();
    }

    /**
     * Список системных ролей, которые следует защищать от удаления.
     *
     * @return array<int, string>
     */
    public static function protectedRoles(): array
    {
        return [
            self::ROLE_SUPER_ADMIN,
            self::ROLE_ADMIN,
            self::ROLE_CONTENT_MANAGER,
        ];
    }

    /**
     * Коллекция групп прав.
     */
    protected static function permissionCollection(): Collection
    {
        return collect(self::permissionGroups());
    }
}
