<?php

namespace Tests\Feature;

use Tests\TestCase;

class TelegramHealthCommandTest extends TestCase
{
    public function test_health_command_warns_when_not_configured(): void
    {
        $this->artisan('telegram:health')
            ->expectsOutput('Telegram logging is not configured (missing TELEGRAM_BOT_TOKEN or TELEGRAM_CHAT_ID).')
            ->assertExitCode(0);
    }

    public function test_health_command_dispatches_when_configured(): void
    {
        config()->set('services.telegram.bot_token', 'token');
        config()->set('services.telegram.chat_id', 'chat');

        $this->artisan('telegram:health')
            ->expectsOutput('Telegram notifications are configured. Test messages have been dispatched.')
            ->assertExitCode(0);
    }
}
