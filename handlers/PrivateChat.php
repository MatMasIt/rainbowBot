<?php

use \TelegramBot\Api\Types\Message;
use \TelegramBot\Api\Client;
use  \TelegramBot\Api\Types\Inline\InlineKeyboardMarkup;
use \TelegramBot\Api\Types\User;
//Change Users Class and fb to store all user data in one table
class PrivateChat extends Command
{
    const STATUS_ASK_DM = "askDm";
    const STATUS_CONSENT = "consent";
    const STATUS_ASK_NAME = "askName";
    const STATUS_ASK_BIRTH = "askBirth";
    const STATUS_ASK_GENDER = "askGender";
    const STATUS_ASK_ORIENT = "askOrient";
    const STATUS_ASK_WHERE = "askWhere";
    const STATUS_ASK_PVT = "askPVT";
    const STATUS_ASK_REL = "askrel";
    const STATUS_ASK_BIO = "askBio";
    const STATUS_EDITING = "edit";
    const STATUS_STRAY_USER = "stray";
    const STATUS_EXCLUDED = "exclude";
    const STATUS_NOT_CONSENT = "notConsent";
    const STATUS_ENDED = "ended";
    const STATUS_VIEWING_CARD = "card";
    const STATUS_MESSAGE_SEND = "msgsend";
    private Database $database;

    public static function exposedCommands(): array
    {
        return [];
    }

