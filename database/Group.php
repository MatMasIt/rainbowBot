<?php

use TelegramBot\Api\Types\Message;
use \TelegramBot\Api\Client;

class Group
{
    private int $id;
    private int $telegramId;
    private string $name, $username;
    private int $lastUpdate, $creation;


    public static function listAll(Database $database): array
    {
        $list = [];
        $q = $database->getPdo()->prepare("SELECT * FROM Groups");
        $q->execute();
        while ($r = $q->fetch(PDO::FETCH_ASSOC)) {
            $g = new Group();
            $g->setId((int) $r["id"]);
            $g->setTelegramId((int) $r["telegramId"]);
            $g->setName((string) $r["name"]);
            $g->setUsername((string) $r["username"]);
            $g->setLastUpdate((int) $r["lastUpdate"]);
            $g->setCreation((int) $r["creation"]);
            $list[] = $g;
        }
        return $list;
    }

    public static function poke(Database $database, Message $message, DatabaseUser $du): array
    {
        $res = [];
        $group = $message->getChat();
        $q = $database->getPdo()->prepare("SELECT id FROM Groups WHERE telegramId=:telegramId");
        $q->execute([":telegramId" => $group->getId()]);
        $r = $q->fetch(PDO::FETCH_ASSOC);
        if ($r) {
            $q = $database->getPdo()->prepare("UPDATE Groups SET telegramId=:telegramId, name=:name, username=:username, lastUpdate=:lastUpdate WHERE id=:id");
            $q->execute([
                ":telegramId" => $group->getId(),
                ":name" => $group->getTitle(),
                ":username" => $group->getUsername(),
                ":lastUpdate" => time(),
                ":id" => $r["id"]
            ]);
            $res[] = false;
        } else {
            $q = $database->getPdo()->prepare("INSERT INTO Groups(telegramId, name, username, lastUpdate, creation) VALUES(:telegramId, :name, :username, :lastUpdate, :creation)");
            $q->execute([
                ":telegramId" => $group->getId(),
                ":name" => $group->getTitle(),
                ":username" => $group->getUsername(),
                ":lastUpdate" => time(),
                ":creation" => time()
            ]);
            $res[] = true;
        }
        $q = $database->getPdo()->prepare("SELECT * FROM UserGroupLink WHERE groupId=:groupId AND userId=:userId");
        $q->execute([":groupId" => $group->getId(), ":userId" => $message->getFrom()->getId()]);
        $r = $q->fetch(PDO::FETCH_ASSOC);
        if ($r) {
            $q = $database->getPdo()->prepare("UPDATE UserGroupLink SET userId=:userId, groupId=:groupId, lastSeen=:lastSeen WHERE id=:id");
            $q->execute([
                ":userId" => $message->getFrom()->getId(),
                ":groupId" => $group->getId(),
                ":lastSeen" => time(),
                ":id" => $r["id"]
            ]);

            $res[] = false;
        } else {
            $q = $database->getPdo()->prepare("INSERT INTO UserGroupLink(userId, groupId, firstSeen, lastSeen) VALUES(:userId, :groupId, :firstSeen, :lastSeen)");
            $q->execute([
                ":userId" => $message->getFrom()->getId(),
                ":groupId" => $group->getId(),
                ":firstSeen" => time(),
                ":lastSeen" => time()
            ]);
            if ($du->getPrivateChatStatus() == PrivateChat::STATUS_EXCLUDED) $res[] = false;
            else $res[] = true;
        }
        return $res;
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
}
