<?php

namespace App\Services\Seo;

use App\Models\SeoTemplate;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Arr;

class SeoMetaResolver
{
    /**
     * @param  array<string, mixed>  $variables
     * @return array<string, mixed>
     */
    public function resolve(Model $model, array $variables = []): array
    {
        $mergedVariables = $this->prepareVariables($model, $variables);
        $template = $this->findTemplate($model);
        $templateValues = $template ? $this->applyTemplate($template, $mergedVariables) : [];

        $overrides = Arr::wrap($model->seo ?? []);

        return array_filter(
            array_merge($templateValues, $overrides),
            fn ($value) => $value !== null && $value !== ''
        );
    }

    /**
     * @return array<string, mixed>
     */
    private function applyTemplate(SeoTemplate $template, array $variables): array
    {
        return [
            'title' => $this->replaceVariables($template->title_tpl, $variables),
            'description' => $this->replaceVariables($template->description_tpl, $variables),
            'h1' => $this->replaceVariables($template->h1_tpl, $variables),
            'og_title' => $this->replaceVariables($template->og_title_tpl, $variables),
            'og_description' => $this->replaceVariables($template->og_description_tpl, $variables),
            'og_image_mode' => $template->og_image_mode,
        ];
    }

    /**
     * @param  array<string, mixed>  $provided
     * @return array<string, mixed>
     */
    private function prepareVariables(Model $model, array $provided): array
    {
        $defaults = [
            'title' => $model->title ?? null,
            'description' => $model->description ?? $model->excerpt ?? null,
            'h1' => $model->title ?? null,
            'og_title' => $model->title ?? null,
            'og_description' => $model->description ?? $model->excerpt ?? null,
        ];

        return array_filter(array_merge($defaults, $provided), fn ($value) => $value !== null);
    }

    private function findTemplate(Model $model): ?SeoTemplate
    {
        return SeoTemplate::query()
            ->where('entity_type', class_basename($model))
            ->where('is_default', true)
            ->orderByDesc('id')
            ->first();
    }

    /**
     * @param  array<string, mixed>  $variables
     */
    private function replaceVariables(?string $template, array $variables): ?string
    {
        if ($template === null) {
            return null;
        }

        return preg_replace_callback(
            '/{{\\s*([\\w\\.]+)\\s*}}/',
            fn (array $matches) => (string) data_get($variables, $matches[1], ''),
            $template
        );
    }
}
