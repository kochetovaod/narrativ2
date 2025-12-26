<?php

namespace App\Logging;

use Illuminate\Support\Str;
use Monolog\Handler\AbstractProcessingHandler;
use Monolog\Level;
use Monolog\LogRecord;
use TelegramBot\Api\BotApi;
use Throwable;

class TelegramHandler extends AbstractProcessingHandler
{
    private ?BotApi $bot;
    private ?string $chatId;

    public function __construct(?string $botToken, ?string $chatId, int|string|Level $level = Level::Error, bool $bubble = true)
    {
        parent::__construct($level, $bubble);

        if (empty($botToken) || empty($chatId)) {
            $this->bot = null;
            $this->chatId = null;

            return;
        }

        $this->bot = new BotApi($botToken);
        $this->chatId = $chatId;
    }

    protected function write(LogRecord $record): void
    {
        if ($this->bot === null || $this->chatId === null || app()->environment('testing')) {
            return;
        }

        $text = trim($record->formatted ?? $record->message);
        $text = Str::limit($text, 4000, '…');

        try {
            $this->bot->sendMessage($this->chatId, $text);
        } catch (Throwable $exception) {
            report($exception);
        }
    }
}
