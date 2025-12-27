<?php

namespace Database\Seeders;

use App\Models\Form;
use App\Models\FormField;
use App\Models\FormPlacement;
use App\Models\GlobalBlock;
use App\Models\Lead;
use App\Models\LeadDedupIndex;
use App\Models\MediaFile;
use App\Models\MediaLink;
use App\Models\Menu;
use App\Models\MenuItem;
use App\Models\NewsPost;
use App\Models\Page;
use App\Models\PortfolioCase;
use App\Models\Product;
use App\Models\ProductCategory;
use App\Models\Redirect;
use App\Models\SeoTemplate;
use App\Models\Service;
use App\Models\TrackingEvent;
use Illuminate\Database\Seeder;
use Illuminate\Support\Collection;

class ContentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->seedNavigation();

        $categories = ProductCategory::factory(3)
            ->has(Product::factory(3))
            ->create();

        $products = Product::query()->get();
        $services = Service::factory(3)->create();

        $cases = PortfolioCase::factory(4)->create();
        $cases->each(function (PortfolioCase $case) use ($products, $services): void {
            $case->products()->sync($products->random(2)->pluck('id'));
            $case->services()->sync($services->random(2)->pluck('id'));
        });

        $pages = Page::factory()
            ->count(3)
            ->sequence(
                ['code' => 'home', 'slug' => 'home'],
                ['code' => 'production', 'slug' => 'production'],
                ['code' => 'contacts', 'slug' => 'kontakty'],
            )
            ->create();

        $globalBlocks = GlobalBlock::factory(2)->create();
        $news = NewsPost::factory(5)->create();

        $forms = $this->seedForms();
        $this->seedLeads($forms);

        TrackingEvent::factory(10)->create();
        SeoTemplate::factory(6)->create();
        Redirect::factory(3)->create();

        $this->seedMedia($products, $pages, $news, $globalBlocks);
    }

    private function seedNavigation(): void
    {
        $header = Menu::factory()->state(['code' => 'header', 'title' => 'Главное меню'])->create();
        $footer = Menu::factory()->state(['code' => 'footer', 'title' => 'Меню в подвале'])->create();

        $topItems = MenuItem::factory(3)
            ->for($header)
            ->sequence(
                ['title' => 'Продукция', 'url' => route('products.index', absolute: false)],
                ['title' => 'Услуги', 'url' => route('services.index', absolute: false)],
                ['title' => 'Контакты', 'url' => route('contacts', absolute: false)],
            )
            ->create();

        MenuItem::factory(2)
            ->for($footer)
            ->sequence(
                ['title' => 'Новости', 'url' => route('news.index', absolute: false)],
                ['title' => 'Политика конфиденциальности', 'url' => route('documents.privacy', absolute: false)],
            )
            ->create();

        MenuItem::factory()
            ->for($header)
            ->for($topItems->first(), 'parent')
            ->state(['title' => 'Портфолио', 'url' => '/portfolio', 'sort' => 5])
            ->create();
    }

    /**
     * @return Collection<int, Form>
     */
    private function seedForms(): Collection
    {
        $forms = collect([
            Form::factory()->state(['code' => 'callback', 'title' => 'Обратный звонок'])->create(),
            Form::factory()->state(['code' => 'calc', 'title' => 'Калькулятор заявки'])->create(),
            Form::factory()->state(['code' => 'question', 'title' => 'Вопрос менеджеру'])->create(),
        ]);

        $forms->each(function (Form $form, int $index): void {
            FormField::factory(3)
                ->for($form)
                ->sequence(
                    ['key' => 'name', 'label' => 'Имя', 'type' => 'text', 'sort' => 1, 'options' => null],
                    ['key' => 'phone', 'label' => 'Телефон', 'type' => 'phone', 'sort' => 2, 'options' => null],
                    ['key' => 'message', 'label' => 'Сообщение', 'type' => 'textarea', 'sort' => 3, 'options' => null],
                )
                ->create();

            FormPlacement::factory()
                ->for($form)
                ->state([
                    'entity_type' => $index % 2 === 0 ? 'Product' : 'Page',
                    'entity_id' => $index + 1,
                    'placement' => 'inline',
                ])
                ->create();
        });

        return $forms;
    }

    /**
     * @param  Collection<int, Form>  $forms
     */
    private function seedLeads(Collection $forms): void
    {
        $formCodes = $forms->pluck('code');

        $leads = Lead::factory(5)
            ->state(fn () => ['form_code' => $formCodes->random()])
            ->create();

        $leads->each(function (Lead $lead): void {
            LeadDedupIndex::factory()
                ->for($lead)
                ->state([
                    'contact_key' => $lead->email ? strtolower($lead->email) : preg_replace('/\D+/', '', (string) $lead->phone),
                    'created_date' => $lead->created_at->toDateString(),
                ])
                ->create();
        });
    }

    private function seedMedia(Collection $products, Collection $pages, Collection $news, Collection $globalBlocks): void
    {
        $files = MediaFile::factory(5)->create();

        $targets = collect()
            ->concat($products)
            ->concat($pages)
            ->concat($news)
            ->concat($globalBlocks);

        $targets->each(function ($target) use ($files): void {
            $file = $files->random();

            MediaLink::factory()
                ->state([
                    'entity_type' => $target::class,
                    'entity_id' => $target->id,
                    'media_id' => $file->id,
                ])
                ->create();
        });
    }
}