    private ?User $replyUser;
    public function __construct(Message $message, Client $bot, Database $database, User $replyUser = null)
    {
        parent::__construct($message, $bot);
        $this->database = $database;
        $this->replyUser = $replyUser;
    }
    public function execute(bool $mustSet = true): void
    {
        if (!$this->replyUser) $this->replyUser = $this->message->getFrom();
        try {
            $du = DatabaseUser::getByChat($this->database, $this->replyUser);
            switch ($du->getPrivateChatStatus()) {
                case self::STATUS_ASK_DM:
                    $keyboard = new InlineKeyboardMarkup([
                        [
                            ['text' => "✅ Acconsento", 'callback_data' => 'yesPolicy'],
                            ['text' => "❌ Non Acconsento", 'callback_data' => 'noPolicy']
                        ],
                        [
                            ['text' => "🚫 Escludimi dal bot", 'callback_data' => "excludeMe"]
                        ]
                    ]);
                    $message = $this->bot->sendMessage($this->replyUser->getId(), "Per procedere, occorre acconsentire alla <a href=\"https://telegra.ph/Rainbow-Bot----Privacy-policy-08-04\">Privacy Policy</a>", "HTML", false, null, $keyboard);
                    if ($du->getLastPrivateMessage() != 0) $this->bot->deleteMessage(
                        $this->replyUser->getId(),
                        $du->getLastPrivateMessage()
                    );
                    $du->setOptedOut(false);
                    $du->setLastPrivateMessage($message->getMessageId());
                    $du->poke($this->replyUser);
                    break;
                case self::STATUS_STRAY_USER:
                    throw new NotFoundException(); // trigger stray user message
                    break;
                case self::STATUS_ASK_NAME:
                    if ($mustSet) $du->setUName(ellipses($this->message->getText()));
                    $du->save(false);
                    $this->askName($du);
                    try {
                        if ($mustSet) $this->bot->deleteMessage(
                            $this->replyUser->getId(),
                            $this->message->getMessageId()
                        );
                    } catch (\TelegramBot\Api\Exception $e) {
                    }
                    break;
                case self::STATUS_ASK_BIRTH:
                    $text = $this->message->getText();
                    $text = str_replace("-", "/", $text);
                    $text = str_replace(".", "/", $text);
                    $e = explode("/", $text);
                    $builtDate = strtotime($e[2] . "-" . $e[1] . "-" . $e[0]);
                    if ($mustSet) {
                        if (!ctype_digit($e[0])) $invalid = true;
                        elseif (!ctype_digit($e[1])) $invalid = true;
                        elseif (!ctype_digit($e[2])) $invalid = true;
                        else $invalid = !checkdate((int) $e[1], (int) $e[0], (int) $e[2]);
                        if (!$invalid) $du->setUbirthDate($builtDate);
                    } else {
                        $invalid = false;
                    }
                    $du->save(false);
                    $this->askBirthDate($du, $invalid);
                    try {
                        if ($mustSet) $this->bot->deleteMessage(
                            $this->replyUser->getId(),
                            $this->message->getMessageId()
                        );
                    } catch (\TelegramBot\Api\Exception $e) {
                    }
                    break;
                case self::STATUS_ASK_GENDER:
                    if ($mustSet) $du->setUGender(ellipses($this->message->getText()));
                    $du->save(false);
                    $this->askGender($du);
                    try {
                        if ($mustSet) $this->bot->deleteMessage(
                            $this->replyUser->getId(),
                            $this->message->getMessageId()
                        );
                    } catch (\TelegramBot\Api\Exception $e) {
                    }
                    break;
                case self::STATUS_ASK_ORIENT:
                    if ($mustSet) $du->setUOrient(ellipses($this->message->getText()));
                    $du->save(false);
                    $this->askOrient($du);
                    try {
                        if ($mustSet) $this->bot->deleteMessage(
                            $this->replyUser->getId(),
                            $this->message->getMessageId()
                        );
                    } catch (\TelegramBot\Api\Exception $e) {
                    }
                    break;
                case self::STATUS_ASK_WHERE:
                    if ($mustSet) $du->setUPlace(ellipses($this->message->getText()));
                    $du->save(false);
                    $this->askWhere($du);
                    try {
                        if ($mustSet) $this->bot->deleteMessage(
                            $this->replyUser->getId(),
                            $this->message->getMessageId()
                        );
                    } catch (\TelegramBot\Api\Exception $e) {
                    }
                    break;
                case self::STATUS_ASK_PVT:
                    if ($mustSet) $du->setUpvtChoice(ellipses($this->message->getText()));
                    $du->save(false);
                    $this->askPVT($du);

                    try {
                        if ($mustSet) $this->bot->deleteMessage(
                            $this->replyUser->getId(),
                            $this->message->getMessageId()
                        );
                    } catch (\TelegramBot\Api\Exception $e) {
                    }
                    break;

                case self::STATUS_ASK_REL:
                    if ($mustSet) $du->setURelationships(ellipses($this->message->getText()));
                    $du->save(false);
                    $this->askRel($du);
                    try {
                        if ($mustSet) $this->bot->deleteMessage(
                            $this->replyUser->getId(),
                            $this->message->getMessageId()
                        );
                    } catch (\TelegramBot\Api\Exception $e) {
                    }
                    break;
                case self::STATUS_ASK_BIO:
                    if ($mustSet) $du->setUBio(ellipses($this->message->getText(), 500));
                    $du->save(false);
                    $this->askBio($du);
                    try {
                        if ($mustSet) $this->bot->deleteMessage(
                            $this->replyUser->getId(),
                            $this->message->getMessageId()
                        );
                    } catch (\TelegramBot\Api\Exception $e) {
                    }
                    break;
                case self::STATUS_ENDED:
                    $this->completedMenu();
                    /*$this->bot->deleteMessage(
                        $this->replyUser->getId(),
                        $this->message->getMessageId()
                    );*/
                    break;
                case self::STATUS_EXCLUDED:
                    $this->excludeYes();
                    break;
                case self::STATUS_NOT_CONSENT:
                    $this->noPolicy();
                case self::STATUS_MESSAGE_SEND:
                    $this->sendMessageToGroups($this->message->getText());
                    break;
            }
        } catch (NotFoundException $e) {
            $message = $this->bot->sendMessage($this->replyUser->getId(), "Per usare questo bot devi avere prima partecipato ad un gruppo a cui appartiene.",  "HTML", false);
            $du = DatabaseUser::create($this->database, $this->replyUser);
            $du->setPrivateChatStatus(self::STATUS_STRAY_USER);
            $du->setLastPrivateMessage($message->getMessageId());
            $du->poke($this->replyUser);
        }
    }
    public static function isPrivateChatContextChecker($update): bool
    {
        if ($update instanceof Message) return $update->getChat()->getType() == "private";
        else return $update->getMessage()->getChat()->getType() == "private";
    }
    public static function isPublicChatContextChecker($update): bool
    {
        return !PrivateChat::isPrivateChatContextChecker($update);
    }
    public function sendMessageToGroups(string $data): void
    {
        $du = DatabaseUser::getByChat($this->database, $this->replyUser);
        $list = Group::listAll($this->database);
        $text = "Inviando a...\n\n";
        foreach ($list as $group) {
            $text .= "➡️ " . $group->getName();
            if ($group->getUsername()) $text .= " (" . $group->getUsername() . ")";
            $text .= "\n";
            try {
                $this->bot->sendMessage($group->getTelegramId(), $data, "HTML", false, null);
                $text .= ": ✅ OK\n";
            } catch (Exception $e) {
                $text .= ": ❌ Errore\n";
            }
        }
        $text .= "\n\n ... fatto";
        $keyboard = new InlineKeyboardMarkup([
            [
                ['text' => "✅Ok", 'callback_data' => 'endSet#save']
            ]
        ]);
        $message = $this->bot->sendMessage($this->replyUser->getId(), $text, "HTML", false, null, $keyboard);
        $du->setPrivateChatStatus(self::STATUS_ENDED);
        $du->setLastPrivateMessage($message->getMessageId());
        $du->save(false);
    }
    public function writeMessage(): void
    {
        $du = DatabaseUser::getByChat($this->database, $this->replyUser);
        $keyboard = new InlineKeyboardMarkup([
            [
                ['text' => "❌ Annulla", 'callback_data' => 'endSet#save']
            ]
        ]);
        $message = $this->bot->sendMessage($this->replyUser->getId(), "Scrivi un messaggio in formato HTML da inviare a <b>tutti i gruppi</b>", "HTML", false, null, $keyboard);
        if ($du->getLastPrivateMessage() != 0) $this->bot->deleteMessage(
            $this->replyUser->getId(),
            $du->getLastPrivateMessage()
        );
        $du->setPrivateChatStatus(self::STATUS_MESSAGE_SEND);
        $du->setLastPrivateMessage($message->getMessageId());
        $du->save(false);
    }
    public function exclude(): void
    {
        $du = DatabaseUser::getByChat($this->database, $this->replyUser);
        $keyboard = new InlineKeyboardMarkup([
            [
                ['text' => "✅ Escludimi", 'callback_data' => 'yesExclude'],
                ['text' => "❌ Annulla", 'callback_data' => 'noExclude']
            ]
        ]);
        $message = $this->bot->sendMessage($this->replyUser->getId(), "Con questa scelta il bot eliminetà i tuoi dati, non ti disturberà più e non salverà tuoi dati finchè tu non lo richiederai\n<b>Non sei sicurə?</b> Puoi sempre cambiare idea più tardi!", "HTML", false, null, $keyboard);
        if ($du->getLastPrivateMessage() != 0) $this->bot->deleteMessage(
            $this->replyUser->getId(),
            $du->getLastPrivateMessage()
        );
        $du->setLastPrivateMessage($message->getMessageId());
        $du->save(false);
    }



