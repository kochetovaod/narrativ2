<?php

namespace Tests\Feature;

use App\Models\Form;
use App\Models\FormField;
use App\Models\Lead;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FormSubmissionTest extends TestCase
{
    use RefreshDatabase;

    public function test_form_submission_persists_lead_and_deduplication(): void
    {
        $form = Form::factory()->create([
            'code' => 'callback',
            'title' => 'Обратный звонок',
            'captcha_mode' => 'none',
            'is_active' => true,
        ]);

        FormField::factory()->create([
            'form_id' => $form->id,
            'key' => 'name',
            'label' => 'Имя',
            'type' => 'text',
            'is_required' => true,
            'sort' => 1,
            'validation_rules' => 'string|max:255',
        ]);

        FormField::factory()->create([
            'form_id' => $form->id,
            'key' => 'email',
            'label' => 'Email',
            'type' => 'email',
            'is_required' => true,
            'sort' => 2,
        ]);

        FormField::factory()->create([
            'form_id' => $form->id,
            'key' => 'consent_given',
            'label' => 'Согласие',
            'type' => 'checkbox',
            'is_required' => false,
            'sort' => 3,
        ]);

        $payload = [
            'name' => 'Иван',
            'email' => 'ivan@example.com',
            'consent_given' => true,
            'consent_doc_url' => 'https://example.com/privacy',
            'source_url' => 'https://example.com/landing',
            'page_title' => 'Лендинг',
            'utm_source' => 'adwords',
            'utm_campaign' => 'spring',
        ];

        $response = $this->postJson(route('forms.submit', ['formCode' => $form->code]), $payload);

        $response->assertOk()
            ->assertJson([
                'success' => true,
                'message' => 'Заявка успешно отправлена!',
            ])
            ->assertJsonStructure(['lead_id']);

        $lead = Lead::query()->first();

        $this->assertNotNull($lead);
        $this->assertSame($form->code, $lead->form_code);
        $this->assertSame('ivan@example.com', $lead->email);
        $this->assertTrue($lead->consent_given);
        $this->assertSame('https://example.com/privacy', $lead->consent_doc_url);
        $this->assertSame('https://example.com/landing', $lead->source_url);
        $this->assertEqualsCanonicalizing([
            'utm_source' => 'adwords',
            'utm_campaign' => 'spring',
        ], $lead->utm ?? []);
        $this->assertArrayHasKey('_submitted_at', $lead->payload);
        $this->assertSame('Иван', $lead->payload['name']);
        $this->assertSame('https://example.com/privacy', $lead->payload['_consent_doc_url']);

        $this->assertDatabaseHas('lead_dedup_index', [
            'lead_id' => $lead->id,
            'contact_key' => 'email:ivan@example.com',
            'created_date' => today()->toDateString(),
        ]);

        $this->assertDatabaseCount('tracking_events', 2);
        $this->assertDatabaseHas('tracking_events', [
            'event_type' => 'form_submit',
            'event_name' => 'form_submit',
        ]);
        $this->assertDatabaseHas('tracking_events', [
            'event_type' => 'conversion',
            'event_name' => 'form_callback',
        ]);
    }

    public function test_consent_is_required_when_checkbox_present(): void
    {
        $form = Form::factory()->create([
            'code' => 'question',
            'captcha_mode' => 'none',
            'is_active' => true,
        ]);

        FormField::factory()->create([
            'form_id' => $form->id,
            'key' => 'email',
            'label' => 'Email',
            'type' => 'email',
            'is_required' => true,
            'sort' => 1,
        ]);

        FormField::factory()->create([
            'form_id' => $form->id,
            'key' => 'consent_given',
            'label' => 'Согласие',
            'type' => 'checkbox',
            'is_required' => false,
            'sort' => 2,
        ]);

        $response = $this->postJson(route('forms.submit', ['formCode' => $form->code]), [
            'email' => 'client@example.com',
        ]);

        $response->assertStatus(422);
        $this->assertDatabaseCount('leads', 0);
    }

    public function test_form_preview_returns_sorted_active_form(): void
    {
        $form = Form::factory()->create([
            'code' => 'preview',
            'title' => 'Форма предпросмотра',
            'captcha_mode' => 'none',
            'is_active' => true,
        ]);

        FormField::factory()->create([
            'form_id' => $form->id,
            'key' => 'email',
            'label' => 'Email',
            'type' => 'email',
            'is_required' => true,
            'sort' => 2,
        ]);

        FormField::factory()->create([
            'form_id' => $form->id,
            'key' => 'name',
            'label' => 'Имя',
            'type' => 'text',
            'is_required' => true,
            'sort' => 1,
        ]);

        $response = $this->getJson(route('forms.preview', ['formCode' => $form->code]));

        $response->assertOk()
            ->assertJsonPath('form.id', $form->id)
            ->assertJsonPath('form.code', $form->code)
            ->assertJsonPath('form.fields.0.key', 'name')
            ->assertJsonPath('form.fields.1.key', 'email');

        $inactiveForm = Form::factory()->create([
            'code' => 'hidden-form',
            'is_active' => false,
        ]);

        $this->getJson(route('forms.preview', ['formCode' => $inactiveForm->code]))
            ->assertStatus(404);
    }
}
