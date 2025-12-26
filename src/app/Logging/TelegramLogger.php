<?php

namespace App\Logging;

use Monolog\Formatter\LineFormatter;
use Monolog\Logger;

class TelegramLogger
{
    /**
     * Create a custom Monolog instance.
     *
     * @return \Monolog\Logger
     */
    public function __invoke(array $config): Logger
    {
        $handler = new TelegramHandler(
            botToken: config('services.telegram.bot_token'),
            chatId: config('services.telegram.chat_id'),
            level: $config['level'] ?? Logger::ERROR,
        );

        $handler->setFormatter(new LineFormatter(
            format: "%level_name%: %message% %context% %extra%",
            dateFormat: null,
            allowInlineLineBreaks: true,
            ignoreEmptyContextAndExtra: true
        ));

        return new Logger('telegram', [$handler]);
    }
}
