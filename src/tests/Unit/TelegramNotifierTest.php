<?php

namespace Tests\Unit;

use App\Services\TelegramNotifier;
use Illuminate\Support\Facades\Log;
use Tests\TestCase;

class TelegramNotifierTest extends TestCase
{
    public function test_notifier_sends_expected_messages(): void
    {
        Log::shouldReceive('channel')->times(3)->with('telegram')->andReturnSelf();
        Log::shouldReceive('info')->once()->with('🚀 Деплой завершён (production).', []);
        Log::shouldReceive('error')->once()->with('❗️ Ошибка: Something went wrong', ['code' => 500]);
        Log::shouldReceive('info')->once()->with('📨 Новая заявка от Jane Doe.', ['phone' => '+123']);

        $notifier = app(TelegramNotifier::class);

        $notifier->notifyDeploy('production');
        $notifier->notifyError('Something went wrong', ['code' => 500]);
        $notifier->notifyApplication('Jane Doe', ['phone' => '+123']);
    }
}