    private function getStateVal(DatabaseUser $u, string $c): ?string
    {
        if ($c == PrivateChat::STATUS_ASK_NAME) return $u->getUName();
        if ($c == PrivateChat::STATUS_ASK_BIRTH) return $u->getUbirthDate();
        if ($c == PrivateChat::STATUS_ASK_GENDER) return $u->getUGender();
        if ($c == PrivateChat::STATUS_ASK_ORIENT) return $u->getUOrient();
        if ($c == PrivateChat::STATUS_ASK_WHERE) return $u->getUPlace();
        if ($c == PrivateChat::STATUS_ASK_PVT) return $u->getUpvtChoice();
        if ($c == PrivateChat::STATUS_ASK_REL) return $u->getURelationships();
        if ($c == PrivateChat::STATUS_ASK_BIO) return $u->getUBio();
        return null;
    }

    public static array $order = [
        self::STATUS_ASK_NAME,
        self::STATUS_ASK_BIRTH,
        self::STATUS_ASK_GENDER,
        self::STATUS_ASK_ORIENT,
        self::STATUS_ASK_WHERE,
        self::STATUS_ASK_PVT,
        self::STATUS_ASK_REL,
        self::STATUS_ASK_BIO
    ];

    private function getPrevState(string $state): ?string
    {
        $index = array_search($state, self::$order);
        if ($index === false) throw new NotFoundException();
        if ($index === 0) return null;
        return self::$order[$index - 1];
    }

    private static function getNextState(string $state): ?string
    {
        $index = array_search($state, self::$order);
        if ($index === false) throw new NotFoundException();
        if ($index === count(self::$order) - 1) return null;
        return self::$order[$index + 1];
    }

