<?php
require_once "common.php";

use \TelegramBot\Api\Types\Message;
use \TelegramBot\Api\Types\User;
use \TelegramBot\Api\Client;
use  \TelegramBot\Api\Types\Inline\InlineKeyboardMarkup;

class Explain extends Command
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
        $text = "Provo a spiegare:\n";
        $name = $du->getUName() ?: $du->getName();
        $g = strtoupper($du->getUGender());
        $o = strtoupper($du->getUOrient());
        if (contains("TRANS", $g) || contains("MTF", $g) || contains("FTM", $g)) $text .= "\n$name è transgender, questo significa che la sua identità di genere (autopercezione del genere) diverge dal suo sesso biologico.";
        if (contains("FTM", $g)) $text .= "\nNello specifico il genere percepito da $name è Maschile.";
        if (contains("MTF", $g)) $text .= "\nNello specifico il genere percepito da $name è Femmnile.";
        if (contains("UOMO", $g)) $text .= "\n$name si percepisce come uomo.";
        if (contains("DONNA", $g)) $text .= "\n$name si percepisce come donna.";
        if (contains("NON-BINARY", $g)) $text .= "\n$name si percepisce come non-binary (non-binario).\nLa sua identità di genere (genere autopercepito) è al di fuori del cosiddetto binarismo di genere, ovvero non strettamente e/o completamente maschile o femminile.";
        if (contains("DEMIBOY", $g)) $text .= "\n$name si percepisce come parzialmente come uomo (demiboy).";
        if (contains("DEMIGIRL", $g)) $text .= "\n$name si percepisce come parzialmente come donna (demigirl).";
        if (contains("GENDERQUEER", $g)) $text .= "\n$name si percepisce come genderqueer, ovvero in una relazione non-normata rispetto al proprio genere.";

        if (contains("ETERO", $o)) $text .= "\n$name è eterosessuale, ovvero sperimenta attrazione verso persone del genere opposto.";
        if (contains("GAY", $o)) $text .= "\n$name è gay, ovvero sperimenta attrazione verso persone dello stesso genere.";
        if (contains("LESBICA", $o)) $text .= "\n$name è lesbica, ovvero sperimenta attrazione verso persone dello stesso genere.";
        if (contains("ASESSUALE", $o)) $text .= "\n$name è asessuale, ovvero non sperimenta attrazione.";
        if (contains("PANSESSUALE", $o)) $text .= "\n$name è pansessuale, ovvero sperimenta attrazione verso persone di tutti i generi.";
        if (contains("BISESSUALE", $o)) $text .= "\n$name è bisessuale, ovvero sperimenta attrazione verso persone di tutti i generi.";
        if (contains("DEMISESSUALE", $o)) $text .= "\n$name è demisessuale, ovvero sperimenta attrazione solo verso persone con cui ha un forte legame emotivo.";
        if (contains("QUESTIONING", $o)) $text .= "\n$name è questioning, ovvero sta comprendendo e/o esplorando la propria sessualità.";
        if ($text == "Provo a spiegare:\n") $text = "Non ho dati sufficienti o conformi al mio database per spiegare";
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
