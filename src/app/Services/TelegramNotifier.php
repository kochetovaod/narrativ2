<?php

namespace App\Services;

use Illuminate\Support\Facades\Log;

class TelegramNotifier
{
    public function notifyDeploy(string $environment): void
    {
        Log::channel('telegram')->info("🚀 Деплой завершён ({$environment}).");
    }

    public function notifyError(string $message, array $context = []): void
    {
        Log::channel('telegram')->error("❗️ Ошибка: {$message}", $context);
    }

    public function notifyApplication(string $applicant, array $context = []): void
    {
        Log::channel('telegram')->info("📨 Новая заявка от {$applicant}.", $context);
    }
}
