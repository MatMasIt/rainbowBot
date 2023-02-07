<?php

use \TelegramBot\Api\Types\Message;
use \TelegramBot\Api\Types\User;
use \TelegramBot\Api\Client;
use  \TelegramBot\Api\Types\Inline\InlineKeyboardMarkup;

class Card extends Command
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
    public function execute(): void
    {
        if (!$this->replyUser) $this->replyUser = $this->message->getFrom();
        $m = $this->message->getReplyToMessage();
        if (!$m) {
            $this->sendCard($this->message->getFrom(), $this->message->getChat()->getId(), $this->message->getMessageId());
        } else {
            $this->sendCard($m->getFrom(), $m->getChat()->getId(), $m->getMessageId());
        }
    }
    public function viewPVC(DatabaseUser $du)
    {
        if ($du->getLastPrivateMessage() != 0) $this->bot->deleteMessage(
            $this->user->getId(),
            $du->getLastPrivateMessage()
        );
        $keyboard = new InlineKeyboardMarkup([
            [
                ['text' => "✅ Ok", 'callback_data' => 'endSet#save']
            ]
        ]);
        $m = $this->sendCard($this->user, $this->user->getId(), null, $keyboard);
        $du->setLastPrivateMessage($m->getMessageId());
        $du->setPrivateChatStatus(PrivateChat::STATUS_VIEWING_CARD);
        $du->save(false);
    }
    public function sendCard(User $u, int $chatId, ?int $replyTo = null, $keyboard = null)
    {
        $du = DatabaseUser::getByChat($this->database, $u);
        if ($du->getPrivateChatStatus() != PrivateChat::STATUS_ENDED && $du->getPrivateChatStatus() != PrivateChat::STATUS_VIEWING_CARD) {
            $message = $this->bot->sendMessage($chatId, "L'utente non ha ancora impostato i suoi dati o li sta modificando.", "HTML", false, $replyTo, $keyboard);
            $du->setPrivateChatStatus(PrivateChat::STATUS_ENDED);
            return $message;
        }
        if ($du->countCompleted() == 0) {
            $message = $this->bot->sendMessage($chatId, "Non ci sono dati", "HTML", false, $replyTo, $keyboard);
            $du->setPrivateChatStatus(PrivateChat::STATUS_ENDED);
            return $message;
        }
        $ps = $this->bot->getUserProfilePhotos($u->getId())->getPhotos();
        $firstPhoto = $ps[0];
        $biggest = $firstPhoto[count($firstPhoto) - 1];
        $path = $this->bot->getFile($biggest->getFileId())->getFilePath();
        $url = "https://api.telegram.org/file/bot" . $GLOBALS["token"] . "/" . $path;
        $data = [
            "name" => $du->getUName(),
            "birth" => $du->getUbirthDate(),
            "gender" => $du->getUGender(),
            "orientation" => $du->getUOrient(),
            "where" => $du->getUPlace(),
            "pvt" => $du->getUpvtChoice(),
            "rel" => $du->getURelationships(),
            "flags" => $du->getFlags(),
            "isDev" => $du->getTelegramId() == $GLOBALS["masterId"],
            "img" => $url,
            "nonce" => bin2hex(random_bytes(16))
        ];
        $height = 480 + floor(count($du->getFlags()) / 5) * 100;
        $url =  $GLOBALS["imageServer"] . "?access_key=" . $GLOBALS["urltoimagekey"] . "&url=" . $GLOBALS["baseurl"] . "/card.php%3Fdata%3D" . urlencode(json_encode($data)) . "&height=$height&width=600";
        $this->bot->sendChatAction($this->message->getChat()->getId(), "upload_photo");
        try {
            $message = $this->bot->sendPhoto(
                $chatId,
                $url,
                "",
                $replyTo,
                $keyboard,
                null,
                "HTML"
            );
        } catch (\TelegramBot\Api\Exception $e) {
            $message = $this->bot->sendMessage($chatId, "Non è stato possibile generare l'immagine", "HTML", false, $replyTo, $keyboard);
        }
        return $message;
    }
}
