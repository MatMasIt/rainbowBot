<?php
use \TelegramBot\Api\Types\Message;
use \TelegramBot\Api\Client;
class Rave extends Command
{

    public static function exposedCommands(): array
    {
        return [];
    }

    const CLASSIC = "classic";
    const GARFIELD = "garfield";
    const MEGALOVANIA = "megalovania";
    const OTAMATONE = "otamatone";

    private string $mode;
    public function __construct(Message $message, Client $bot, string $mode)
    {
        parent::__construct($message, $bot);
        $this->mode = $mode;
    }
    public function execute(): void
    {
        $m = $this->message->getReplyToMessage();
        if (!$m) {
            $this->bot->sendMessage($this->message->getChat()->getId(), "Cita un messaggio di testo", null, false, $this->message->getMessageId());
        } else {
            $this->bot->sendChatAction($this->message->getChat()->getId(), "upload_video");
            $this->bot->sendVideo(
                $this->message->getChat()->getId(),
                "https://crabrave.boringcactus.com/render?text=" . urlencode($m->getText()) . "&ext=mp4&style=" . $this->mode,
                null,
                null,
                $this->message->getMessageId(),
                null,
                null,
                "HTML"
            );
        }
    }
}
