<?php
require_once "common.php";

use \TelegramBot\Api\Types\Message;
use \TelegramBot\Api\Types\User;
use \TelegramBot\Api\Client;
use  \TelegramBot\Api\Types\Inline\InlineKeyboardMarkup;

class Lookup extends Command
{
    private Database $database;
    private ?User $user;
    public static function exposedCommands(): array
    {
        return [];
    }
    public function __construct(Message $message, Client $bot, Database $database, ?User $user = null)
    {
        parent::__construct($message, $bot);
        $this->database = $database;
        $this->user = $user;
    }
    public static function toText(DatabaseUser $du): string
    {

        if ($du->getPrivateChatStatus() != PrivateChat::STATUS_ENDED && $du->getPrivateChatStatus() != PrivateChat::STATUS_VIEWING_CARD) {
            return  "L'utente non ha ancora impostato i suoi dati o li sta modificando.";
        }
        $text = "";
        if ($du->getUName()) $text .= "\n\n<b>Nome</b>: \n" . $du->getUName();
        if ($du->getUbirthDate()) $text .= "\n\n<b>Data di Nascita</b>: \n" . itdate($du->getUbirthDate()) . " (" . DatabaseUser::calcAge($du->getUbirthDate()) . " anni)";
        if ($du->getUGender()) $text .= "\n\n<b>Genere</b>: \n" . $du->getUGender();
        if ($du->getUOrient()) $text .= "\n\n<b>Orientamento</b>: \n" . $du->getUOrient();
        if ($du->getUPlace()) $text .= "\n\n<b>Provenienza</b>: \n" . $du->getUPlace();
        if ($du->getURelationships()) $text .= "\n\n<b>Relazioni</b>: \n" . $du->getURelationships();
        if ($du->getUBio()) $text .= "\n\n<b>Bio</b>: \n" . $du->getUBio();
        if (strlen($text) == 0) $text = "Non ci sono dati";
        return $text;
    }
    public function execute(): void
    {
        if (!$this->replyUser) $this->replyUser = $this->message->getFrom();
        $m = $this->message->getReplyToMessage();
        if (!$m) {
            $du = DatabaseUser::getByChat($this->database, $this->message->getFrom());
            $this->bot->sendMessage($this->message->getChat()->getId(), self::toText($du), "HTML", false, $this->message->getMessageId());
        } else {
            $du = DatabaseUser::getByChat($this->database, $m->getFrom());
            $this->bot->sendMessage($m->getChat()->getId(), self::toText($du), "HTML", false, $m->getMessageId());
        }
    }
    public function pvtLookup(DatabaseUser $du)
    {
        if ($du->getLastPrivateMessage() != 0) {
            try {
                $this->bot->deleteMessage(
                    $this->user->getId(),
                    $du->getLastPrivateMessage()
                );
            } catch (\TelegramBot\Api\Exception $e) {
            }
        }
        $keyboard = new InlineKeyboardMarkup([
            [
                ['text' => "✅ Ok", 'callback_data' => 'endSet#save']
            ]
        ]);
        $message = $this->bot->sendMessage($this->user->getId(), self::toText($du), "HTML", false, null, $keyboard);
        $du->setLastPrivateMessage($message->getMessageId());
        $du->setPrivateChatStatus(PrivateChat::STATUS_VIEWING_CARD);
        $du->save(false);
    }
}
