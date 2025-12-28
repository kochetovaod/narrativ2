<?php

namespace Tests\Feature;

use App\Models\Form;
use App\Models\FormField;
use App\Services\CaptchaService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class CaptchaTest extends TestCase
{
    use RefreshDatabase;

    public function test_form_rejects_invalid_recaptcha_token(): void
    {
        // Настраиваем reCAPTCHA в конфиге
        config(['services.recaptcha.secret' => 'test_secret_key']);

        // Создаем форму с reCAPTCHA
        $form = Form::factory()->create([
            'code' => 'callback',
            'title' => 'Обратный звонок',
            'captcha_mode' => 'recaptcha',
            'is_active' => true,
        ]);

        FormField::factory()->create([
            'form_id' => $form->id,
            'key' => 'name',
            'label' => 'Имя',
            'type' => 'text',
            'is_required' => true,
            'sort' => 1,
        ]);

        // Мокаем неуспешную проверку reCAPTCHA
        Http::fake([
            'https://www.google.com/recaptcha/api/siteverify' => Http::response([
                'success' => false,
                'error-codes' => ['invalid-input-response'],
            ], 200),
        ]);

        $payload = [
            'name' => 'Тест',
            'g-recaptcha-response' => 'invalid_token',
            'consent_given' => true,
            'consent_doc_url' => 'https://example.com/privacy',
        ];

        $response = $this->postJson(route('forms.submit', ['formCode' => $form->code]), $payload);

        $response->assertStatus(400)
            ->assertJson([
                'success' => false,
                'message' => 'Ошибка проверки безопасности. Попробуйте еще раз.',
            ]);

        $this->assertDatabaseCount('leads', 0);
        $this->assertDatabaseCount('tracking_events', 1);
        $this->assertDatabaseHas('tracking_events', [
            'event_type' => 'custom',
            'event_name' => 'form_captcha_failed',
        ]);
    }

    public function test_form_accepts_valid_recaptcha_token(): void
    {
        config(['services.recaptcha.secret' => 'test_secret_key']);

        $form = Form::factory()->create([
            'code' => 'callback',
            'title' => 'Обратный звонок',
            'captcha_mode' => 'recaptcha',
            'is_active' => true,
        ]);

        FormField::factory()->create([
            'form_id' => $form->id,
            'key' => 'name',
            'label' => 'Имя',
            'type' => 'text',
            'is_required' => true,
            'sort' => 1,
        ]);

        // Мокаем успешную проверку reCAPTCHA
        Http::fake([
            'https://www.google.com/recaptcha/api/siteverify' => Http::response([
                'success' => true,
                'challenge_ts' => now()->toISOString(),
                'hostname' => 'localhost',
                'score' => 0.9,
            ], 200),
        ]);

        $payload = [
            'name' => 'Тест',
            'g-recaptcha-response' => 'valid_token',
            'consent_given' => true,
            'consent_doc_url' => 'https://example.com/privacy',
        ];

        $response = $this->postJson(route('forms.submit', ['formCode' => $form->code]), $payload);

        $response->assertOk()
            ->assertJson([
                'success' => true,
                'message' => 'Заявка успешно отправлена!',
            ]);

        $this->assertDatabaseCount('leads', 1);
        $this->assertDatabaseHas('tracking_events', [
            'event_type' => 'form_submit',
            'event_name' => 'form_submit',
        ]);
    }

    public function test_form_rejects_invalid_hcaptcha_token(): void
    {
        config(['services.hcaptcha.secret' => 'test_hcaptcha_secret']);

        $form = Form::factory()->create([
            'code' => 'contact',
            'title' => 'Контактная форма',
            'captcha_mode' => 'hcaptcha',
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

        // Мокаем неуспешную проверку hCaptcha
        Http::fake([
            'https://hcaptcha.com/siteverify' => Http::response([
                'success' => false,
                'error-codes' => ['invalid-or-already-seen-response'],
            ], 200),
        ]);

        $payload = [
            'email' => 'test@example.com',
            'h-captcha-response' => 'invalid_hcaptcha_token',
            'consent_given' => true,
            'consent_doc_url' => 'https://example.com/privacy',
        ];

        $response = $this->postJson(route('forms.submit', ['formCode' => $form->code]), $payload);

        $response->assertStatus(400)
            ->assertJson([
                'success' => false,
                'message' => 'Ошибка проверки безопасности. Попробуйте еще раз.',
            ]);

        $this->assertDatabaseCount('leads', 0);
    }

    public function test_form_accepts_valid_hcaptcha_token(): void
    {
        config(['services.hcaptcha.secret' => 'test_hcaptcha_secret']);

        $form = Form::factory()->create([
            'code' => 'contact',
            'title' => 'Контактная форма',
            'captcha_mode' => 'hcaptcha',
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

        // Мокаем успешную проверку hCaptcha
        Http::fake([
            'https://hcaptcha.com/siteverify' => Http::response([
                'success' => true,
                'credit' => false,
                'timestamp' => now()->toISOString(),
            ], 200),
        ]);

        $payload = [
            'email' => 'test@example.com',
            'h-captcha-response' => 'valid_hcaptcha_token',
            'consent_given' => true,
            'consent_doc_url' => 'https://example.com/privacy',
        ];

        $response = $this->postJson(route('forms.submit', ['formCode' => $form->code]), $payload);

        $response->assertOk()
            ->assertJson([
                'success' => true,
                'message' => 'Заявка успешно отправлена!',
            ]);

        $this->assertDatabaseCount('leads', 1);
    }

    public function test_form_with_no_captcha_allows_submission(): void
    {
        $form = Form::factory()->create([
            'code' => 'simple',
            'title' => 'Простая форма',
            'captcha_mode' => 'none',
            'is_active' => true,
        ]);

        FormField::factory()->create([
            'form_id' => $form->id,
            'key' => 'message',
            'label' => 'Сообщение',
            'type' => 'textarea',
            'is_required' => true,
            'sort' => 1,
        ]);

        $payload = [
            'message' => 'Тестовое сообщение',
            'consent_given' => true,
            'consent_doc_url' => 'https://example.com/privacy',
        ];

        $response = $this->postJson(route('forms.submit', ['formCode' => $form->code]), $payload);

        $response->assertOk()
            ->assertJson([
                'success' => true,
                'message' => 'Заявка успешно отправлена!',
            ]);

        $this->assertDatabaseCount('leads', 1);
    }

    public function test_form_rejects_when_recaptcha_not_configured(): void
    {
        // Не настраиваем reCAPTCHA в конфиге
        config(['services.recaptcha.secret' => null]);

        $form = Form::factory()->create([
            'code' => 'callback',
            'captcha_mode' => 'recaptcha',
            'is_active' => true,
        ]);

        FormField::factory()->create([
            'form_id' => $form->id,
            'key' => 'name',
            'label' => 'Имя',
            'type' => 'text',
            'is_required' => true,
            'sort' => 1,
        ]);

        $payload = [
            'name' => 'Тест',
            'g-recaptcha-response' => 'any_token',
            'consent_given' => true,
            'consent_doc_url' => 'https://example.com/privacy',
        ];

        $response = $this->postJson(route('forms.submit', ['formCode' => $form->code]), $payload);

        $response->assertStatus(400)
            ->assertJson([
                'success' => false,
                'message' => 'Ошибка проверки безопасности. Попробуйте еще раз.',
            ]);

        $this->assertDatabaseCount('leads', 0);
    }

    public function test_form_rejects_when_no_captcha_token_provided(): void
    {
        config(['services.recaptcha.secret' => 'test_secret']);

        $form = Form::factory()->create([
            'code' => 'callback',
            'captcha_mode' => 'recaptcha',
            'is_active' => true,
        ]);

        FormField::factory()->create([
            'form_id' => $form->id,
            'key' => 'name',
            'label' => 'Имя',
            'type' => 'text',
            'is_required' => true,
            'sort' => 1,
        ]);

        // Не отправляем токен капчи
        $payload = [
            'name' => 'Тест',
            'consent_given' => true,
            'consent_doc_url' => 'https://example.com/privacy',
        ];

        $response = $this->postJson(route('forms.submit', ['formCode' => $form->code]), $payload);

        $response->assertStatus(400)
            ->assertJson([
                'success' => false,
                'message' => 'Ошибка проверки безопасности. Попробуйте еще раз.',
            ]);

        $this->assertDatabaseCount('leads', 0);
    }

    public function test_captcha_service_methods(): void
    {
        $captchaService = app(CaptchaService::class);

        // Тест проверки конфигурации
        config(['services.recaptcha.secret' => '']);
        $this->assertFalse($captchaService->isConfigured('recaptcha'));

        config(['services.recaptcha.secret' => 'test_secret']);
        $this->assertTrue($captchaService->isConfigured('recaptcha'));

        config(['services.hcaptcha.secret' => 'test_hcaptcha_secret']);
        $this->assertTrue($captchaService->isConfigured('hcaptcha'));

        $this->assertTrue($captchaService->isConfigured('none'));
        $this->assertFalse($captchaService->isConfigured('invalid_type'));

        // Тест получения конфигурации для фронтенда
        config([
            'services.recaptcha.site_key' => 'test_site_key',
            'services.hcaptcha.site_key' => 'test_hcaptcha_site_key',
        ]);

        $recaptchaConfig = $captchaService->getFrontendConfig('recaptcha');
        $this->assertEquals([
            'enabled' => true,
            'siteKey' => 'test_site_key',
            'version' => 'v3',
        ], $recaptchaConfig);

        $hcaptchaConfig = $captchaService->getFrontendConfig('hcaptcha');
        $this->assertEquals([
            'enabled' => true,
            'siteKey' => 'test_hcaptcha_site_key',
            'version' => 'v1',
        ], $hcaptchaConfig);

        $noneConfig = $captchaService->getFrontendConfig('none');
        $this->assertEquals([
            'enabled' => false,
            'siteKey' => null,
            'version' => null,
        ], $noneConfig);
    }
}
