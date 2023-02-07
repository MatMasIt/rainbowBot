<?php

use \TelegramBot\Api\Types\Message;
use \TelegramBot\Api\Client;

class Eva extends Command
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
        $remaining = PictureAPIRateLimitHandler::countAdd();
        if ($remaining->getFinished() == CountAddResult::FINISHED_TODAY) {
            $message = $this->bot->sendMessage($this->message->getChat()->getId(), "È stato raggiunto il numero massimo di immagini giornaliero (" . $remaining->getDailyLimit() . ")", "HTML", false);
        } elseif ($remaining->getFinished() == CountAddResult::FINISHED_UNTIL_RENEW) {
            $message = $this->bot->sendMessage($this->message->getChat()->getId(), "È stato raggiunto il numero massimo di immagini ( stop fino a " . date("d/m/Y H:i:s", $remaining->getReset()) . ")", "HTML", false);
        } else {
            $m = $this->message->getReplyToMessage();
            if (!$m) {
                $this->bot->sendMessage($this->message->getChat()->getId(), "Cita un messaggio di testo", null, false, $this->message->getMessageId());
            } else {
                $url =  "https://api.apiflash.com/v1/urltoimage?access_key=" . $GLOBALS["urltoimagekey"] . "&url=" . $GLOBALS["baseurl"] . "/eva.php%3Fdata%3D" . urlencode(json_encode(explode("\n", $m->getText()))) . "&height=478&width=600";
                $this->bot->sendChatAction($this->message->getChat()->getId(), "upload_photo");
                $this->bot->sendPhoto(
                    $this->message->getChat()->getId(),
                    $url,
                    $remaining->getRemainingToday() . " immagini rimanenti oggi",
                    $this->message->getMessageId(),
                    null,
                    null,
                    "HTML"
                );
            }
        }
    }
}
