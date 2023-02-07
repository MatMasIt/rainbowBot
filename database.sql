BEGIN TRANSACTION;
CREATE TABLE IF NOT EXISTS "BotChatLog" (
	"id"	INTEGER NOT NULL,
	"personTelegramId"	INTEGER,
	"message"	TEXT,
	FOREIGN KEY("personTelegramId") REFERENCES "Users"("telegramId")
);
CREATE TABLE IF NOT EXISTS "UserGroupLink" (
	"id"	INTEGER NOT NULL UNIQUE,
	"userId"	INTEGER NOT NULL,
	"groupId"	INTEGER NOT NULL,
	"firstSeen"	INTEGER NOT NULL,
	"lastSeen"	INTEGER NOT NULL,
	FOREIGN KEY("groupId") REFERENCES "Groups"("id"),
	FOREIGN KEY("userId") REFERENCES "Users"("id"),
	PRIMARY KEY("id" AUTOINCREMENT)
);
CREATE TABLE IF NOT EXISTS "Groups" (
	"id"	INTEGER NOT NULL UNIQUE,
	"telegramId"	INTEGER NOT NULL UNIQUE,
	"name"	TEXT NOT NULL,
	"username"	TEXT,
	"lastUpdate"	INTEGER NOT NULL,
	"creation"	INTEGER NOT NULL,
	PRIMARY KEY("id" AUTOINCREMENT)
);
CREATE TABLE IF NOT EXISTS "Users" (
	"id"	INTEGER NOT NULL UNIQUE,
	"telegramId"	INTEGER NOT NULL UNIQUE,
	"name"	TEXT NOT NULL,
	"username"	TEXT NOT NULL,
	"privateChatStatus"	TEXT NOT NULL,
	"editAction"	TEXT NOT NULL,
	"optedOut"	INTEGER NOT NULL DEFAULT 0,
	"policyAccepted"	INTEGER NOT NULL DEFAULT 0,
	"lastUpdate"	INTEGER NOT NULL,
	"lastAccess"	INTEGER,
	"lastPrivateMessage"	INTEGER NOT NULL DEFAULT 0,
	"creation"	INTEGER NOT NULL,
	"uName"	TEXT,
	"ubirthDate"	TEXT,
	"ugender"	TEXT,
	"uorient"	TEXT,
	"uplace"	TEXT,
	"upvtChoice"	TEXT,
	"uRelationships"	TEXT,
	"uBio"	TEXT,
	PRIMARY KEY("id" AUTOINCREMENT)
);
COMMIT;
