<?php

declare(strict_types=1);

namespace App\Services;

use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class CaptchaService
{
    private const RECAPTCHA_VERIFY_URL = 'https://www.google.com/recaptcha/api/siteverify';

    private const HCAPTCHA_VERIFY_URL = 'https://hcaptcha.com/siteverify';

    /**
     * Проверка Google reCAPTCHA
     */
    public function validateReCaptcha(string $token, ?string $remoteIp = null): bool
    {
        $secret = Config::get('services.recaptcha.secret');

        if (empty($secret)) {
            Log::warning('reCAPTCHA secret key not configured');

            return false;
        }

        try {
            $response = Http::timeout((int) Config::get('services.captcha.verify_timeout', 10))
                ->asForm()
                ->post(self::RECAPTCHA_VERIFY_URL, [
                    'secret' => $secret,
                    'response' => $token,
                    'remoteip' => $remoteIp,
                ]);

            if (! $response->successful()) {
                Log::error('reCAPTCHA verification request failed', [
                    'status' => $response->status(),
                    'body' => $response->body(),
                ]);

                return false;
            }

            $data = $response->json();

            if (! isset($data['success'])) {
                Log::error('reCAPTCHA verification response missing success field', $data);

                return false;
            }

            if ($data['success']) {
                Log::info('reCAPTCHA verification successful', [
                    'challenge_ts' => $data['challenge_ts'] ?? null,
                    'hostname' => $data['hostname'] ?? null,
                    'score' => $data['score'] ?? null,
                    'action' => $data['action'] ?? null,
                ]);

                return true;
            } else {
                Log::warning('reCAPTCHA verification failed', [
                    'error_codes' => $data['error-codes'] ?? [],
                    'remoteip' => $remoteIp,
                ]);

                return false;
            }

        } catch (\Exception $e) {
            Log::error('reCAPTCHA verification exception', [
                'error' => $e->getMessage(),
                'remoteip' => $remoteIp,
            ]);

            return false;
        }
    }

    /**
     * Проверка hCaptcha
     */
    public function validateHCaptcha(string $token, ?string $remoteIp = null): bool
    {
        $secret = Config::get('services.hcaptcha.secret');

        if (empty($secret)) {
            Log::warning('hCaptcha secret key not configured');

            return false;
        }

        try {
            $response = Http::timeout((int) Config::get('services.captcha.verify_timeout', 10))
                ->asForm()
                ->post(self::HCAPTCHA_VERIFY_URL, [
                    'secret' => $secret,
                    'response' => $token,
                    'remoteip' => $remoteIp,
                ]);

            if (! $response->successful()) {
                Log::error('hCaptcha verification request failed', [
                    'status' => $response->status(),
                    'body' => $response->body(),
                ]);

                return false;
            }

            $data = $response->json();

            if (! isset($data['success'])) {
                Log::error('hCaptcha verification response missing success field', $data);

                return false;
            }

            if ($data['success']) {
                Log::info('hCaptcha verification successful', [
                    'credit' => $data['credit'] ?? false,
                    'timestamp' => $data['timestamp'] ?? null,
                    'remoteip' => $remoteIp,
                ]);

                return true;
            } else {
                Log::warning('hCaptcha verification failed', [
                    'error_codes' => $data['error-codes'] ?? [],
                    'remoteip' => $remoteIp,
                ]);

                return false;
            }

        } catch (\Exception $e) {
            Log::error('hCaptcha verification exception', [
                'error' => $e->getMessage(),
                'remoteip' => $remoteIp,
            ]);

            return false;
        }
    }

    /**
     * Валидация токена капчи в зависимости от типа
     */
    public function validateCaptcha(string $type, string $token, ?string $remoteIp = null): bool
    {
        return match ($type) {
            'recaptcha' => $this->validateReCaptcha($token, $remoteIp),
            'hcaptcha' => $this->validateHCaptcha($token, $remoteIp),
            'none' => true,
            default => false,
        };
    }

    /**
     * Проверка настроек капчи
     */
    public function isConfigured(string $type): bool
    {
        return match ($type) {
            'recaptcha' => ! empty(Config::get('services.recaptcha.secret')),
            'hcaptcha' => ! empty(Config::get('services.hcaptcha.secret')),
            'none' => true,
            default => false,
        };
    }

    /**
     * Получение конфигурации капчи для фронтенда
     */
    public function getFrontendConfig(string $type): array
    {
        return match ($type) {
            'recaptcha' => [
                'enabled' => ! empty(Config::get('services.recaptcha.site_key')),
                'siteKey' => Config::get('services.recaptcha.site_key'),
                'version' => 'v3',
            ],
            'hcaptcha' => [
                'enabled' => ! empty(Config::get('services.hcaptcha.site_key')),
                'siteKey' => Config::get('services.hcaptcha.site_key'),
                'version' => 'v1',
            ],
            'none' => [
                'enabled' => false,
                'siteKey' => null,
                'version' => null,
            ],
            default => [
                'enabled' => false,
                'siteKey' => null,
                'version' => null,
            ],
        };
    }
}
