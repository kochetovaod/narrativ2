<?php

declare(strict_types=1);

namespace App\Orchid\Screens\Menu;

use App\Models\Menu;
use App\Models\MenuItem;
use App\Models\Page;
use App\Models\ProductCategory;
use App\Models\Service;
use Illuminate\Http\Request;
use Orchid\Screen\Actions\Button;
use Orchid\Screen\Actions\Link;
use Orchid\Screen\Screen;
use Orchid\Support\Facades\Alert;
use Orchid\Support\Facades\Layout;

class MenuEditScreen extends Screen
{
    public string $menuCode;

    /**
     * @var string|array<int, string>
     */
    public $permission = 'platform.menu';

    /**
     * Fetch the menu data for editing.
     *
     * @return array<string, Menu|array>
     */
    public function query(string $menuCode): iterable
    {
        $this->menuCode = $menuCode;

        $menu = Menu::where('code', $menuCode)
            ->with(['items' => function ($query) {
                $query->orderBy('sort');
            }, 'items.children' => function ($query) {
                $query->orderBy('sort');
            }])
            ->first();

        if (! $menu) {
            // Создаем меню если его нет
            $menu = Menu::create([
                'code' => $menuCode,
                'title' => $menuCode === 'header' ? 'Верхнее меню' : 'Нижнее меню',
            ]);
        }

        return [
            'menu' => $menu,
            'pages' => Page::where('status', 'published')->select('id', 'title', 'slug')->get(),
            'services' => Service::where('status', 'published')->select('id', 'title', 'slug')->get(),
            'productCategories' => ProductCategory::where('status', 'published')->select('id', 'title', 'slug')->get(),
        ];
    }

    public function name(): ?string
    {
        $menuTitle = $this->menuCode === 'header' ? 'Верхнее меню' : 'Нижнее меню';

        return __('Редактирование: ').$menuTitle;
    }

    public function description(): ?string
    {
        return __('Управление элементами меню с поддержкой вложенности');
    }

    public function commandBar(): iterable
    {
        return [
            Button::make(__('Сохранить'))
                ->icon('check')
                ->method('save'),

            Link::make(__('Назад к списку меню'))
                ->icon('action-undo')
                ->route('platform.systems.menu'),
        ];
    }

    public function layout(): iterable
    {
        return [
            Layout::view('orchid.menu.edit'),
        ];
    }

    public function save(Request $request, string $menuCode): void
    {
        $menu = Menu::where('code', $menuCode)->firstOrFail();

        // Валидация и сохранение элементов меню
        $itemsData = $request->input('menu_items', []);
        $this->saveMenuItems($menu, $itemsData);

        Alert::success(__('Меню сохранено'));

        $this->redirect(route('platform.systems.menu'));
    }

    private function saveMenuItems(Menu $menu, array $itemsData, ?int $parentId = null): void
    {
        foreach ($itemsData as $itemData) {
            $itemId = $itemData['id'] ?? null;
            $sort = (int) ($itemData['sort'] ?? 0);
            $isVisible = (bool) ($itemData['is_visible'] ?? true);

            // Создаем или обновляем элемент меню
            $menuItem = $itemId
                ? MenuItem::where('id', $itemId)->where('menu_id', $menu->id)->firstOrFail()
                : new MenuItem;

            $menuItem->fill([
                'menu_id' => $menu->id,
                'parent_id' => $parentId,
                'title' => $itemData['title'] ?? '',
                'url' => $itemData['url'] ?? '',
                'entity_type' => $itemData['entity_type'] ?? null,
                'entity_id' => $itemData['entity_id'] ?? null,
                'sort' => $sort,
                'is_visible' => $isVisible,
            ]);

            $menuItem->save();

            // Рекурсивно сохраняем дочерние элементы
            if (! empty($itemData['children'])) {
                $this->saveMenuItems($menu, $itemData['children'], $menuItem->id);
            }
        }
    }

