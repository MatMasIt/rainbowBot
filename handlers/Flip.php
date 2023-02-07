<?php
use \TelegramBot\Api\Types\Message;
use \TelegramBot\Api\Client;
class Flip extends Command
{
    const VERTICAL = 0;
    const HORIZONTAL = 1;
    const BOTH = 2;

    public static function exposedCommands(): array
    {
        return [];
    }
    private int $mode;
    public function __construct(Message $message, Client $bot, int $mode)
    {
        parent::__construct($message, $bot);
        $this->mode = $mode;
    }
    public function execute(): void
    {
        $m = $this->message->getReplyToMessage();
        if (!$m) {
            $this->bot->sendMessage($this->message->getChat()->getId(), "Cita una foto (nessun messaggio citato)", null, false, $this->message->getMessageId());
        } else {
            $p = $m->getPhoto();
            if (!$p) {
                $this->bot->sendMessage($this->message->getChat()->getId(), "Cita una foto (non hai citato una foto)", null, false, $this->message->getMessageId());
            } else {
                $biggest = $p[count($p) - 1];
                $path = $this->bot->getFile($biggest->getFileId())->getFilePath();
                $url = "https://api.telegram.org/file/bot" . $GLOBALS["token"] . "/" . $path;
                if (!file_exists('tempImageProcessing')) {
                    mkdir('tempImageProcessing');
                }
                $io = bin2hex(random_bytes(10));
                $filename = "tempImageProcessing/" . $io . ".jpg";
                $ch = curl_init($url);
                $fp = fopen($filename, 'wb+');
                curl_setopt($ch, CURLOPT_FILE, $fp);
                curl_setopt($ch, CURLOPT_HEADER, 0);
                curl_exec($ch);
                curl_close($ch);
                fclose($fp);
                $im = imagecreatefromjpeg($filename);
                if ($this->mode == self::VERTICAL || $this->mode == self::BOTH) imageflip($im, IMG_FLIP_VERTICAL);
                if ($this->mode == self::HORIZONTAL || $this->mode == self::BOTH) imageflip($im, IMG_FLIP_HORIZONTAL);
                if ($this->mode == self::VERTICAL) $text = "Rovesciato in <b>Verticale</b>";
                if ($this->mode == self::HORIZONTAL) $text = "Rovesciato in <b>Orizzontale</b>";
                if ($this->mode == self::BOTH) $text = "Rovesciato in <b>Orizzontale e Verticale</b>";
                imagejpeg($im, $filename);
                $this->bot->sendChatAction($this->message->getChat()->getId(), "upload_photo");
                $this->bot->sendPhoto(
                    $this->message->getChat()->getId(),
                    $GLOBALS["baseurl"] . "/tempImageProcessing/getter.php?photo=" . $io,
                    $text,
                    $this->message->getMessageId(),
                    null,
                    null,
                    "HTML"
                );
            }
        }
    }
}
