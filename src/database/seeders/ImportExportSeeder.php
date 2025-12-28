<?php

namespace Database\Seeders;

use App\Models\Lead;
use App\Models\LeadDedupIndex;
use App\Models\PortfolioCase;
use App\Models\Product;
use App\Models\ProductCategory;
use App\Models\Service;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class ImportExportSeeder extends Seeder
{
    public function run(): void
    {
        $this->seedProductCategories();
        $this->seedProducts();
        $this->seedServices();
        $this->seedPortfolioCases();
        $this->seedLeads();
    }

    private function seedProductCategories(): void
    {
        $categories = [
            [
                'title' => 'Оборудование',
                'slug' => 'equipment',
                'intro_text' => 'Промышленное оборудование',
                'body' => [
                    'blocks' => [
                        [
                            'type' => 'text',
                            'content' => 'Описание категории оборудования',
                        ],
                    ],
                ],
                'status' => 'published',
                'seo' => [
                    'title' => 'Промышленное оборудование',
                    'description' => 'Промышленное оборудование для производства',
                    'h1' => 'Промышленное оборудование',
                ],
                'published_at' => now(),
            ],
            [
                'title' => 'Компоненты',
                'slug' => 'components',
                'intro_text' => 'Запасные части и компоненты',
                'body' => [
                    'blocks' => [
                        [
                            'type' => 'text',
                            'content' => 'Описание категории компонентов',
                        ],
                    ],
                ],
                'status' => 'published',
                'seo' => [
                    'title' => 'Запасные части и компоненты',
                    'description' => 'Запасные части для оборудования',
                    'h1' => 'Запасные части и компоненты',
                ],
                'published_at' => now(),
            ],
        ];

        foreach ($categories as $categoryData) {
            $categoryData['preview_token'] = Str::orderedUuid()->toString();
            ProductCategory::updateOrCreate(
                ['slug' => $categoryData['slug']],
                $categoryData
            );
        }
    }

    private function seedProducts(): void
    {
        $products = [
            [
                'title' => 'Промышленный станок',
                'slug' => 'industrial-machine',
                'description' => 'Высокоточный промышленный станок для обработки металла',
                'specs' => [
                    'power' => '5 кВт',
                    'voltage' => '380В',
                    'weight' => '1500 кг',
                    'dimensions' => '2000x1500x1800 мм',
                ],
                'category_id' => 1,
                'status' => 'published',
                'seo' => [
                    'title' => 'Промышленный станок',
                    'description' => 'Высокоточный промышленный станок',
                    'h1' => 'Промышленный станок',
                ],
                'published_at' => now(),
            ],
            [
                'title' => 'Запасная часть A1',
                'slug' => 'spare-part-a1',
                'description' => 'Запасная часть A1 для промышленного оборудования',
                'specs' => [
                    'material' => 'сталь',
                    'weight' => '2.5 кг',
                    'warranty' => '12 месяцев',
                ],
                'category_id' => 2,
                'status' => 'published',
                'seo' => [
                    'title' => 'Запасная часть A1',
                    'description' => 'Запасная часть A1',
                    'h1' => 'Запасная часть A1',
                ],
                'published_at' => now(),
            ],
        ];

        foreach ($products as $productData) {
            $productData['preview_token'] = Str::orderedUuid()->toString();
            Product::updateOrCreate(
                ['slug' => $productData['slug']],
                $productData
            );
        }
    }

    private function seedServices(): void
    {
        $services = [
            [
                'title' => 'Ремонт оборудования',
                'slug' => 'equipment-repair',
                'content' => [
                    'blocks' => [
                        [
                            'type' => 'text',
                            'content' => 'Профессиональный ремонт промышленного оборудования',
                        ],
                        [
                            'type' => 'list',
                            'items' => [
                                'Диагностика неисправностей',
                                'Замена изношенных деталей',
                                'Настройка и калибровка',
                                'Гарантия на выполненные работы',
                            ],
                        ],
                    ],
                ],
                'show_cases' => true,
                'status' => 'published',
                'seo' => [
                    'title' => 'Ремонт оборудования',
                    'description' => 'Профессиональный ремонт промышленного оборудования',
                    'h1' => 'Ремонт оборудования',
                ],
                'published_at' => now(),
            ],
            [
                'title' => 'Техническое обслуживание',
                'slug' => 'maintenance',
                'content' => [
                    'blocks' => [
                        [
                            'type' => 'text',
                            'content' => 'Регулярное техническое обслуживание оборудования',
                        ],
                    ],
                ],
                'show_cases' => false,
                'status' => 'published',
                'seo' => [
                    'title' => 'Техническое обслуживание',
                    'description' => 'Регулярное техническое обслуживание',
                    'h1' => 'Техническое обслуживание',
                ],
                'published_at' => now(),
            ],
        ];

        foreach ($services as $serviceData) {
            $serviceData['preview_token'] = Str::orderedUuid()->toString();
            Service::updateOrCreate(
                ['slug' => $serviceData['slug']],
                $serviceData
            );
        }
    }

    private function seedPortfolioCases(): void
    {
        $cases = [
            [
                'title' => 'Модернизация завода',
                'slug' => 'factory-modernization',
                'description' => 'Комплексная модернизация производственного завода',
                'client_name' => 'ООО ПромТех',
                'is_nda' => false,
                'public_client_label' => 'Крупный промышленный завод',
                'date' => '2024-12-01',
                'status' => 'published',
                'seo' => [
                    'title' => 'Модернизация завода',
                    'description' => 'Комплексная модернизация производственного завода',
                    'h1' => 'Модернизация завода',
                ],
                'published_at' => now(),
            ],
            [
                'title' => 'Установка нового оборудования',
                'slug' => 'new-equipment-installation',
                'description' => 'Установка и наладка нового производственного оборудования',
                'client_name' => 'ООО МашСтрой',
                'is_nda' => true,
                'public_client_label' => 'Строительная компания',
                'date' => '2024-11-15',
                'status' => 'published',
                'seo' => [
                    'title' => 'Установка нового оборудования',
                    'description' => 'Установка и наладка нового оборудования',
                    'h1' => 'Установка нового оборудования',
                ],
                'published_at' => now(),
            ],
        ];

        foreach ($cases as $caseData) {
            $caseData['preview_token'] = Str::orderedUuid()->toString();
            PortfolioCase::updateOrCreate(
                ['slug' => $caseData['slug']],
                $caseData
            );
        }
    }

    private function seedLeads(): void
    {
        $leads = [
            [
                'form_code' => 'question',
                'status' => 'new',
                'phone' => '+79001234567',
                'email' => 'client1@company.ru',
                'payload' => [
                    'name' => 'Иван Петров',
                    'company' => 'ООО ТехПром',
                    'message' => 'Интересует ремонт станка',
                ],
                'source_url' => 'https://site.ru/contact',
                'page_title' => 'Контакты',
                'utm' => [
                    'source' => 'google',
                    'medium' => 'cpc',
                    'campaign' => 'repair-services',
                ],
                'consent_given' => true,
                'consent_doc_url' => 'https://site.ru/privacy',
                'consent_at' => now(),
                'created_at' => now()->subDays(5),
            ],
            [
                'form_code' => 'callback',
                'status' => 'in_progress',
                'phone' => '+79007654321',
                'email' => 'client2@factory.ru',
                'payload' => [
                    'name' => 'Мария Сидорова',
                    'callback_time' => '14:00',
                    'preferred_contact' => 'phone',
                ],
                'source_url' => 'https://site.ru/',
                'page_title' => 'Главная страница',
                'utm' => [
                    'source' => 'yandex',
                    'medium' => 'cpc',
                    'campaign' => 'industrial-equipment',
                ],
                'consent_given' => true,
                'consent_doc_url' => 'https://site.ru/privacy',
                'consent_at' => now(),
                'created_at' => now()->subDays(3),
            ],
            [
                'form_code' => 'calc',
                'status' => 'closed',
                'phone' => '+79005555555',
                'email' => 'client3@manufacturing.ru',
                'payload' => [
                    'name' => 'Алексей Козлов',
                    'company' => 'ООО Производство',
                    'budget' => '1000000',
                    'timeline' => '6 месяцев',
                ],
                'source_url' => 'https://site.ru/uslugi',
                'page_title' => 'Услуги',
                'utm' => [
                    'source' => 'direct',
                    'medium' => 'none',
                ],
                'consent_given' => true,
                'consent_doc_url' => 'https://site.ru/privacy',
                'consent_at' => now(),
                'manager_comment' => 'Успешно закрыт заказ на 1 млн рублей',
                'created_at' => now()->subDays(1),
            ],
        ];

        foreach ($leads as $leadData) {
            $lead = Lead::updateOrCreate(
                ['email' => $leadData['email']],
                $leadData
            );

            // Создаем запись дедупликации
            LeadDedupIndex::updateOrCreate(
                ['lead_id' => $lead->id],
                [
                    'email_hash' => hash('sha256', $lead->email),
                    'phone_hash' => hash('sha256', $lead->phone),
                    'created_at' => now(),
                ]
            );
        }
    }
}