    public function addItem(Request $request): void
    {
        $menuCode = $request->input('menu_code');
        $parentId = $request->input('parent_id');
        $itemType = $request->input('item_type');
        $entityId = $request->input('entity_id');

        $menu = Menu::where('code', $menuCode)->firstOrFail();

        // Получаем данные для нового элемента
        $itemData = $this->getItemData($itemType, $entityId);

        if (! $itemData) {
            Alert::error(__('Не удалось получить данные элемента'));

            return;
        }

        // Подсчитываем текущий сортировочный индекс
        $maxSort = MenuItem::where('menu_id', $menu->id)
            ->where('parent_id', $parentId)
            ->max('sort') ?? -1;

        $menuItem = MenuItem::create([
            'menu_id' => $menu->id,
            'parent_id' => $parentId,
            'title' => $itemData['title'],
            'url' => $itemData['url'],
            'entity_type' => $itemData['entity_type'],
            'entity_id' => $itemData['entity_id'],
            'sort' => $maxSort + 1,
            'is_visible' => true,
        ]);

        Alert::success(__('Элемент меню добавлен'));

        $this->redirect(route('platform.systems.menu.edit', $menuCode));
    }

    public function deleteItem(Request $request): void
    {
        $itemId = $request->input('item_id');

        $menuItem = MenuItem::findOrFail($itemId);
        $menuCode = $menuItem->menu->code;

        $menuItem->delete();

        Alert::success(__('Элемент меню удален'));

        $this->redirect(route('platform.systems.menu.edit', $menuCode));
    }

    public function reorderItems(Request $request): void
    {
        $menuCode = $request->input('menu_code');
        $orders = $request->input('orders', []);

        foreach ($orders as $orderData) {
            $itemId = $orderData['id'] ?? null;
            $newSort = (int) ($orderData['sort'] ?? 0);
            $parentId = $orderData['parent_id'] ?? null;

            if ($itemId) {
                MenuItem::where('id', $itemId)->update([
                    'sort' => $newSort,
                    'parent_id' => $parentId,
                ]);
            }
        }

        Alert::success(__('Порядок элементов обновлен'));

        $this->redirect(route('platform.systems.menu.edit', $menuCode));
    }

    private function getItemData(string $itemType, ?string $entityId): ?array
    {
        return match ($itemType) {
            'page' => $this->getPageData($entityId),
            'service' => $this->getServiceData($entityId),
            'product_category' => $this->getProductCategoryData($entityId),
            'custom_url' => ['title' => 'Новая ссылка', 'url' => '/', 'entity_type' => null, 'entity_id' => null],
            default => null,
        };
    }

    private function getPageData(?string $entityId): ?array
    {
        if (! $entityId) {
            return null;
        }

        $page = Page::find($entityId);
        if (! $page || $page->status !== 'published') {
            return null;
        }

        return [
            'title' => $page->title,
            'url' => route('pages.show', ['pageSlug' => $page->slug], absolute: false),
            'entity_type' => 'page',
            'entity_id' => $page->id,
        ];
    }

    private function getServiceData(?string $entityId): ?array
    {
        if (! $entityId) {
            return null;
        }

        $service = Service::find($entityId);
        if (! $service || $service->status !== 'published') {
            return null;
        }

        return [
            'title' => $service->title,
            'url' => route('services.show', ['serviceSlug' => $service->slug], absolute: false),
            'entity_type' => 'service',
            'entity_id' => $service->id,
        ];
    }

    private function getProductCategoryData(?string $entityId): ?array
    {
        if (! $entityId) {
            return null;
        }

        $category = ProductCategory::find($entityId);
        if (! $category || $category->status !== 'published') {
            return null;
        }

        return [
            'title' => $category->title,
            'url' => route('products.category', ['categorySlug' => $category->slug], absolute: false),
            'entity_type' => 'product_category',
            'entity_id' => $category->id,
        ];
    }
}
