<?php

use TelegramBot\Api\Types\Message;
use \TelegramBot\Api\Client;
use \TelegramBot\Api\Types\Inline\InlineKeyboardMarkup;

class GroupWatcher
{
    public static function watch(Database $database, Message $message, Client $bot): void
    {
        $isNew = false;
        try {
            $du = DatabaseUser::getByChat($database, $message->getFrom());
            if ($du->getPrivateChatStatus() == PrivateChat::STATUS_STRAY_USER) {
                self::createMessageStray($database, $message, $bot);
                $du->setPrivateChatStatus(PrivateChat::STATUS_ASK_DM);
            }
            $du->save(false);
        } catch (NotFoundException $e) {
            $isNew = true;
            self::createMessageNormal($database, $message, $bot);
            $du = DatabaseUser::create($database, $message->getFrom());
            $du->setPrivateChatStatus(PrivateChat::STATUS_ASK_DM);
            $du->poke($message->getFrom());
        }

        $r = Group::poke($database, $message, $du);
        $shouldSendSeen = $r[1];
        $shouldSendNewGroup = $r[0];
        if ($shouldSendSeen && !$isNew) $bot->sendMessage($message->getChat()->getId(), "Benvenutə, ti ho già visto in precedenza.\n" . Lookup::toText($du), "HTML", false, $message->getMessageId());
        if ($shouldSendNewGroup) $bot->sendMessage($message->getChat()->getId(), "Sono in un nuovo gruppo! yay. Ricordatevi di darmi accesso ai messaggi per accogliere i nuovi arrivati.", "HTML", false, $message->getMessageId());
        if ($shouldSendNewGroup) $bot->sendMessage($GLOBALS["logId"], "Sono in un nuovo gruppo! " . $message->getChat()->getTitle() . "(@" . $message->getChat()->getUsername() . ")", "HTML", false);
    }
    public static function getCommonKeyboard(): InlineKeyboardMarkup
    {
        return $keyboard = new InlineKeyboardMarkup([
            [
                ['text' => "✏️ Compila", 'url' => 'https://t.me/' . $GLOBALS["username"]],
                ['text' => "❓ Cosa fa questo bot?", 'callback_data' => 'whatIsThis'],
                ['text' => "🚫 Escludimi dal bot", 'url' => 'https://t.me/' . $GLOBALS["username"]]
            ]
        ]);
    }
    public static function createMessageNormal(Database $database, Message $message, Client $bot): void
    {
        $keyboard = self::getCommonKeyboard();

        $bot->sendMessage($message->getChat()->getId(), "Benvenutə, questo bot gestisce un sistema di profili su questo ed altri gruppi!", null, false, $message->getMessageId(), $keyboard);
    }
    public static function createMessageStray(Database $database, Message $message, Client $bot): void
    {
        $keyboard = self::getCommonKeyboard();

        $bot->sendMessage($message->getChat()->getId(), "Benvenutə!\n Mi avevi scritto prima di entrare in un gruppo in cui c'ero e non potevo creare il profilo, ma ora posso.\n Questo bot gestisce un sistema di profili su questo ed altri gruppi!", null, false, $message->getMessageId(), $keyboard);
    }
}
