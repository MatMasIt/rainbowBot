<?php
require_once "common.php";
use TelegramBot\Api\Types\User;
/**
 * A user in the database
 */
class DatabaseUser
{

    private int $id, $telegramId;
    private string $name, $username;
    private string $privateChatStatus;
    private string $editAction;
    private bool $policyAccepted, $optedOut;
    private int $lastUpdate, $lastAccess, $creation;
    private int $lastPrivateMessage;
    private Database $database;
    private ?string $uName, $uGender, $uOrient, $uPlace, $upvtChoice, $uRelationships, $uBio;
    private ?int $ubirthDate;

    public function __construct(Database $database)
    {
        $this->database = $database;
    }

    public static function calcAge(int $unix): int
    {
        $tz  = new DateTimeZone('Europe/Brussels');
        return (int) DateTime::createFromFormat('U', $unix, $tz)
            ->diff(new DateTime('now', $tz))
            ->y;
    }

    public function getAge(): int
    {
        return self::calcAge($this->getUbirthDate());
    }

    /**
     * Determines the appropriate flag
     */
    public function getFlags(): array
    {
        $re = [];
        $o = strtoupper($this->getUOrient() . " " . $this->getUGender() . " " . $this->getUPlace());
        if (contains("LESB", $o)) $re[] = "lesbian";
        if ("BI" == explode(" ", $o)[0]) $re[] = "bi";
        if (contains("FLUID", $o)) $re[] = "fluid";
        if (contains("ACE", $o)) $re[] = "asex";
        if (contains("GENDERQ", $o)) $re[] = "gqueer";
        if (contains("ARO", $o)) $re[] = "aro";
        if (contains("ASE", $o)) $re[] = "asex";
        if (contains("GAY", $o)) $re[] = "gay";
        if (contains("BIS", $o)) $re[] = "bi";
        if (contains("BSX", $o)) $re[] = "bi";
        if (contains("PAN", $o)) $re[] = "pan";
        if (contains("LELL", $o)) $re[] = "lesbian";
        if (contains("OMNI", $o)) $re[] = "omni";
        if (contains("QUESTIONING", $o)) $re[] = "questioning";
        if (contains("DEMIS", $o)) $re[] = "demi";
        if (contains("NB", $o) || contains("NON BINARY", $o)) $re[] = "nb";
        if (contains("ETERO", $o) || contains("HET", $o)) $re[] = "etero";
        if (contains("T4T", $o)) $re[] = "t4t";
        //orientation, romantic
        if (contains("BIROM", $o)) $re[] = "Biromantic";
        if (contains("DEMIG", $o)) $re[] = "demigirl";
        if (contains("MLM", $o)) $re[] = "mlm";
        //gender-related
        if (contains("MTF", $o) || contains("FTM", $o)  || contains("TRANS", $o)) $re[] = "trans";
        //miscellaneous
        if (contains("FEMB", $o)) $re[] = "femboy";
        if (contains("PIEMONTE", $o)) {
            $re[] = "piemonte";
            $re[] = "eu";
            $re[] = "it";
        }
        if (contains("LOMBARDIA", $o)) {
            $re[] = "lombardia";
            $re[] = "eu";
            $re[] = "it";
        }
        if (contains("VENETO", $o)) {
            $re[] = "veneto";
            $re[] = "eu";
            $re[] = "it";
        }
        if (contains("FRIULI", $o)) {
            $re[] = "friuli";
            $re[] = "eu";
            $re[] = "it";
        }
        if (contains("TRENTINO", $o)) {
            $re[] = "trentino";
            $re[] = "eu";
            $re[] = "it";
        }
        if (contains("LIGURIA", $o)) {
            $re[] = "liguria";
            $re[] = "eu";
            $re[] = "it";
        }
        if (contains("EMILIA", $o)) {
            $re[] = "emilia";
            $re[] = "eu";
            $re[] = "it";
        }
        if (contains("AOSTA", $o)) {
            $re[] = "valleAosta";
            $re[] = "eu";
            $re[] = "it";
        }
        if (contains("TOSCANIA", $o)) {
            $re[] = "toscania";
            $re[] = "eu";
            $re[] = "it";
        }
        if (contains("MARCHE", $o)) {
            $re[] = "marche";
            $re[] = "eu";
            $re[] = "it";
        }
        if (contains("ABRUZZO", $o)) {
            $re[] = "abruzzo";
            $re[] = "eu";
            $re[] = "it";
        }
        if (contains("MOLISE", $o)) {
            $re[] = "molise";
            $re[] = "eu";
            $re[] = "it";
        }
        if (contains("LAZIO", $o)) {
            $re[] = "lazio";
            $re[] = "eu";
            $re[] = "it";
        }
        if (contains("CAMPANIA", $o)) {
            $re[] = "campania";
            $re[] = "eu";
            $re[] = "it";
        }
        if (contains("BASILICATA", $o)) {
            $re[] = "basilicata";
            $re[] = "eu";
            $re[] = "it";
        }
        if (contains("CALABRIA", $o)) {
            $re[] = "calabria";
            $re[] = "eu";
            $re[] = "it";
        }
        if (contains("PUGLIA", $o)) {
            $re[] = "puglia";
            $re[] = "eu";
            $re[] = "it";
        }
        if (contains("SICILIA", $o)) {
            $re[] = "sicilia";
            $re[] = "eu";
            $re[] = "it";
        }
        if (contains("SARDEGNA", $o)) {
            $re[] = "sardegna";
            $re[] = "eu";
            $re[] = "it";
        }
        if (contains("UMBRIA", $o)) {
            $re[] = "umbria";
            $re[] = "eu";
            $re[] = "it";
        }
        if (contains("EU", $o)) $re[] = "eu";
        if (contains("ITALIA", $o)) $re[] = "it";
        if (contains("FRANCIA", $o)) $re[] = "fr";
        if (contains("GERMANIA", $o)) $re[] = "de";
        if (contains("SLOVENIA", $o)) $re[] = "slovenia";
        return array_unique($re);
    }
    /**
     * Get the value of id
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set the value of id
     *
     * @return  self
     */
    public function setId($id)
    {
        $this->id = $id;

        return $this;
    }