    private static function getRemaining(string $state): int
    {
        $index = array_search($state, self::$order);
        return count(self::$order) - $index - 1;
    }

    private function getKeyboard(DatabaseUser $du, string $state, array $nextRows)
    {
        $list = [];
        $prev = $this->getPrevState($state);
        $next = $this->getNextState($state);
        if ($prev) {
            if ($this->getStateVal($du, $prev)) {
                $list[] = [['text' => "⏮️✏️ Indietro", 'callback_data' => 'go#' . $prev]];
            } else {
                $list[] = [['text' => "⏮️🆕 Indietro", 'callback_data' => 'go#' . $prev]];
            }
        }
        if ($this->getStateVal($du, $state))  $list[] = [['text' => "🔄 Reimposta", 'callback_data' => 'go#' . $state . "#reset"]];
        if ($next) {
            if ($this->getStateVal($du, $next)) {
                if (!$this->getStateVal($du, $state)) $list[] = [['text' => "⏭✏️ Avanti (salta questa)", 'callback_data' => 'go#' . $next]];
                else $list[] = [['text' => "⏭✏️ Avanti", 'callback_data' => 'go#' . $next]];
            } else {
                if (!$this->getStateVal($du, $state)) $list[] = [['text' => "⏭🆕 Avanti (salta questa)", 'callback_data' => 'go#' . $next]];
                else $list[] = [['text' => "⏭🆕 Avanti", 'callback_data' => 'go#' . $next]];
            }
            if ($du->countCompleted() == count(self::$order)) $list[] = [['text' => "✅ Fine", 'callback_data' => 'endSet']];
            else $list[] = [['text' => "✅ Fine (" . $du->countCompleted() . "/" . count(self::$order) . " domande completate)", 'callback_data' => 'endSet']];
        } else {
            $list[] = [['text' => "✅ Fine", 'callback_data' => 'endSet']];
        }
        $list = array_merge($list, $nextRows); // concat arrays
        return $list;
    }

