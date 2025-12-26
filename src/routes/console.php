<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use App\Services\TelegramNotifier;
use Illuminate\Support\Facades\Log;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Artisan::command('telegram:health', function (TelegramNotifier $notifier) {
    $token = config('services.telegram.bot_token');
    $chatId = config('services.telegram.chat_id');

    if (empty($token) || empty($chatId)) {
        $this->warn('Telegram logging is not configured (missing TELEGRAM_BOT_TOKEN or TELEGRAM_CHAT_ID).');

        return;
    }

    $notifier->notifyDeploy(app()->environment());
    Log::channel('telegram')->info('✅ Health-check ping from console.');

    $this->info('Telegram notifications are configured. Test messages have been dispatched.');
})->purpose('Sends test messages to the Telegram log channel');
