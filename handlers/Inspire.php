<?php

use \TelegramBot\Api\Types\Message;
use \TelegramBot\Api\Client;

class Inspire extends Command
{
    public static function exposedCommands(): array
    {
        return [];
    }
    public function __construct(Message $message, Client $bot)
    {
        parent::__construct($message, $bot);
    }
    public function execute(): void
    {

        $url = file_get_contents("https://inspirobot.me/api?generate=true");
        $this->bot->sendChatAction($this->message->getChat()->getId(), "upload_photo");
        $this->bot->sendPhoto(
            $this->message->getChat()->getId(),
            $url,
            null,
            $this->message->getMessageId(),
            null,
            null,
            "HTML"
        );
    }
}
