<?php
class Tpdne extends Command
{

    public static function exposedCommands(): array{
        return [];
    }

    public function __construct(\TelegramBot\Api\Types\Message $message, \TelegramBot\Api\Client $bot)
    {
        parent::__construct($message, $bot);
    }
    public function execute(): void
    {
        $this->bot->sendChatAction($this->message->getChat()->getId(), "upload_photo");
        $this->bot->sendPhoto(
            $this->message->getChat()->getId(),
            "https://thispersondoesnotexist.com/image?v=" . bin2hex(random_bytes(10)) . time(),
            null,
            $this->message->getMessageId(),
            null,
            null,
            "HTML"
        );
    }
}
