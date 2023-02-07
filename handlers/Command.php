<?php
use \TelegramBot\Api\Types\Message;
use \TelegramBot\Api\Client;
class Command
{
    protected $message; // Message
    protected $bot; // Client
    public function __construct(Message $message, Client $bot)
    {
        $this->message = $message;
        $this->bot = $bot;
    }
    protected function multiExplode(string $string, array $delimiters, int $max = -1): array
    {
        $l = [];
        $temp = "";
        $found = 0;
        foreach (str_split($string) as $char) {
            if (in_array($char, $delimiters)) {
                if ($max != -1 && $found == $max) break;
                $l[] = $temp;
                $temp = "";
                $found++;
            }
        }
        return $l;
    }

    public function execute(): void{

    }

}
