<?php

namespace Database\Seeders;

use App\Models\Menu;
use App\Models\MenuItem;
use Illuminate\Database\Seeder;

class MenuSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Создаем базовые меню если их нет
        $headerMenu = Menu::firstOrCreate(
            ['code' => 'header'],
            ['title' => 'Верхнее меню']
        );

        $footerMenu = Menu::firstOrCreate(
            ['code' => 'footer'],
            ['title' => 'Нижнее меню']
        );

        // Заполняем верхнее меню
        $this->createHeaderMenuItems($headerMenu);

        // Заполняем нижнее меню
        $this->createFooterMenuItems($footerMenu);
    }

    /**
     * Создает элементы верхнего меню
     */
    private function createHeaderMenuItems(Menu $menu): void
    {
        // Проверяем, есть ли уже элементы в меню
        if ($menu->items()->count() > 0) {
            return; // Если есть, то не создаем новые
        }

        // Создаем основные элементы меню
        $homeItem = MenuItem::create([
            'menu_id' => $menu->id,
            'title' => 'Главная',
            'url' => '/',
            'sort' => 0,
            'is_visible' => true,
        ]);

        $productsItem = MenuItem::create([
            'menu_id' => $menu->id,
            'title' => 'Продукция',
            'url' => '/products',
            'sort' => 1,
            'is_visible' => true,
        ]);

        $servicesItem = MenuItem::create([
            'menu_id' => $menu->id,
            'title' => 'Услуги',
            'url' => '/services',
            'sort' => 2,
            'is_visible' => true,
        ]);

        $portfolioItem = MenuItem::create([
            'menu_id' => $menu->id,
            'title' => 'Портфолио',
            'url' => '/portfolio',
            'sort' => 3,
            'is_visible' => true,
        ]);

        $newsItem = MenuItem::create([
            'menu_id' => $menu->id,
            'title' => 'Новости',
            'url' => '/news',
            'sort' => 4,
            'is_visible' => true,
        ]);

        $aboutItem = MenuItem::create([
            'menu_id' => $menu->id,
            'title' => 'О компании',
            'url' => '/about',
            'sort' => 5,
            'is_visible' => true,
        ]);

        $contactItem = MenuItem::create([
            'menu_id' => $menu->id,
            'title' => 'Контакты',
            'url' => '/contacts',
            'sort' => 6,
            'is_visible' => true,
        ]);
    }

    /**
     * Создает элементы нижнего меню
     */
    private function createFooterMenuItems(Menu $menu): void
    {
        // Проверяем, есть ли уже элементы в меню
        if ($menu->items()->count() > 0) {
            return; // Если есть, то не создаем новые
        }

        // Создаем элементы нижнего меню
        $legalItems = [
            ['title' => 'Политика конфиденциальности', 'url' => '/privacy'],
            ['title' => 'Условия использования', 'url' => '/terms'],
            ['title' => 'Согласие на обработку ПДн', 'url' => '/consent'],
            ['title' => 'Политика cookie', 'url' => '/cookies'],
        ];

        $contactItems = [
            ['title' => 'Контакты', 'url' => '/contacts'],
            ['title' => 'Карта сайта', 'url' => '/sitemap'],
        ];

        // Создаем группу "Правовая информация"
        $legalGroup = MenuItem::create([
            'menu_id' => $menu->id,
            'title' => 'Правовая информация',
            'url' => '#',
            'sort' => 0,
            'is_visible' => true,
        ]);

        foreach ($legalItems as $index => $item) {
            MenuItem::create([
                'menu_id' => $menu->id,
                'parent_id' => $legalGroup->id,
                'title' => $item['title'],
                'url' => $item['url'],
                'sort' => $index,
                'is_visible' => true,
            ]);
        }

        // Создаем группу "Контакты"
        $contactGroup = MenuItem::create([
            'menu_id' => $menu->id,
            'title' => 'Контакты',
            'url' => '#',
            'sort' => 1,
            'is_visible' => true,
        ]);

        foreach ($contactItems as $index => $item) {
            MenuItem::create([
                'menu_id' => $menu->id,
                'parent_id' => $contactGroup->id,
                'title' => $item['title'],
                'url' => $item['url'],
                'sort' => $index,
                'is_visible' => true,
            ]);
        }

        // Создаем копию основных разделов в нижнем меню
        $footerProducts = MenuItem::create([
            'menu_id' => $menu->id,
            'title' => 'Продукция',
            'url' => '/products',
            'sort' => 2,
            'is_visible' => true,
        ]);

        $footerServices = MenuItem::create([
            'menu_id' => $menu->id,
            'title' => 'Услуги',
            'url' => '/services',
            'sort' => 3,
            'is_visible' => true,
        ]);

        $footerPortfolio = MenuItem::create([
            'menu_id' => $menu->id,
            'title' => 'Портфолио',
            'url' => '/portfolio',
            'sort' => 4,
            'is_visible' => true,
        ]);
    }
}