    /**
     * Get the value of telegramId
     */
    public function getTelegramId()
    {
        return $this->telegramId;
    }

    /**
     * Set the value of telegramId
     *
     * @return  self
     */
    public function setTelegramId($telegramId)
    {
        $this->telegramId = $telegramId;

        return $this;
    }

    /**
     * Get the value of name
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Set the value of name
     *
     * @return  self
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * Get the value of username
     */
    public function getUsername()
    {
        return $this->username;
    }

    /**
     * Set the value of username
     *
     * @return  self
     */
    public function setUsername($username)
    {
        $this->username = $username;

        return $this;
    }


    /**
     * Get the value of privateChatStatus
     */
    public function getPrivateChatStatus()
    {
        return $this->privateChatStatus;
    }

    /**
     * Set the value of privateChatStatus
     *
     * @return  self
     */
    public function setPrivateChatStatus($privateChatStatus)
    {
        $this->privateChatStatus = $privateChatStatus;

        return $this;
    }

    /**
     * Get the value of editAction
     */
    public function getEditAction()
    {
        return $this->editAction;
    }

    /**
     * Set the value of editAction
     *
     * @return  self
     */
    public function setEditAction($editAction)
    {
        $this->editAction = $editAction;

        return $this;
    }

    /**
     * Get the value of policyAccepted
     */
    public function getPolicyAccepted()
    {
        return $this->policyAccepted;
    }

    /**
     * Set the value of policyAccepted
     *
     * @return  self
     */
    public function setPolicyAccepted($policyAccepted)
    {
        $this->policyAccepted = $policyAccepted;

        return $this;
    }

    /**
     * Get the value of lastUpdate
     */
    public function getLastUpdate()
    {
        return $this->lastUpdate;
    }

    /**
     * Set the value of lastUpdate
     *
     * @return  self
     */
    public function setLastUpdate($lastUpdate)
    {
        $this->lastUpdate = $lastUpdate;

        return $this;
    }

    /**
     * Get the value of creation
     */
    public function getCreation()
    {
        return $this->creation;
    }

    /**
     * Set the value of creation
     *
     * @return  self
     */
    public function setCreation($creation)
    {
        $this->creation = $creation;

        return $this;
    }

    /**
     * Get the value of optedOut
     */
    public function getOptedOut()
    {
        return $this->optedOut;
    }

    /**
     * Set the value of optedOut
     *
     * @return  self
     */
    public function setOptedOut($optedOut)
    {
        $this->optedOut = $optedOut;

        return $this;
    }

    public static function getByChat(Database $database, User $user): DatabaseUser
    {
        $q = $database->getPdo()->prepare("SELECT * FROM Users WHERE telegramId = :telegramId");
        +$q->execute([":telegramId" => $user->getId()]);
        $r = $q->fetch(PDO::FETCH_ASSOC);
        if ($r == null) throw new NotFoundException();
        $u = new DatabaseUser($database);
        $u->setId((int) $r["id"]);
        $u->setTelegramId((int) $r["telegramId"]);
        $u->setName($r["name"]);
        $u->setUsername($r["username"]);
        $u->setPrivateChatStatus($r["privateChatStatus"]);
        $u->setEditAction($r["editAction"]);
        $u->setOptedOut((bool) $r["optedOut"]);
        $u->setPolicyAccepted((bool) $r["policyAccepted"]);
        $u->setLastUpdate((int) $r["lastUpdate"]);
        $u->setCreation((int) $r["creation"]);
        $u->setLastPrivateMessage((int) $r["lastPrivateMessage"]);
        $u->setUName($r["uName"]);
        $u->setUbirthDate($r["ubirthDate"]);
        $u->setUGender($r["ugender"]);
        $u->setUOrient($r["uorient"]);
        $u->setUPlace($r["uplace"]);
        $u->setUpvtChoice($r["upvtChoice"]);
        $u->setURelationships($r["uRelationships"]);
        $u->setUBio($r["uBio"]);
        $u->poke($user);
        return $u;
    }