    public function excludeYes()
    {
        $du = DatabaseUser::getByChat($this->database, $this->replyUser);
        if ($du->getLastPrivateMessage() != 0) $this->bot->deleteMessage(
            $this->replyUser->getId(),
            $du->getLastPrivateMessage()
        );
        $du = DatabaseUser::getByChat($this->database, $this->replyUser);
        $du->setPrivateChatStatus(self::STATUS_EXCLUDED);
        $du->setUName(null);
        $du->setUbirthDate(null);
        $du->setUGender(null);
        $du->setUOrient(null);
        $du->setUPlace(null);
        $du->setUpvtChoice(null);
        $du->setURelationships(null);
        $du->setUBio(null);
        $keyboard = new InlineKeyboardMarkup([
            [
                ['text' => "✅ Riabilitami", 'callback_data' => 'noExclude']
            ]
        ]);
        $message = $this->bot->sendMessage($this->replyUser->getId(), "La tua scelta è stata confermata. Io rimango qui se cambi idea.",  "HTML", false, null, $keyboard);
        $du->setLastPrivateMessage($message->getMessageId());
        $du->setOptedOut(true);
        $du->save(false);
    }
    public function completedMenu(): void
    {
        $du = DatabaseUser::getByChat($this->database, $this->replyUser);
        $keys = [
            [
                ['text' => "✏️" . ($du->getUName() ? "" : "🆕") . "Modifica Nome", 'callback_data' => 'go#' . self::STATUS_ASK_NAME],
                ['text' => "✏️" . ($du->getUbirthDate() ? "" : "🆕") . "Modifica Data di Nascita", 'callback_data' => 'go#' . self::STATUS_ASK_BIRTH]
            ],
            [
                ['text' => "✏️" . ($du->getUGender() ? "" : "🆕") . "Modifica Genere", 'callback_data' => 'go#' . self::STATUS_ASK_GENDER],
                ['text' => "✏️" . ($du->getUOrient() ? "" : "🆕") . "Modifica Orientamento", 'callback_data' => 'go#' . self::STATUS_ASK_ORIENT]
            ],
            [
                ['text' => "✏️" . ($du->getUPlace() ? "" : "🆕") . "Modifica Provenienza", 'callback_data' => 'go#' . self::STATUS_ASK_WHERE],
                ['text' => "✏️" . ($du->getUpvtChoice() ? "" : "🆕") . "Modifica Messaggi privati", 'callback_data' => 'go#' . self::STATUS_ASK_PVT]
            ],
            [
                ['text' => "✏️" . ($du->getURelationships() ? "" : "🆕") . "Modifica Relazioni", 'callback_data' => 'go#' . self::STATUS_ASK_REL],
                ['text' => "✏️" . ($du->getUBio() ? "" : "🆕") . "Modifica Bio", 'callback_data' => 'go#' . self::STATUS_ASK_BIO]
            ],
            [
                ['text' => "💳Visualizza Card", 'callback_data' => 'viewCard']
            ],
            [
                ['text' => "📄Visualizza Info", 'callback_data' => 'viewInfo']
            ],
            [
                ['text' => "🔍Visualizza Spiegazione", 'callback_data' => 'viewExp']
            ],
            [
                ['text' => "🚫 Rimuovi consenso", 'callback_data' => "excludeMe"]
            ]
        ];
        if ($this->replyUser->getId() == $GLOBALS["masterId"]) {
            $keys[] = [
                ['text' => "✏️ Scrivi messaggio", 'callback_data' => "writeMessage"]
            ];
        }
        $keyboard = new InlineKeyboardMarkup($keys);
        $message =  $this->bot->sendMessage(
            $this->replyUser->getId(),
            "<b>Menu</b>",
            "HTML",
            false,
            null,
            $keyboard
        );
        if ($du->getLastPrivateMessage() != 0) $this->bot->deleteMessage(
            $this->replyUser->getId(),
            $du->getLastPrivateMessage()
        );
        $du->setLastPrivateMessage($message->getMessageId());
        $du->save(false);
    }
    public function end($ask = false)
    {
        $du = DatabaseUser::getByChat($this->database, $this->replyUser);
        if (!$ask) {
            $du->setPrivateChatStatus(self::STATUS_ENDED);
            $add = "";
            /*$message =  $this->bot->sendMessage(
                $this->replyUser->getId(),
                "Abbiamo Finito!" . $add,
                "HTML",
                false,
                null
            );*/
            $du->save(false);
            $this->completedMenu();
        } else {
            $keyboard = new InlineKeyboardMarkup([
                [
                    ['text' => "✅Salva comunque", 'callback_data' => 'endSet#save']
                ],
                [
                    ['text' => "⏭✏️Torna alla modifica", 'callback_data' => 'go#' . $du->getPrivateChatStatus()]
                ],
                [
                    ['text' => "✏️Rivedi le domande", 'callback_data' => 'go#' . self::STATUS_ASK_NAME],
                ]
            ]);
            if ($du->getLastPrivateMessage() != 0) $this->bot->deleteMessage(
                $this->replyUser->getId(),
                $du->getLastPrivateMessage()
            );
            $add = $du->listUncompleted();
            $message =  $this->bot->sendMessage(
                $this->replyUser->getId(),
                "<b>Alcune domande non hanno risposta</b>:" . $add,
                "HTML",
                false,
                null,
                $keyboard
            );
            $du->setLastPrivateMessage($message->getMessageId());
            $du->save(false);
        }
    }
    public function yesPolicy()
    {
        $du = DatabaseUser::getByChat($this->database, $this->replyUser);
        if ($du->getLastPrivateMessage() != 0) $this->bot->deleteMessage(
            $this->replyUser->getId(),
            $du->getLastPrivateMessage()
        );
        $message =  $this->bot->sendMessage(
            $this->replyUser->getId(),
            "Grazie! Ora possiamo procedere a costruire il tuo profilo\nTi farò una serie di domande.\nRicordati che <b>puoi sempre saltare domande specifiche</b>\n Le risposte saranno visibili in tutti i gruppi che adottano questo bot.",
            "HTML"
        );
        $du->setLastPrivateMessage($message->getMessageId());
        $du->setPolicyAccepted(true);
        $du->save(false);
        $this->askName($du);
    }
    public function askName(DatabaseUser $du)
    {

        $du->setPrivateChatStatus(self::STATUS_ASK_NAME);
        $keyboard = new InlineKeyboardMarkup($this->getKeyboard($du, self::STATUS_ASK_NAME, []));
        if ($du->getLastPrivateMessage() != 0) $this->bot->deleteMessage(
            $this->replyUser->getId(),
            $du->getLastPrivateMessage()
        );
        $add = "";
        if ($du->getUName()) $add = "\nRisposta corrente: <i>" . $du->getUName() . "</i>";
        $message =  $this->bot->sendMessage(
            $this->replyUser->getId(),
            "<b>Come ti Chiami?</b> (massimo <b>40</b> caratteri) " . $add,
            "HTML",
            false,
            null,
            $keyboard
        );
        $du->setLastPrivateMessage($message->getMessageId());
        $du->save(false);
    }

