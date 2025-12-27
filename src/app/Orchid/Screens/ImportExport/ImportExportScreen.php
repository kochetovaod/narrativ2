<?php

namespace App\Orchid\Screens\ImportExport;

use App\Models\ImportLog;
use App\Services\ImportExport\CSVExporter;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Orchid\Screen\Actions\Link;
use Orchid\Screen\Screen;
use Orchid\Screen\TD;
use Orchid\Support\Facades\Layout;
use Orchid\Support\Facades\Toast;

class ImportExportScreen extends Screen
{
    public $name = 'Импорт/Экспорт';

    public $description = 'Управление импортом и экспортом данных';

    public function query(): array
    {
        return [
            'logs' => ImportLog::latest()
                ->where('user_id', Auth::id())
                ->paginate(20),
            'recent_exports' => ImportLog::latest()
                ->where('operation_type', 'export')
                ->where('user_id', Auth::id())
                ->limit(5)
                ->get(),
        ];
    }

    public function commandBar(): array
    {
        return [];
    }

    public function layout(): array
    {
        return [
            Layout::tabs([
                __('Импорт') => $this->importLayout(),
                __('Экспорт') => $this->exportLayout(),
                __('Логи') => $this->logsLayout(),
            ]),
        ];
    }

    /**
     * Layout для импорта
     */
    private function importLayout(): array
    {
        return [
            Layout::rows([
                \Orchid\Screen\Fields\Select::make('entity_type')
                    ->title('Тип сущности')
                    ->options([
                        'products' => 'Товары',
                        'services' => 'Услуги',
                        'portfolio_cases' => 'Кейсы портфолио',
                        'leads' => 'Заявки',
                        'product_categories' => 'Категории товаров',
                    ])
                    ->required(),

                \Orchid\Screen\Fields\Select::make('operation_type')
                    ->title('Режим импорта')
                    ->options([
                        'create' => 'Создать (только новые)',
                        'update' => 'Обновить (только существующие)',
                        'upsert' => 'Создать/Обновить',
                    ])
                    ->required(),

                \Orchid\Screen\Fields\Input::make('csv_file')
                    ->type('file')
                    ->title('CSV файл')
                    ->required()
                    ->help('Выберите CSV файл для импорта'),
            ]),
        ];
    }

    /**
     * Layout для экспорта
     */
    private function exportLayout(): array
    {
        return [
            Layout::rows([
                \Orchid\Screen\Fields\Select::make('entity_type')
                    ->title('Тип сущности')
                    ->options([
                        'all' => 'Все типы (массовый экспорт)',
                        'products' => 'Товары',
                        'services' => 'Услуги',
                        'portfolio_cases' => 'Кейсы портфолио',
                        'leads' => 'Заявки',
                        'product_categories' => 'Категории товаров',
                    ])
                    ->required(),

                \Orchid\Screen\Fields\Select::make('filters.status')
                    ->title('Статус')
                    ->options([
                        '' => 'Все статусы',
                        'draft' => 'Черновик',
                        'published' => 'Опубликовано',
                        'archived' => 'Архив',
                    ]),

                \Orchid\Screen\Fields\DateTimer::make('filters.date_from')
                    ->title('Дата от')
                    ->format('Y-m-d'),

                \Orchid\Screen\Fields\DateTimer::make('filters.date_to')
                    ->title('Дата до')
                    ->format('Y-m-d'),
            ]),
        ];
    }

    /**
     * Layout для логов
     */
    private function logsLayout(): array
    {
        return [
            Layout::table('logs', [
                TD::make('entity_label', 'Тип')
                    ->sort()
                    ->filter(TD::FILTER_TEXT),

                TD::make('operation_label', 'Операция')
                    ->sort()
                    ->filter(TD::FILTER_TEXT),

                TD::make('status_label', 'Статус')
                    ->sort()
                    ->filter(TD::FILTER_TEXT),

                TD::make('processed_records', 'Обработано')
                    ->sort(),

                TD::make('error_records', 'Ошибок')
                    ->sort(),

                TD::make('started_at', 'Начало')
                    ->sort(),

                TD::make('finished_at', 'Завершение')
                    ->sort(),

                TD::make('actions', 'Действия')
                    ->render(function ($log) {
                        $downloadButton = '';
                        if ($log->file_path && $log->status === 'completed') {
                            $downloadButton = Link::make('Скачать')
                                ->route('platform.import-export.download', $log->id)
                                ->class('btn btn-sm btn-primary');
                        }

                        return $downloadButton;
                    }),
            ]),
        ];
    }

