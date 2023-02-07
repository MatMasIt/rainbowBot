<?php
error_reporting(E_ALL);

use \TelegramBot\Api\Client;
use \TelegramBot\Api\Types\Update;

if (PHP_INT_MAX == 2147483647) die("64 bit plaform required");
require_once "vendor/autoload.php";
require_once "database/Database.php";
require_once "database/NotFoundException.php";
require_once "database/DatabaseUser.php";
require_once "handlers/Command.php";
require_once "handlers/Flip.php";
require_once "handlers/Inspire.php";
require_once "handlers/Tpdne.php";
require_once "handlers/Rave.php";
require_once "handlers/Eva.php";
require_once "handlers/PrivateChat.php";
require_once "database/Group.php";
require_once "handlers/GroupWatcher.php";
require_once "handlers/Card.php";
require_once "handlers/Lookup.php";
require_once "handlers/Explain.php";
require_once "config.php";
try {
    $bot = new Client($GLOBALS["token"]);
    $database = new Database(new PDO("sqlite:database.sqlite3"));

    $bot->command('fliph', function ($message) use ($bot, $database) {
        if (PrivateChat::isPrivateChatContextChecker($message)) return false;
        GroupWatcher::watch($database, $message, $bot);
        $flip = new Flip($message, $bot, Flip::HORIZONTAL);
        $flip->execute();
    });

    $bot->command('flipv', function ($message) use ($bot, $database) {
        if (PrivateChat::isPrivateChatContextChecker($message)) return false;
        GroupWatcher::watch($database, $message, $bot);
        $flip = new Flip($message, $bot, Flip::VERTICAL);
        $flip->execute();
    });

    $bot->command('flipb', function ($message) use ($bot, $database) {
        if (PrivateChat::isPrivateChatContextChecker($message)) return false;
        GroupWatcher::watch($database, $message, $bot);
        $flip = new Flip($message, $bot, Flip::BOTH);
        $flip->execute();
    });

    //TODO implement [commands] /card /lookup /help /rabio /stats
    //TODO create private flow, with interactive buttons and datepicker
    //TOO create admin comm ands and all logic

    $bot->command('inspire', function ($message) use ($bot, $database) {
        if (PrivateChat::isPrivateChatContextChecker($message)) return false;
        GroupWatcher::watch($database, $message, $bot);
        $inspire = new Inspire($message, $bot);
        $inspire->execute();
    });

    $bot->command('tpdne', function ($message) use ($bot, $database) {
        if (PrivateChat::isPrivateChatContextChecker($message)) return false;
        GroupWatcher::watch($database, $message, $bot);
        $inspire = new Tpdne($message, $bot);
        $inspire->execute();
    });

    $bot->command('rave', function ($message) use ($bot, $database) {
        if (PrivateChat::isPrivateChatContextChecker($message)) return false;
        GroupWatcher::watch($database, $message, $bot);
        $inspire = new Rave($message, $bot, Rave::CLASSIC);
        $inspire->execute();
    });

    $bot->command('garfield', function ($message) use ($bot, $database) {
        if (PrivateChat::isPrivateChatContextChecker($message)) return false;
        GroupWatcher::watch($database, $message, $bot);
        $inspire = new Rave($message, $bot, Rave::GARFIELD);
        $inspire->execute();
    });

    $bot->command('megalovania', function ($message) use ($bot, $database) {
        if (PrivateChat::isPrivateChatContextChecker($message)) return false;
        GroupWatcher::watch($database, $message, $bot);
        $inspire = new Rave($message, $bot, Rave::MEGALOVANIA);
        $inspire->execute();
    });

    $bot->command('otamatone', function ($message) use ($bot, $database) {
        if (PrivateChat::isPrivateChatContextChecker($message)) return false;
        GroupWatcher::watch($database, $message, $bot);
        $inspire = new Rave($message, $bot, Rave::OTAMATONE);
        $inspire->execute();
    });

    $bot->command('info', function ($message) use ($bot, $database) {
        if (PrivateChat::isPrivateChatContextChecker($message)) return false;
        GroupWatcher::watch($database, $message, $bot);
        $bot->sendMessage($message->getChat()->getId(), "V 10.0.0 by @matmasak\n Open source code on https://github.com/MatMasIt/rainbowBot", null, false, $message->getMessageId());
    });

    /*$bot->command('debug', function ($message) use ($bot) {
        $bot->sendMessage($message->getChat()->getId(), $bot->getRawBody(), null, false, $message->getMessageId());
    });*/



    $bot->command('eva', function ($message) use ($bot, $database) {
        if (PrivateChat::isPrivateChatContextChecker($message)) return false;
        GroupWatcher::watch($database, $message, $bot);
        $eva = new Eva($message, $bot);
        $eva->execute();
    });

    $bot->command('card', function ($message) use ($bot, $database) {
        if (PrivateChat::isPrivateChatContextChecker($message)) return false;
        GroupWatcher::watch($database, $message, $bot);
        $card = new Card($message, $bot, $database);
        $card->execute();
    });

    $bot->command('lookup', function ($message) use ($bot, $database) {
        if (PrivateChat::isPrivateChatContextChecker($message)) return false;
        GroupWatcher::watch($database, $message, $bot);
        $l = new Lookup($message, $bot, $database);
        $l->execute();
    });

    $bot->command('explain', function ($message) use ($bot, $database) {
        if (PrivateChat::isPrivateChatContextChecker($message)) return false;
        GroupWatcher::watch($database, $message, $bot);
        $l = new Explain($message, $bot, $database);
        $l->execute();
    });



    # This function is called if someone clicks on an inline button
    $bot->callbackQuery(function ($message) use ($bot, $database) {
        if ($message->getData() == "noPolicy") {
            $bot->answerCallbackQuery($message->getId());
            $p = new PrivateChat($message->getMessage(), $bot, $database, $message->getFrom());
            $p->noPolicy();
        } elseif ($message->getData() == "yesPolicy") {
            $bot->answerCallbackQuery($message->getId());
            $p = new PrivateChat($message->getMessage(), $bot, $database, $message->getFrom());
            $p->yesPolicy();
        } elseif ($message->getData() == "whatIsThis") {
            $bot->answerCallbackQuery($message->getId(), "Long descrizione");
        } elseif ($message->getData() == "excludeMe") {
            $bot->answerCallbackQuery($message->getId());
            $p = new PrivateChat($message->getMessage(), $bot, $database, $message->getFrom());
            $p->exclude();
        } elseif ($message->getData() == "yesExclude") {
            $bot->answerCallbackQuery($message->getId());
            $p = new PrivateChat($message->getMessage(), $bot, $database, $message->getFrom());
            $p->excludeYes();
        } elseif ($message->getData() == "noExclude") {
            $bot->answerCallbackQuery($message->getId());
            $du = DatabaseUser::getByChat($database, $message->getFrom());
            $du->setPrivateChatStatus(PrivateChat::STATUS_ASK_DM);
            $du->setOptedOut(false);
            $du->save(false);
            $p = new PrivateChat($message->getMessage(), $bot, $database, $message->getFrom());
            $p->execute();
        } elseif ($message->getData() == "changedIdea") {
            $bot->answerCallbackQuery($message->getId());
            $du = DatabaseUser::getByChat($database, $message->getFrom());
            $du->setPrivateChatStatus(PrivateChat::STATUS_ASK_DM);
            $du->setOptedOut(false);
            $du->save(false);
            $p = new PrivateChat($message->getMessage(), $bot, $database, $message->getFrom());
            $p->execute();
            //syntax is skipTo#part
        } elseif (explode("#", $message->getData())[0] == "go") {
            $bot->answerCallbackQuery($message->getId());
            $expl = explode("#", $message->getData());
            $step = $expl[1];
            $reset = $expl[2] === "reset";
            $du = DatabaseUser::getByChat($database, $message->getFrom());
            if ($reset) $du->setNULL($step);
            $du->setPrivateChatStatus($step);
            $du->setOptedOut(false);
            $du->save(false);
            $p = new PrivateChat($message->getMessage(), $bot, $database, $message->getFrom());
            $p->execute(false);
        } elseif (explode("#", $message->getData())[0] == "setGender") {
            $bot->answerCallbackQuery($message->getId());
            $expl = explode("#", $message->getData());
            $text = $expl[1];
            $du = DatabaseUser::getByChat($database, $message->getFrom());
            if ($text == "custom") {
                $message = $bot->sendMessage($message->getFrom()->getId(), "Scrivi il genere", null, false);
                try {
                    if ($du->getLastPrivateMessage() != 0) $this->bot->deleteMessage(
                        $du->getTelegramId(),
                        $du->getLastPrivateMessage()
                    );
                } catch (\TelegramBot\Api\Exception $e) {
                }
                $du->setLastPrivateMessage($message->getMessageId());
                $du->save(false);
            } else {
                $du->setUGender($text);
                $du->setOptedOut(false);
                $du->save(false);
                $p = new PrivateChat($message->getMessage(), $bot, $database, $message->getFrom());
                $p->execute(false);
            }
        } elseif (explode("#", $message->getData())[0] == "setOrient") {
            $bot->answerCallbackQuery($message->getId());
            $expl = explode("#", $message->getData());
            $text = $expl[1];
            $du = DatabaseUser::getByChat($database, $message->getFrom());
            if ($text == "custom") {
                $message = $bot->sendMessage($message->getFrom()->getId(), "Scrivi l'orientamento", null, false);
                try {
                    if ($du->getLastPrivateMessage() != 0) $this->bot->deleteMessage(
                        $du->getTelegramId(),
                        $du->getLastPrivateMessage()
                    );
                } catch (\TelegramBot\Api\Exception $e) {
                }
                $du->setLastPrivateMessage($message->getMessageId());
                $du->save(false);
            } else {
                $du->setUOrient($text);
                $du->setOptedOut(false);
                $du->save(false);
                $p = new PrivateChat($message->getMessage(), $bot, $database, $message->getFrom());
                $p->execute(false);
            }
        } elseif (explode("#", $message->getData())[0] == "setWhere") {
            $bot->answerCallbackQuery($message->getId());
            $expl = explode("#", $message->getData());
            $text = $expl[1];
            $du = DatabaseUser::getByChat($database, $message->getFrom());
            if ($text == "custom") {
                $message = $bot->sendMessage($message->getFrom()->getId(), "Scrivi da dove vieni", null, false);
                try {
                    if ($du->getLastPrivateMessage() != 0) $this->bot->deleteMessage(
                        $du->getTelegramId(),
                        $du->getLastPrivateMessage()
                    );
                } catch (\TelegramBot\Api\Exception $e) {
                }
                $du->setLastPrivateMessage($message->getMessageId());
                $du->save(false);
            } else {
                $du->setUPlace($text);
                $du->setOptedOut(false);
                $du->save(false);
                $p = new PrivateChat($message->getMessage(), $bot, $database, $message->getFrom());
                $p->execute(false);
            }
        } elseif (explode("#", $message->getData())[0] == "setPVT") {
            $bot->answerCallbackQuery($message->getId());
            $expl = explode("#", $message->getData());
            $text = $expl[1];
            $du = DatabaseUser::getByChat($database, $message->getFrom());
            if ($text == "custom") {
                $message = $bot->sendMessage($message->getFrom()->getId(), "Scrivi una risposta personalizzata", null, false);
                try {
                    if ($du->getLastPrivateMessage() != 0) $this->bot->deleteMessage(
                        $du->getTelegramId(),
                        $du->getLastPrivateMessage()
                    );
                } catch (\TelegramBot\Api\Exception $e) {
                }
                $du->setLastPrivateMessage($message->getMessageId());
                $du->save(false);
            } else {
                $du->setUpvtChoice($text);
                $du->setOptedOut(false);
                $du->save(false);
                $p = new PrivateChat($message->getMessage(), $bot, $database, $message->getFrom());
                $p->execute(false);
            }
        } elseif (explode("#", $message->getData())[0] == "setRel") {
            $bot->answerCallbackQuery($message->getId());
            $expl = explode("#", $message->getData());
            $text = $expl[1];
            $du = DatabaseUser::getByChat($database, $message->getFrom());
            if ($text == "custom") {
                $message = $bot->sendMessage($message->getFrom()->getId(), "Scrivi una risposta personalizzata", null, false);
                try {
                    if ($du->getLastPrivateMessage() != 0) $this->bot->deleteMessage(
                        $du->getTelegramId(),
                        $du->getLastPrivateMessage()
                    );
                } catch (\TelegramBot\Api\Exception $e) {
                }
                $du->setLastPrivateMessage($message->getMessageId());
                $du->save(false);
            } else {
                $du->setURelationships($text);
                $du->setOptedOut(false);
                $du->save(false);
                $p = new PrivateChat($message->getMessage(), $bot, $database, $message->getFrom());
                $p->execute(false);
            }
        } elseif (explode("#", $message->getData())[0] == "endSet") {
            $bot->answerCallbackQuery($message->getId());
            $du = DatabaseUser::getByChat($database, $message->getFrom());
            $expl = explode("#", $message->getData());
            $askConf = $expl[1] != "save" && $du->countCompleted() != count(PrivateChat::$order);
            $du = DatabaseUser::getByChat($database, $message->getFrom());
            $p = new PrivateChat($message->getMessage(), $bot, $database, $message->getFrom());
            $p->end($askConf);
        } elseif ($message->getData() == "viewCard") {
            $bot->answerCallbackQuery($message->getId());
            $card = new Card($message->getMessage(), $bot, $database, $message->getFrom());
            $du = DatabaseUser::getByChat($database, $message->getFrom());
            $card->viewPVC($du);
        } elseif ($message->getData() == "viewInfo") {
            $bot->answerCallbackQuery($message->getId());
            $card = new Lookup($message->getMessage(), $bot, $database, $message->getFrom());
            $du = DatabaseUser::getByChat($database, $message->getFrom());
            $card->pvtLookup($du);
        } elseif ($message->getData() == "viewExp") {
            $bot->answerCallbackQuery($message->getId());
            $card = new Explain($message->getMessage(), $bot, $database, $message->getFrom());
            $du = DatabaseUser::getByChat($database, $message->getFrom());
            $card->pvtLookup($du);
        } elseif ($message->getData() == "writeMessage") {
            $bot->answerCallbackQuery($message->getId());
            $p = new PrivateChat($message->getMessage(), $bot, $database, $message->getFrom());
            $p->writeMessage();
        }
    });


    $bot->on(function (Update $update) use ($bot, $database) {
        $p = new PrivateChat($update->getMessage(), $bot, $database);
        $p->execute();
    }, function (Update $update) {
        return PrivateChat::isPrivateChatContextChecker($update);
    });

    $bot->on(function (Update $update) use ($bot, $database) {
        GroupWatcher::watch($database, $update->getMessage(), $bot);
    }, function (Update $update) {
        return PrivateChat::isPublicChatContextChecker($update);
    });


    $bot->run();
} catch (\TelegramBot\Api\Exception $e) {
    file_put_contents("error.log", date("d/m/Y H:i:s") . "| " . $e->getMessage());
}