    public function askBio(DatabaseUser $du)
    {
        $du->setPrivateChatStatus(self::STATUS_ASK_BIO);
        $keyboard = new InlineKeyboardMarkup($this->getKeyboard($du, self::STATUS_ASK_BIO, []));
        if ($du->getLastPrivateMessage() != 0) $this->bot->deleteMessage(
            $this->replyUser->getId(),
            $du->getLastPrivateMessage()
        );
        $add = "";
        if ($du->getUBio()) $add = "\nRisposta corrente: <i>" . $du->getUBio() . "</i>";
        $message =  $this->bot->sendMessage(
            $this->replyUser->getId(),
            "<b>Scrivi una breve descrizione di te stessə (bio)</b> (massimo <b>500</b> caratteri) " . $add,
            "HTML",
            false,
            null,
            $keyboard
        );
        $du->setLastPrivateMessage($message->getMessageId());
        $du->save(false);
    }
    public function askGender(DatabaseUser $du)
    {
        $du->setPrivateChatStatus(self::STATUS_ASK_GENDER);
        $keyboard = new InlineKeyboardMarkup($this->getKeyboard($du, self::STATUS_ASK_GENDER, [
            [
                ['text' => "Uomo", 'callback_data' => 'setGender#Uomo'],
                ['text' => "Donna", 'callback_data' => 'setGender#Donna'],
                ['text' => "Non-Binary", 'callback_data' => 'setGender#Non-Binary']
            ],
            [
                ['text' => "Donna Transgender (MtF)", 'callback_data' => 'setGender#Donna Transgender (MtF)'],
                ['text' => "Uomo Transgender (FtM)", 'callback_data' => 'setGender#Uomo Transgender (FtM)']
            ],
            [
                ['text' => "Non-Binary Transgender", 'callback_data' => 'setGender#Non-Binary Transgender'],
                ['text' => "Genderqueer", 'callback_data' => 'setGender#Genderqueer']
            ],
            [
                ['text' => "Demiboy", 'callback_data' => 'setGender#Demiboy'],
                ['text' => "Demigirl", 'callback_data' => 'setGender#Demigirl']
            ],
            [
                ['text' => "👉Imposta Personalizzato", 'callback_data' => 'setGender#custom']
            ]
        ]));
        if ($du->getLastPrivateMessage() != 0) $this->bot->deleteMessage(
            $this->replyUser->getId(),
            $du->getLastPrivateMessage()
        );
        $add = "";
        if ($du->getUGender()) $add = "\nRisposta corrente: <i>" . $du->getUGender() . "</i>";
        $message =  $this->bot->sendMessage(
            $this->replyUser->getId(),
            "<b>Quel'è il tuo genere?</b> (massimo <b>40</b> caratteri) " . $add,
            "HTML",
            false,
            null,
            $keyboard
        );
        $du->setLastPrivateMessage($message->getMessageId());
        $du->save(false);
    }