    /**
     * Обработка импорта
     */
    public function import(Request $request)
    {
        $request->validate([
            'entity_type' => 'required|string|in:products,services,portfolio_cases,leads,product_categories',
            'operation_type' => 'required|string|in:create,update,upsert',
            'csv_file' => 'required|file|mimes:csv,txt',
        ]);

        try {
            $file = $request->file('csv_file');
            $entityType = $request->input('entity_type');
            $operationType = $request->input('operation_type');

            $importer = $this->getImporter($entityType);
            if (! $importer) {
                Toast::error('Неподдерживаемый тип сущности');

                return redirect()->route('platform.import-export');
            }

            $result = $importer->import($file, $operationType);

            if ($result['success']) {
                $message = "Импорт завершен успешно. Обработано: {$result['processed_count']} записей";
                if ($result['error_count'] > 0) {
                    $message .= ", ошибок: {$result['error_count']}";
                }
                Toast::success($message);
            } else {
                $message = "Импорт завершен с ошибками. Обработано: {$result['processed_count']}, ошибок: {$result['error_count']}";
                Toast::warning($message);
            }

        } catch (\Exception $e) {
            Toast::error('Ошибка при импорте: '.$e->getMessage());
        }

        return redirect()->route('platform.import-export');
    }

    /**
     * Обработка экспорта
     */
    public function export(Request $request)
    {
        $request->validate([
            'entity_type' => 'required|string|in:products,services,portfolio_cases,leads,product_categories,all',
            'filters' => 'array',
            'filters.status' => 'string|in:draft,published,archived',
            'filters.date_from' => 'date',
            'filters.date_to' => 'date',
            'filters.category_id' => 'integer',
            'filters.form_code' => 'string',
            'filters.utm_source' => 'string',
            'filters.utm_medium' => 'string',
            'filters.utm_campaign' => 'string',
        ]);

        try {
            $entityType = $request->input('entity_type');
            $filters = $request->input('filters', []);

            $exporter = new CSVExporter;

            if ($entityType === 'all') {
                // Массовый экспорт
                $filePaths = $exporter->exportAll($filters);
                Toast::success('Массовый экспорт запущен. Проверьте папку экспорта.');
            } else {
                // Экспорт одной сущности
                $filePath = $this->exportSingleEntity($exporter, $entityType, $filters);
                Toast::success("Экспорт завершен. Файл: {$filePath}");
            }

        } catch (\Exception $e) {
            Toast::error('Ошибка при экспорте: '.$e->getMessage());
        }

        return redirect()->route('platform.import-export');
    }

    /**
     * Предпросмотр экспорта
     */
    public function preview(Request $request)
    {
        $request->validate([
            'preview_entity_type' => 'required|string|in:products,services,portfolio_cases,leads,product_categories',
            'preview_filters' => 'array',
        ]);

        try {
            $entityType = $request->input('preview_entity_type');
            $filters = $request->input('preview_filters', []);

            $exporter = new CSVExporter;
            $preview = $exporter->previewExport($entityType, $filters);

            return response()->json([
                'success' => true,
                'preview' => $preview,
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Скачивание файла экспорта
     */
    public function download($logId)
    {
        $log = ImportLog::where('id', $logId)
            ->where('user_id', Auth::id())
            ->firstOrFail();

        if (! $log->file_path || ! file_exists(storage_path('app/'.$log->file_path))) {
            Toast::error('Файл не найден');

            return redirect()->route('platform.import-export');
        }

        return response()->download(storage_path('app/'.$log->file_path));
    }

    /**
     * Получение импортера по типу сущности
     */
    protected function getImporter(string $entityType)
    {
        $importers = [
            'products' => new \App\Services\ImportExport\ProductImporter,
            'services' => new \App\Services\ImportExport\ServiceImporter,
            'portfolio_cases' => new \App\Services\ImportExport\PortfolioCaseImporter,
            'leads' => new \App\Services\ImportExport\LeadImporter,
            'product_categories' => new \App\Services\ImportExport\ProductCategoryImporter,
        ];

        return $importers[$entityType] ?? null;
    }

    /**
     * Экспорт одной сущности
     */
    protected function exportSingleEntity(CSVExporter $exporter, string $entityType, array $filters): string
    {
        switch ($entityType) {
            case 'products':
                return $exporter->exportProducts($filters);
            case 'services':
                return $exporter->exportServices($filters);
            case 'portfolio_cases':
                return $exporter->exportPortfolioCases($filters);
            case 'leads':
                return $exporter->exportLeads($filters);
            case 'product_categories':
                return $exporter->exportProductCategories($filters);
            default:
                throw new \InvalidArgumentException("Неподдерживаемый тип сущности: {$entityType}");
        }
    }
}