    public static function create(Database $database, User $user): DatabaseUser
    {
        $u = new DatabaseUser($database); // TODO FINISH IMPLEMENTING
        $u->setTelegramId($user->getId());
        $u->setName($user->getFirstName() . " " . $user->getLastName());
        $u->setUsername($user->getUsername());
        $u->setPrivateChatStatus(PrivateChat::STATUS_ASK_DM);
        $u->setEditAction("");
        $u->setOptedOut(false);
        $u->setPolicyAccepted(false);
        $u->setLastUpdate(time());
        $u->setCreation(time());
        $u->setLastPrivateMessage(0);
        $u->setUName(null);
        $u->setUbirthDate(null);
        $u->setUGender(null);
        $u->setUOrient(null);
        $u->setUPlace(null);
        $u->setUpvtChoice(null);
        $u->setURelationships(null);
        $u->setUBio(null);
        $u->save(true);
        return $u;
    }

    public function setNULL(string $step): void
    {
        if ($step == PrivateChat::STATUS_ASK_NAME) $this->setUName(null);
        if ($step == PrivateChat::STATUS_ASK_BIRTH) $this->setUbirthDate(null);
        if ($step == PrivateChat::STATUS_ASK_GENDER) $this->setUGender(null);
        if ($step == PrivateChat::STATUS_ASK_ORIENT)  $this->setUOrient(null);
        if ($step == PrivateChat::STATUS_ASK_WHERE) $this->setUPlace(null);
        if ($step == PrivateChat::STATUS_ASK_PVT) $this->setUpvtChoice(null);
        if ($step == PrivateChat::STATUS_ASK_REL)  $this->setURelationships(null);
        if ($step == PrivateChat::STATUS_ASK_BIO)  $this->setUBio(null);
    }

    public function listUncompleted(): string
    {
        $list = "";
        if (!$this->getUName()) $list .= "\n➡ Nome";
        if (!$this->getUbirthDate()) $list .= "\n➡ Data di nascita";
        if (!$this->getUGender()) $list .= "\n➡ Genere";
        if (!$this->getUOrient()) $list .= "\n➡ Orientamento";
        if (!$this->getUPlace()) $list .= "\n➡ Provenienza";
        if (!$this->getUpvtChoice()) $list .= "\n➡ Messaggi privati";
        if (!$this->getURelationships()) $list .= "\n➡ Relazioni";
        if (!$this->getUBio()) $list .= "\n➡ Bio";
        return $list;
    }
    public function countCompleted(): int
    {
        $tot = 0;
        if ($this->getUName()) $tot++;
        if ($this->getUbirthDate()) $tot++;
        if ($this->getUGender()) $tot++;
        if ($this->getUOrient()) $tot++;
        if ($this->getUPlace()) $tot++;
        if ($this->getUpvtChoice()) $tot++;
        if ($this->getURelationships()) $tot++;
        if ($this->getUBio()) $tot++;
        return $tot;
    }

    public function poke(User $user): void
    {
        $this->setTelegramId($user->getId());
        $this->setName($user->getFirstName() . " " . $user->getLastName());
        $this->setUsername($user->getUsername());
        $this->setLastAccess(time());
        $this->save(false);
    }