    public function askRel(DatabaseUser $du)
    {
        $du->setPrivateChatStatus(self::STATUS_ASK_REL);
        $keyboard = new InlineKeyboardMarkup($this->getKeyboard($du, self::STATUS_ASK_REL, [
            [
                ['text' => "Fidanzatə", 'callback_data' => 'setRel#Fidanzatə'],
                ['text' => "Single", 'callback_data' => 'setRel#Single'],
                ['text' => "Aiutatemi vi prego", 'callback_data' => 'setRel#Aiutatemi vi prego']
            ],
            [
                ['text' => "👉Imposta Personalizzato", 'callback_data' => 'setRel#custom']
            ]
        ]));
        if ($du->getLastPrivateMessage() != 0) $this->bot->deleteMessage(
            $this->replyUser->getId(),
            $du->getLastPrivateMessage()
        );
        $add = "";
        if ($du->getURelationships()) $add = "\nRisposta corrente: <i>" . $du->getURelationships() . "</i>";
        $message =  $this->bot->sendMessage(
            $this->replyUser->getId(),
            "<b>Relazioni?</b> (massimo <b>40</b> caratteri) " . $add,
            "HTML",
            false,
            null,
            $keyboard
        );
        $du->setLastPrivateMessage($message->getMessageId());
        $du->save(false);
    }
    public function askPVT(DatabaseUser $du)
    {
        $du->setPrivateChatStatus(self::STATUS_ASK_PVT);
        $keyboard = new InlineKeyboardMarkup($this->getKeyboard($du, self::STATUS_ASK_PVT, [
            [
                ['text' => "Sì", 'callback_data' => 'setPVT#Sì'],
                ['text' => "No", 'callback_data' => 'setPVT#No'],
                ['text' => "Solo se mi chiedono prima", 'callback_data' => 'setPVT#Solo se mi chiedono prima']
            ],
            [
                ['text' => "👉Imposta Personalizzato", 'callback_data' => 'setPVT#custom']
            ]
        ]));
        if ($du->getLastPrivateMessage() != 0) $this->bot->deleteMessage(
            $this->replyUser->getId(),
            $du->getLastPrivateMessage()
        );
        $add = "";
        if ($du->getUpvtChoice()) $add = "\nRisposta corrente: <i>" . $du->getUpvtChoice() . "</i>";
        $message =  $this->bot->sendMessage(
            $this->replyUser->getId(),
            "<b>Vuoi poter essere contattatə dagli altri utenti?</b> (massimo <b>40</b> caratteri) " . $add,
            "HTML",
            false,
            null,
            $keyboard
        );
        $du->setLastPrivateMessage($message->getMessageId());
        $du->save(false);
    }
    public function askWhere(DatabaseUser $du)
    {
        $du->setPrivateChatStatus(self::STATUS_ASK_WHERE);
        $keyboard = new InlineKeyboardMarkup($this->getKeyboard($du, self::STATUS_ASK_WHERE, [
            [
                ['text' => "Abruzzo", 'callback_data' => 'setWhere#Abruzzo'],
                ['text' => "Basilicata", 'callback_data' => 'setWhere#Basilicata'],
                ['text' => "Calabria", 'callback_data' => 'setWhere#Calabria']
            ],
            [
                ['text' => "Campania", 'callback_data' => 'setWhere#Campania'],
                ['text' => "Emilia-Romagna", 'callback_data' => 'setWhere#Emilia-Romagna'],
                ['text' => "Friuli-Venezia Giulia", 'callback_data' => 'setWhere#Friuli-Venezia Giulia']
            ],
            [
                ['text' => "Lazio", 'callback_data' => 'setWhere#Lazio'],
                ['text' => "Liguria", 'callback_data' => 'setWhere#Liguria'],
                ['text' => "Lombardia", 'callback_data' => 'setWhere#Lombardia']
            ],
            [
                ['text' => "Marche", 'callback_data' => 'setWhere#Marche'],
                ['text' => "Molise", 'callback_data' => 'setWhere#Molise'],
                ['text' => "Piemonte", 'callback_data' => 'setWhere#Piemonte']
            ],
            [
                ['text' => "Puglia", 'callback_data' => 'setWhere#Puglia'],
                ['text' => "Sardegna", 'callback_data' => 'setWhere#Sardegna'],
                ['text' => "Sicilia", 'callback_data' => 'setWhere#Sicilia']
            ],
            [
                ['text' => "Toscana", 'callback_data' => 'setWhere#Toscana'],
                ['text' => "Trentino-Alto Adige", 'callback_data' => 'setWhere#Trentino-Alto Adige'],
                ['text' => "Umbria", 'callback_data' => 'setWhere#Umbria']
            ],
            [
                ['text' => "Valle d'Aosta", 'callback_data' => 'setWhere#Valle d\'Aosta'],
                ['text' => "Veneto", 'callback_data' => 'setWhere#Veneto']
            ],
            [
                ['text' => "👉Imposta Personalizzato", 'callback_data' => 'setWhere#custom']
            ]
        ]));
        if ($du->getLastPrivateMessage() != 0) $this->bot->deleteMessage(
            $this->replyUser->getId(),
            $du->getLastPrivateMessage()
        );
        $add = "";
        if ($du->getUPlace()) $add = "\nRisposta corrente: <i>" . $du->getUPlace() . "</i>";
        $message =  $this->bot->sendMessage(
            $this->replyUser->getId(),
            "<b>Da dove vieni?</b> (massimo <b>40</b> caratteri) " . $add,
            "HTML",
            false,
            null,
            $keyboard
        );
        $du->setLastPrivateMessage($message->getMessageId());
        $du->save(false);
    }
    public function askOrient(DatabaseUser $du)
    {
        $du->setPrivateChatStatus(self::STATUS_ASK_ORIENT);
        $keyboard = new InlineKeyboardMarkup($this->getKeyboard($du, self::STATUS_ASK_ORIENT, [
            [
                ['text' => "Etero", 'callback_data' => 'setOrient#Etero'],
                ['text' => "Gay", 'callback_data' => 'setOrient#Gay'],
                ['text' => "Bisessuale", 'callback_data' => 'setOrient#Bisessuale']
            ],
            [
                ['text' => "Lesbica", 'callback_data' => 'setOrient#Lesbica'],
                ['text' => "Pansessuale", 'callback_data' => 'setOrient#Pansessuale'],
                ['text' => "Asessuale", 'callback_data' => 'setOrient#Asessuale']
            ],
            [

                ['text' => "Demisessuale", 'callback_data' => 'setOrient#Demisessuale'],
                ['text' => "Questioning", 'callback_data' => 'setOrient#Questioning'],
            ],
            [
                ['text' => "👉Imposta Personalizzato", 'callback_data' => 'setOrient#custom']
            ]
        ]));
        if ($du->getLastPrivateMessage() != 0) $this->bot->deleteMessage(
            $this->replyUser->getId(),
            $du->getLastPrivateMessage()
        );
        $add = "";
        if ($du->getUOrient()) $add = "\nRisposta corrente: <i>" . $du->getUOrient() . "</i>";
        $message =  $this->bot->sendMessage(
            $this->replyUser->getId(),
            "<b>Quel'è il tuo orientamento?</b>  (massimo <b>40</b> caratteri) " . $add,
            "HTML",
            false,
            null,
            $keyboard
        );
        $du->setLastPrivateMessage($message->getMessageId());
        $du->save(false);
    }
    public function askBirthDate(DatabaseUser $du, bool $invalid = false)
    {
        $du->setPrivateChatStatus(self::STATUS_ASK_BIRTH);

        $keyboard = new InlineKeyboardMarkup($this->getKeyboard($du, self::STATUS_ASK_BIRTH, []));
        if ($du->getLastPrivateMessage() != 0) $this->bot->deleteMessage(
            $this->replyUser->getId(),
            $du->getLastPrivateMessage()
        );
        $add = "";
        if ($invalid) $add .= "\n<b>Data invalida</b>";
        if ($du->getUbirthDate()) $add .= "\nRisposta corrente: <i>" . date("d/m/Y", $du->getUbirthDate()) . " (" . $du->getAge() . " anni)</i>";
        $message =  $this->bot->sendMessage(
            $this->replyUser->getId(),
            "Quando Sei Natə?\n<b>Formato data: giorno/mese/anno</b>" . $add,
            "HTML",
            false,
            null,
            $keyboard
        );
        $du->setLastPrivateMessage($message->getMessageId());
        $du->save(false);
    }
    public function noPolicy()
    {
        $du = DatabaseUser::getByChat($this->database, $this->replyUser);
        $du->setPrivateChatStatus(self::STATUS_NOT_CONSENT);
        if ($du->getLastPrivateMessage() != 0) $this->bot->deleteMessage(
            $this->replyUser->getId(),
            $du->getLastPrivateMessage()
        );
        $keyboard = new InlineKeyboardMarkup([
            [
                ['text' => "✅ Ho cambiato idea", 'callback_data' => 'changedIdea']
            ]
        ]);
        $message = $this->bot->sendMessage(
            $this->replyUser->getId(),
            "<b>Nessun Problema</b>, torna se hai cambiato idea. \nTi lascio la mia  <a href=\"https://telegra.ph/Rainbow-Bot----Privacy-policy-08-04\">privacy policy</a> da consultare",
            "HTML",
            false,
            null,
            $keyboard
        );
        $du->setLastPrivateMessage($message->getMessageId());
        $du->save(false);
    }
}