    public function save(bool $isNew): void
    {
        $exec = [
            ":telegramId" => $this->getTelegramId(),
            ":name" => $this->getName(),
            ":username" => $this->getUsername(),
            ":privateChatStatus" => $this->getPrivateChatStatus(),
            ":editAction" => $this->getEditAction(),
            ":optedOut" => $this->getOptedOut(),
            ":policyAccepted" => $this->getPolicyAccepted(),
            ":lastUpdate" => time(),
            ":lastAccess" => time(),
            ":lastPrivateMessage" => $this->getLastPrivateMessage(),
            ":uName" => $this->getUName(),
            ":ubirthDate" => $this->getUbirthDate(),
            ":ugender" => $this->getuGender(),
            ":uorient" => $this->getUOrient(),
            ":uplace" => $this->getUPlace(),
            ":upvtChoice" => $this->getUpvtChoice(),
            ":uRelationships" => $this->getURelationships(),
            ":uBio" => $this->getUBio()
        ];
        if (!$isNew) {
            $q = $this->getDatabase()->getPdo()->prepare("UPDATE Users SET telegramId = :telegramId, name=:name, username=:username, privateChatStatus=:privateChatStatus, editAction=:editAction, optedOut=:optedOut, policyAccepted=:policyAccepted, lastUpdate=:lastUpdate, lastAccess=:lastAccess, lastPrivateMessage=:lastPrivateMessage, uName=:uName, ubirthDate=:ubirthDate, ugender=:ugender, uorient=:uorient, uplace=:uplace, upvtChoice=:upvtChoice, uRelationships=:uRelationships, uBio=:uBio WHERE telegramId=:telegramId");
            $exec[":telegramId"] = $this->getTelegramId();
        } else {
            $q = $this->getDatabase()->getPdo()->prepare("INSERT INTO Users(telegramId, name, username, privateChatStatus, editAction, optedOut, policyAccepted, lastUpdate, lastAccess, lastPrivateMessage, creation, uName, ubirthDate, ugender, uorient, uplace, upvtChoice, uRelationships, uBio) VALUES(:telegramId, :name, :username, :privateChatStatus, :editAction, :optedOut, :policyAccepted, :lastUpdate, :lastAccess, :lastPrivateMessage, :creation, :uName, :ubirthDate, :ugender, :uorient, :uplace, :upvtChoice, :uRelationships, :uBio)");
            $exec[":creation"] = time();
        }
        $q->execute($exec);
    }

    /**
     * Get the value of lastAccess
     */
    public function getLastAccess()
    {
        return $this->lastAccess;
    }

    /**
     * Set the value of lastAccess
     *
     * @return  self
     */
    public function setLastAccess($lastAccess)
    {
        $this->lastAccess = $lastAccess;

        return $this;
    }

    /**
     * Get the value of database
     */
    public function getDatabase()
    {
        return $this->database;
    }

    /**
     * Set the value of database
     *
     * @return  self
     */
    public function setDatabase($database)
    {
        $this->database = $database;

        return $this;
    }

    /**
     * Get the value of lastPrivateMessage
     */
    public function getLastPrivateMessage()
    {
        return $this->lastPrivateMessage;
    }

    /**
     * Set the value of lastPrivateMessage
     *
     * @return  self
     */
    public function setLastPrivateMessage($lastPrivateMessage)
    {
        $this->lastPrivateMessage = $lastPrivateMessage;

        return $this;
    }

    /**
     * Get the value of uName
     */
    public function getUName()
    {
        return $this->uName;
    }

    /**
     * Set the value of uName
     *
     * @return  self
     */
    public function setUName($uName)
    {
        $this->uName = $uName;

        return $this;
    }

    /**
     * Get the value of ubirthDate
     */
    public function getUbirthDate()
    {
        return $this->ubirthDate;
    }

    /**
     * Set the value of ubirthDate
     *
     * @return  self
     */
    public function setUbirthDate($ubirthDate)
    {
        $this->ubirthDate = $ubirthDate;

        return $this;
    }

    /**
     * Get the value of uGender
     */
    public function getUGender()
    {
        return $this->uGender;
    }

    /**
     * Set the value of uGender
     *
     * @return  self
     */
    public function setUGender($uGender)
    {
        $this->uGender = $uGender;

        return $this;
    }

    /**
     * Get the value of uOrient
     */
    public function getUOrient()
    {
        return $this->uOrient;
    }

    /**
     * Set the value of uOrient
     *
     * @return  self
     */
    public function setUOrient($uOrient)
    {
        $this->uOrient = $uOrient;

        return $this;
    }

    /**
     * Get the value of uPlace
     */
    public function getUPlace()
    {
        return $this->uPlace;
    }

    /**
     * Set the value of uPlace
     *
     * @return  self
     */
    public function setUPlace($uPlace)
    {
        $this->uPlace = $uPlace;

        return $this;
    }

    /**
     * Get the value of upvtChoice
     */
    public function getUpvtChoice()
    {
        return $this->upvtChoice;
    }

    /**
     * Set the value of upvtChoice
     *
     * @return  self
     */
    public function setUpvtChoice($upvtChoice)
    {
        $this->upvtChoice = $upvtChoice;

        return $this;
    }

    /**
     * Get the value of uRelationships
     */
    public function getURelationships()
    {
        return $this->uRelationships;
    }

    /**
     * Set the value of uRelationships
     *
     * @return  self
     */
    public function setURelationships($uRelationships)
    {
        $this->uRelationships = $uRelationships;

        return $this;
    }

    /**
     * Get the value of uBio
     */
    public function getUBio()
    {
        return $this->uBio;
    }

    /**
     * Set the value of uBio
     *
     * @return  self
     */
    public function setUBio($uBio)
    {
        $this->uBio = $uBio;

        return $this;
    }
}
