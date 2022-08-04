<?php
ini_set('error_log', 'errors.log');
require_once("vendor/autoload.php");
require("API.php");
require("gdImg/gd.php");
$GLOBALS["config"] = require("../config.php");

function is_404($url)
{
    $handle = curl_init($url);
    curl_setopt($handle, CURLOPT_RETURNTRANSFER, true); /* Get the HTML or whatever is linked in $url. */
    $response = curl_exec($handle); /* Check for 404 (file not found). */
    $httpCode = curl_getinfo($handle, CURLINFO_HTTP_CODE);
    /* Handle 404 here. */
    curl_close($handle);
    return $httpCode == 404;
}
function ymd($dmy)
{
    $d = explode("/", $dmy);
    if (count($d) < 3) {
        return 0;
    }
    return $d[2] . "-" . $d[1] . "-" . $d[0];
}
function contains($needle, $haystack)
{
    return (strpos($haystack, $needle) !== false);
}
function orientDerivate($people)
{
    $d = [];
    foreach ($people->findAll() as $p) {
        $f = true;
        $o = strtoupper($p->orient);
        if (contains("LESB", $o)) {
            $d["Lesbiche"] += 1;
            $f = false;
        }
        if (contains("BI", $o)) {
            $d["Bisex"] += 1;
            $f = false;
        }
        if (contains("ACE", $o)) {
            $d["Asex"] += 1;
            $f = false;
        }
        if (contains("ASE", $o)) {
            $d["Asex"] += 1;
            $f = false;
        }
        if (contains("GAY", $o)) {
            $d["Gay"] += 1;
            $f = false;
        }
        if (contains("BSX", $o)) {
            $d["Bisex"] += 1;
            $f = false;
        }
        if (contains("PAN", $o)) {
            $d["Pansex"] += 1;
            $f = false;
        }
        if (contains("LELL", $o)) {
            $d["Lesbiche"] += 1;
            $f = false;
        }
        if (contains("OMNI", $o)) {
            $d["Ommisex"] += 1;
            $f = false;
        }
        if (contains("QUEST", $o)) {
            $d["Questioning"] += 1;
            $f = false;
        }
        if (contains("ETERO", $o) || contains("HET", $o)) {
            $d["Etero"] += 1;
            $f = false;
        }
        //if ($f) $d["Altro"] += 1;


    }
    return $d;
}
function purge($people, $groups)
{
    $i = 0;
    foreach ($people->findAll() as $u) {
        $id = $u->getId();
        $inGroup = true;
        foreach ($groups as $n) {
            $r = API("getChatMember", ["chat_id" => $n, "user_id" => $id]);
            $inGroup = $r["description"] !== "Bad Request: user not found";
            if ($inGroup !== false) {
                break;
            }
        }
        if ($inGroup === false) {
            $i++;
            $u->delete();
        }
    }
    return $i;
}
function secondsToTime($seconds)
{
    $dtF = new \DateTime('@0');
    $dtT = new \DateTime("@$seconds");
    return $dtF->diff($dtT)->format($GLOBALS["config"]["lgbt"]["text"]["timeDiffFormat"]);
}
function isInGroup($n, $id)
{
    $r = API("getChatMember", ["chat_id" => $n, "user_id" => $id]);
    return  $r["description"] !== "Bad Request: user not found";
}
function maintenance($m)
{
    if ($m === false) return false;
    if (gettype($m) == "integer") {
        if ($m < time()) return false;
        return $GLOBALS["config"]["lgbt"]["text"]["scheduledMaintenance"] . secondsToTime($m - time());
    }
    return $GLOBALS["config"]["lgbt"]["text"]["maintenance"];
}
function moduleOn($name, $chatId, $DATA, $alr = true)
{
    if ($GLOBALS["config"]["lgbt"]["on"] == false) return false;
    if ($tk = maintenance($GLOBALS["config"]["lgbt"]["maintenance"])) {
        if ($alr) {
            if (str_split($DATA["message"]["text"])[0] == ".") {
                API("sendMessage", ["chat_id" => $DATA["message"]["chat"]["id"], "text" =>  $tk, "reply_to_message_id" => $DATA["message"]["message_id"]]);
            }
        }
        return false;
    }
    if (!$GLOBALS["config"]["lgbt"]["modulesEnabled"][$name]) return false;
    foreach ($GLOBALS["config"]["lgbt"]["groups"] as $g) {
        if ($g["id"] == $chatId && in_array($name, array_keys($g["modulesEnabled"])) && !$g["modulesEnabled"][$name]) return false;
    }
    return true;
}

function is_admin($id, $chat)
{
    $res = API("getChatMember", ["chat_id" => $chat, "user_id" => $id]);
    return $res["result"]["status"] == "administrator";
}
function years($brt)
{
    //	$tz = new DateTimeZone('Europe/Rome');
    $h = ymd($brt);
    if ($h == 0) {
        return 0;
    }
    try {
        $b = new DateTime($h);
    } catch (Exception $e) {
        return 0;
    }
    if (!$b) {
        return 0;
    }
    $age = $b->diff(new DateTime('now'))->y;

    return $age;
}
function dateStrip($d)
{
    $f = "";
    foreach (str_split($d) as $c) {
        if (in_array($c, ["1", "2", "3", "4", "5", "6", "7", "8", "9", "0", "/"])) {
            $f .= $c;
        }
    }
    return $f;
}
function makeList($a)
{
    $s = "";
    $ka =  $GLOBALS["config"]["lgbt"]["text"]["userView"];
    foreach ($a as $key => $val) {

        if (!in_array($key, array_keys($ka)) || empty($val)) continue;
        $s .= $ka[$key] . ":\n$val\n\n";
    }
    return $s;
}
function randArr($arr)
{
    return $arr[array_rand($arr)];
}
function CAHrand($white = false)
{
    $arr = json_decode(file_get_contents("cah.json"), true);
    $pick = $white ? "whiteCards" : "blackCards";
    $el = randArr($arr[$pick]);
    if ($white) {
        return $el;
    }
    return $el["text"] . "\n" . $el["pick"] . " scelte";
}

function isNo($string)
{
    return trim(strtolower($string)) == "no";
}

$people = new \Filebase\Database(['dir' => 'people']);

function rand_user($db)
{
    $total = $db->count();
    $a = random_int(0, $total);
    return $db->findAll()[$a];
}

function marry()
{

    $db = new \Filebase\Database(['dir' => 'people']);
    $li = [];
    do {
        $pai = rand_user($db);
        $pbi = rand_user($db);
    } while ($pai == $pbi);
    foreach ($GLOBALS["config"]["lgbt"]["groups"] as $g) {
        if (!$g["modulesEnabled"][".marry"]) break;
        switch ($g["modules"]["marry"]) {
            case 0:
                $li[$g["id"]] = [$pai, $pbi];
                break;
            case 1:
                do {
                    $pa = rand_user($db);
                    $pb = rand_user($db);
                } while ($pa != $pb && isInGroup($g["id"], $pa) && isInGroup($g["id"], $pb));
                $li[$g["id"]] = [$pa, $pb];
                break;
        }
    }
    return $li;
}
function relgbt()
{
    $li = [];

    $db = new \Filebase\Database(['dir' => 'people']);
    $pi = rand_user($db);
    foreach ($GLOBALS["config"]["lgbt"]["groups"] as $g) {
        switch ($g["modules"]["king"]) {
            case 0:
                $li[$g["id"]] = $pi;
                break;
            case 1:
                do {
                    $p = rand_user($db);
                } while (!isInGroup($g["id"], $p->getId()));
                $li[$g["id"]] = $p;
                break;
        }
    }

    return $li;
}
//file_put_contents("test.txt",file_get_contents("php://input"),FILE_APPEND);
$DATA = json_decode(file_get_contents("php://input"), true);
$correl =  $GLOBALS["config"]["lgbt"]["correlationMenu"];

function whatToModify($correl)
{
    $i = 1;
    $final =  $GLOBALS["config"]["lgbt"]["text"]["whatEdit"] . "\n";
    foreach ($correl as $el) {
        $final .= $i . ") " . $el[0] . "\n";
        $i++;
    }
    $final .= "\n" .  $GLOBALS["config"]["lgbt"]["text"]["numberEdit"];
    return $final;
}
function endM($DATA)
{
    API("sendMessage", ["chat_id" => $DATA["message"]["chat"]["id"], "text" =>  $GLOBALS["config"]["lgbt"]["text"]["finishText"], "reply_to_message_id" => $DATA["message"]["message_id"]]);
}
function askNameFirst($DATA, $fl = false)
{
    if (!$fl) {
        API("sendMessage", ["chat_id" => $DATA["message"]["chat"]["id"], "text" =>  $GLOBALS["config"]["lgbt"]["text"]["welcomeText"], "reply_to_message_id" => $DATA["message"]["message_id"]]);
    }
    API("sendMessage", ["chat_id" => $DATA["message"]["chat"]["id"], "text" =>  $GLOBALS["config"]["lgbt"]["text"]["nameAsk"], "reply_to_message_id" => $DATA["message"]["message_id"]]);
}
if ($DATA["message"]["from"]["is_bot"]) {
    exit;
}
$t = $DATA["message"]["text"];
$uid = $DATA["message"]["from"]["id"];


beg:
switch ($DATA["message"]["chat"]["id"]) {

    default:
        $fl = true;
        if (!moduleOn("boot", $DATA["message"]["chat"]["id"], $DATA, true)) break;
        foreach ($GLOBALS["config"]["lgbt"]["groups"] as $g) {
            if ($g["id"] != $DATA["message"]["chat"]["id"]) continue;
            if (!$g["on"]) {
                $fl = false;
                break;
            }
            if ($tk = maintenance($g["maintenance"])) {
                if (in_array(explode(" ", $t)[0], array_keys($GLOBALS["config"]["lgbt"]["modulesEnabled"]))) {
                    API("sendMessage", ["chat_id" => $DATA["message"]["chat"]["id"], "text" =>  $tk, "reply_to_message_id" => $DATA["message"]["message_id"]]);
                }
                $fl = false;
            }
        }
        if (!$fl) break;
        if ($people->has($uid) && $people->get($uid)->watch &&  $GLOBALS["config"]["lgbt"]["modulesEnabled"]["watchedForward"]) {
            API("forwardMessage", ["chat_id" => (-1001210906612), "from_chat_id" => $DATA["message"]["chat"]["id"], "message_id" => $DATA["message"]["message_id"]]);
        }
        if (strpos(strtolower($t), 'la lgb') !== false) {
            API("sendMessage", ["chat_id" => $DATA["message"]["chat"]["id"], "text" =>  $GLOBALS["config"]["lgbt"]["text"]["noLaLgbtplz"], "reply_to_message_id" => $DATA["message"]["message_id"]]);
            break;
        }
        if (array_key_exists("new_chat_participant", $DATA["message"]) && $people->has($DATA["message"]["new_chat_participant"]["id"])) {
            if (!moduleOn("welcome", $DATA["message"]["chat"]["id"], $DATA, true)) break;

            $x = $people->get($DATA["message"]["new_chat_participant"]["id"]);
            if ($x->status == "askDm") {
                API("sendMessage", ["chat_id" => $DATA["message"]["chat"]["id"], "text" =>  $GLOBALS["config"]["lgbt"]["text"]["seenButEmptyRecord"]  . makeList($x->toArray()), "reply_to_message_id" => $DATA["message"]["message_id"]]);
            } else {
                API("sendMessage", ["chat_id" => $DATA["message"]["chat"]["id"], "text" =>  $GLOBALS["config"]["lgbt"]["text"]["alreadyKnowYou"]  . makeList($x->toArray()), "reply_to_message_id" => $DATA["message"]["message_id"]]);
            }
            if (!moduleOn("log", $DATA["message"]["chat"]["id"], $DATA, true)) break;
            API("sendMessage", ["chat_id" =>  $GLOBALS["config"]["lgbt"]["logChannelId"], "text" => $DATA["message"]["chat"]["title"] . "(@" . $DATA["message"]["chat"]["username"] . ")\nMembro conosciuto entrato nel gruppo:\n" . $DATA["message"]["from"]["first_name"] . " " . $DATA["message"]["from"]["last_name"] . "\n@" . $DATA["message"]["from"]["username"] . "\nLingua: " . $DATA["message"]["from"]["language_code"] . " \n Dati precedenti: " . makeList($x->toArray())]);
            break;
        } elseif ($t == ".raBIO") {
            if (!moduleOn(".raBIO", $DATA["message"]["chat"]["id"], $DATA, true)) break;
            do {
                $total = $people->count();
                $a = random_int(0, $total);
                $p = $people->findAll()[$a];
            } while (empty($p->bio));
            API("sendMessage", ["chat_id" => $DATA["message"]["chat"]["id"], "text" => $p->bio, "reply_to_message_id" => $DATA["message"]["message_id"]]);
        } elseif ($t == ".ii" || explode(" ", $t)[0] == ".ii" || explode("\n", $t)[0] == ".ii") {
            if (!moduleOn(".ii", $DATA["message"]["chat"]["id"], $DATA, true)) break;
            $ms = explode("\n", $t, 2)[1] ?: explode(" ", $t, 2)[1];
            $ms = $ms ?: $DATA["message"]["reply_to_message"]["text"];
            if (empty($ms)) {
                API("sendMessage", ["chat_id" => $DATA["message"]["chat"]["id"], "text" => $GLOBALS["config"]["lgbt"]["text"]["mustCite"], "reply_to_message_id" => $DATA["message"]["message_id"]]);
            }
            $ms = str_replace("*s*", "ə", $ms);
            $ms = str_replace("*S*", "ə", $ms);
            $ms = str_replace("*p*", "з", $ms);
            $ms = str_replace("*P*", "з", $ms);
            API("sendMessage", ["chat_id" => $DATA["message"]["chat"]["id"], "text" => $ms, "reply_to_message_id" => $DATA["message"]["message_id"]]);
        } elseif ($t == ".watch") {
            if (!moduleOn(".watch", $DATA["message"]["chat"]["id"], $DATA, true)) break;
            if (!is_admin($DATA["message"]["from"]["id"], $DATA["message"]["chat"]["id"])) {
                break;
            }
            $u = $people->get($DATA["message"]["reply_to_message"]["from"]["id"]);
            $u->watch = true;
            $u->save();
            API("sendMessage", ["chat_id" => $DATA["message"]["chat"]["id"], "text" => $GLOBALS["config"]["lgbt"]["text"]["eye"]]);
            API("sendMessage", ["chat_id" => (-1001210906612), "text" => $u->telegramName . " ( @" . $u->username . " ) " . $GLOBALS["config"]["lgbt"]["text"]["isUnderObservation"]]);
        } elseif ($t == ".unwatchAll") {
            if (!moduleOn(".unwatchAll", $DATA["message"]["chat"]["id"], $DATA, true)) break;
            $el = "";
            if (!is_admin($DATA["message"]["from"]["id"], $DATA["message"]["chat"]["id"])) {
                break;
            }
            foreach ($people->where("watch", "=", "true")
                ->results(false) as $p) {
                $p->watch = false;
                $el .= $p->telegramName . ", ";
            }
            if (!empty($el)) {
                $el = substr($string, 0, -2);
                $m =  $GLOBALS["config"]["lgbt"]["text"]["freeBeg"] . $el;
            } else {
                $m =  $GLOBALS["config"]["lgbt"]["text"]["allFree"];
            }
            API("sendMessage", ["chat_id" => $DATA["message"]["chat"]["id"], "text" => $m]);
        } elseif ($t == ".unwatch") {

            if (!moduleOn(".unwatch", $DATA["message"]["chat"]["id"], $DATA, true)) break;

            if (!is_admin($DATA["message"]["from"]["id"], $DATA["message"]["chat"]["id"])) {
                break;
            }
            $u = $people->get($DATA["message"]["reply_to_message"]["from"]["id"]);
            $u->watch = false;
            $u->save();
            API("sendMessage", ["chat_id" => $DATA["message"]["chat"]["id"], "text" => $GLOBALS["config"]["lgbt"]["text"]["freedom"]]);
            API("sendMessage", ["chat_id" => (-1001210906612), "text" => $u->telegramName . " ( @" . $u->username . " )" . $GLOBALS["config"]["lgbt"]["text"]["notObservedAnymore"]]);
        } elseif ($t == ".listWatch") {

            if (!moduleOn(".listWatch", $DATA["message"]["chat"]["id"], $DATA, true)) break;
            if (!is_admin($DATA["message"]["from"]["id"], $DATA["message"]["chat"]["id"])) {
                break;
            }
            $us = $people->where("watch", "=", "true")
                ->results(false);
            $e = "";
            foreach ($us as $u) {
                $e .= "\n";
                $e .= makeList($u->toArray());
                $e .= "\n-------\n";
            }
            API("sendMessage", ["chat_id" => $DATA["message"]["chat"]["id"], "text" => $e]);
        } elseif ($t == ".inspire") {
            if (!moduleOn(".inspire", $DATA["message"]["chat"]["id"], $DATA, true)) break;

            $url = file_get_contents($GLOBALS["config"]["lgbt"]["APIs"]["inspire"]);
            API("sendChatAction", ["chat_id" => $DATA["message"]["chat"]["id"], "action" => "upload_photo"]);
            API("sendPhoto", ["chat_id" => $DATA["message"]["chat"]["id"], "photo" => $url, "reply_to_message_id" => $DATA["message"]["message_id"]]);
        } elseif ($t == ".tpdne") {
            if (!moduleOn(".tpdne", $DATA["message"]["chat"]["id"], $DATA, true)) break;

            $url = $GLOBALS["config"]["lgbt"]["APIs"]["tpdne"] . "?v=" . time();
            API("sendChatAction", ["chat_id" => $DATA["message"]["chat"]["id"], "action" => "upload_photo"]);
            API("sendPhoto", ["chat_id" => $DATA["message"]["chat"]["id"], "photo" => $url, "reply_to_message_id" => $DATA["message"]["message_id"]]);
        } elseif ($t == ".ai") {
            if (!moduleOn(".ai", $DATA["message"]["chat"]["id"], $DATA, true)) break;
            $items = explode("##", file_get_contents("markovdata.txt"));
            $a = $items[array_rand($items)];
            API("sendMessage", ["chat_id" => $DATA["message"]["chat"]["id"], "text" => $a]);
        } elseif ($t == ".rave" || explode(" ", $t)[0] == ".rave") {
            if (!moduleOn(".rave", $DATA["message"]["chat"]["id"], $DATA, true)) break;
            $ms = explode("\n", $t, 3)[1] ?: explode(" ", $t, 2)[1];
            $ms = $ms ?: $DATA["message"]["reply_to_message"]["text"];
            $a = explode("|", $ms);
            $a[1] = strtolower(trim($a[1]));
            if (count($a) == 2) {
                switch ($a[1]) {
                    case "classic":
                        $type = "classic";
                        break;
                    case "garfield":
                        $type = "garfield";
                        break;
                    case "megalovania":
                        $type = "megalovania";
                        break;
                    case "otamatone":
                        $type = "otamatone";
                        break;
                    default:
                        $type = "classic";
                        break;
                }
                $ms = trim($a[0]);
            }
            API("sendChatAction", ["chat_id" => $DATA["message"]["chat"]["id"], "action" => "upload_video"]);
            API("sendVideo", ["chat_id" => $DATA["message"]["chat"]["id"], "video" => "https://crabrave.boringcactus.com/render?text=" . urlencode($ms) . "&ext=mp4&style=" . urlencode($type), "reply_to_message_id" => $DATA["message"]["message_id"]]);
        } elseif ($t == ".reking") {
            if (!moduleOn(".reking", $DATA["message"]["chat"]["id"], $DATA, true)) break;
            if (!is_admin($DATA["message"]["from"]["id"], $DATA["message"]["chat"]["id"])) {
                break;
            }

            $rs = relgbt();
            foreach ($rs as $chid => $u) {
                API("sendMessage", ["chat_id" => $chid, "text" => $GLOBALS["config"]["lgbt"]["text"]["reroll"] . $u->name . "\n(" . $u->telegramName . ",@" . $u->username . ") "]);
                file_put_contents("kingDate", date("dmY"));
            }
        } elseif ($t == ".s") {

            if (!moduleOn(".s", $DATA["message"]["chat"]["id"], $DATA, true)) break;
            $es = "";
            if (!file_exists("DC.txt") || filesize("DC.txt") <= 0) {
                $es .= $GLOBALS["config"]["lgbt"]["text"]["status"]["missingJueBox"];
            }
            if (!file_exists("bibbia.txt") || filesize("bibbia.txt") <= 0) {
                $es .= $GLOBALS["config"]["lgbt"]["text"]["status"]["missingBibleFile"];
            }
            if (!file_exists("pokelist") || filesize("pokelist") <= 0) {
                $es .= $GLOBALS["config"]["lgbt"]["text"]["status"]["missingPokemonFile"];
            }
            if (!file_exists("aalist") || filesize("aalist") <= 0) {
                $es .= $GLOBALS["config"]["lgbt"]["text"]["status"]["missingAAFile"];
            }
            if (!file_exists("mytable.php") || filesize("mytable.php") <= 0) {
                $es .= $GLOBALS["config"]["lgbt"]["text"]["status"]["missingMyTable"];
            }
            if (!is_dir("ignorance")) {
                $es .= $GLOBALS["config"]["lgbt"]["text"]["status"]["missingNoLgbt"];
            }
            if (!empty($es)) {
                $es = $GLOBALS["config"]["lgbt"]["text"]["status"]["someErrors"] . $es;
            }
            $m = date("d/m/Y H:i:s") . $GLOBALS["config"]["lgbt"]["text"]["status"]["up"] . $es;
            API("sendMessage", ["chat_id" => $DATA["message"]["chat"]["id"], "text" => $m, "reply_to_message_id" => $DATA["message"]["message_id"]]);
        } elseif ($t == ".cry") {
            if (!moduleOn(".cry", $DATA["message"]["chat"]["id"], $DATA, true)) break;
            $m = $GLOBALS["config"]["lgbt"]["text"]["cry"];
            API("sendMessage", ["chat_id" => $DATA["message"]["chat"]["id"], "text" => $m, "reply_to_message_id" => $DATA["message"]["message_id"]]);
            break;
        } elseif ($t == ".i") {
            if (!moduleOn(".i", $DATA["message"]["chat"]["id"], $DATA, true)) break;
            $m =  $GLOBALS["config"]["lgbt"]["text"]["info"];
            API("sendMessage", ["chat_id" => $DATA["message"]["chat"]["id"], "text" => $m, "reply_to_message_id" => $DATA["message"]["message_id"]]);
            break;
        } elseif ($t == ".pvt") {
            if (!moduleOn(".pvt", $DATA["message"]["chat"]["id"], $DATA, true)) break;
            API("sendMessage", ["chat_id" => $DATA["message"]["chat"]["id"], "text" => $GLOBALS["config"]["lgbt"]["text"]["pvt"], "reply_to_message_id" => $DATA["message"]["message_id"]]);
            break;
        }
        if ($t == ".debugUser") {

            API("sendMessage", ["chat_id" => $DATA["message"]["chat"]["id"], "text" => json_encode($people->get($DATA["message"]["reply_to_message"]["from"]["id"])->toArray()), "reply_to_message_id" => $DATA["message"]["message_id"]]);
            break;
        } elseif ($t == ".nolgbt") {

            if (!moduleOn(".nolgbt", $DATA["message"]["chat"]["id"], $DATA, true)) break;
            API("sendMessage", ["chat_id" => $DATA["message"]["chat"]["id"], "text" => $GLOBALS["config"]["lgbt"]["text"]["nolgbt"], "reply_to_message_id" => $DATA["message"]["message_id"]]);
        } elseif ($t == ".lgbts") {

            if (!moduleOn(".lgbts", $DATA["message"]["chat"]["id"], $DATA, true)) break;
            foreach ($GLOBALS["config"]["lgbt"]["imagesLgbts"] as $url) {
                API("sendChatAction", ["chat_id" => $DATA["message"]["chat"]["id"], "action" => "upload_photo"]);
                API("sendPhoto", ["chat_id" => $DATA["message"]["chat"]["id"], "photo" => $url, "reply_to_message_id" => $DATA["message"]["message_id"]]);
            }
        } elseif ($t == ".eva" || trim(explode("\n", $t)[0]) == ".eva") {
            if (!moduleOn(".eva", $DATA["message"]["chat"]["id"], $DATA, true)) break;
            $l = explode("\n", $t);
            if (count($l) == 1 || $l[1] == "n") {
                $list = [

                    $GLOBALS["config"]["lgbt"]["baseurlpath"] . "evas/frame_00_delay-0.8s.gif",
                    $GLOBALS["config"]["lgbt"]["baseurlpath"] . "evas/frame_01_delay-0.8s.gif",
                    $GLOBALS["config"]["lgbt"]["baseurlpath"] . "evas/frame_02_delay-0.8s.gif",
                    $GLOBALS["config"]["lgbt"]["baseurlpath"] . "evas/frame_03_delay-0.8s.gif",
                    $GLOBALS["config"]["lgbt"]["baseurlpath"] . "evas/frame_04_delay-0.8s.gif",
                    $GLOBALS["config"]["lgbt"]["baseurlpath"] . "evas/frame_05_delay-0.8s.gif",
                    $GLOBALS["config"]["lgbt"]["baseurlpath"] . "evas/frame_06_delay-0.8s.gif",
                    $GLOBALS["config"]["lgbt"]["baseurlpath"] . "evas/frame_07_delay-0.8s.gif",
                    $GLOBALS["config"]["lgbt"]["baseurlpath"] . "evas/frame_08_delay-0.8s.gif",
                    $GLOBALS["config"]["lgbt"]["baseurlpath"] . "evas/frame_09_delay-0.8s.gif",
                    $GLOBALS["config"]["lgbt"]["baseurlpath"] . "evas/frame_10_delay-0.8s.gif",
                    $GLOBALS["config"]["lgbt"]["baseurlpath"] . "evas/frame_11_delay-0.8s.gif",
                    $GLOBALS["config"]["lgbt"]["baseurlpath"] . "evas/frame_12_delay-0.8s.gif",
                    $GLOBALS["config"]["lgbt"]["baseurlpath"] . "evas/frame_13_delay-0.8s.gif",
                    $GLOBALS["config"]["lgbt"]["baseurlpath"] . "evas/frame_14_delay-0.8s.gif",
                    $GLOBALS["config"]["lgbt"]["baseurlpath"] . "evas/frame_15_delay-0.8s.gif",
                    $GLOBALS["config"]["lgbt"]["baseurlpath"] . "evas/frame_16_delay-0.8s.gif",
                    $GLOBALS["config"]["lgbt"]["baseurlpath"] . "evas/frame_17_delay-0.8s.gif",
                    $GLOBALS["config"]["lgbt"]["baseurlpath"] . "evas/frame_18_delay-0.8s.gif",
                    $GLOBALS["config"]["lgbt"]["baseurlpath"] . "evas/frame_19_delay-0.8s.gif",
                    $GLOBALS["config"]["lgbt"]["baseurlpath"] . "evas/frame_20_delay-0.8s.gif",
                    $GLOBALS["config"]["lgbt"]["baseurlpath"] . "evas/frame_21_delay-0.8s.gif",
                    $GLOBALS["config"]["lgbt"]["baseurlpath"] . "evas/frame_22_delay-0.8s.gif",
                    $GLOBALS["config"]["lgbt"]["baseurlpath"] . "evas/frame_23_delay-0.8s.gif",
                    $GLOBALS["config"]["lgbt"]["baseurlpath"] . "evas/frame_24_delay-0.8s.gif",
                    $GLOBALS["config"]["lgbt"]["baseurlpath"] . "evas/frame_25_delay-1s.gif"

                ];
                if ($l[1] == "n") {
                    $url = $list[$l[2] - 1] ?: $list[array_rand($list)];
                } else {
                    $url = $list[array_rand($list)];
                }
                API("sendChatAction", ["chat_id" => $DATA["message"]["chat"]["id"], "action" => "upload_photo"]);
                API("sendPhoto", ["chat_id" => $DATA["message"]["chat"]["id"], "photo" => $url, "reply_to_message_id" => $DATA["message"]["message_id"]]);
                break;
            }
            $list = [];
            for ($i = 1; $i < count($l); $i++) {
                if ($i > 4) $list[4] .= "\n" . trim($l[$i]);
                $list[] = trim($l[$i]);
            }
            API("sendChatAction", ["chat_id" => $DATA["message"]["chat"]["id"], "action" => "upload_photo"]);
            API("sendPhoto", ["chat_id" => $DATA["message"]["chat"]["id"], "photo" => "https://api.apiflash.com/v1/urltoimage?access_key=9f4b8514390e45c3aff7350c635587a8&url=http%3A%2F%2Fwebport.altervista.org%2Fbots%2Ftelegram%2Flgbt%2Feva.php%3Fdata%3D" . urlencode(json_encode($list)) . "&height=478&width=600", "reply_to_message_id" => $DATA["message"]["message_id"]]);
        } elseif ($t == ".joke") {

            if (!moduleOn(".joke", $DATA["message"]["chat"]["id"], $DATA, true)) break;
            $do = json_decode(file_get_contents($GLOBALS["config"]["lgbt"]["APIs"]["jokes"]), true);
            if ($do["type"] == "twopart") {
                $joke = $do["setup"] . "\n" . $do["delivery"];
            } else {
                $joke = $do["joke"];
            }
            $flags = [];
            foreach ($do["flags"] as $key => $bool) {
                if ($bool) {
                    $flags[] = $key;
                }
            }
            $joke .= "\nCATEGORY:\n" . $do["category"];
            if (count($flags)) {
                $joke .= "\nTAGS:\n" . implode(",", $flags);
            }
            API("sendMessage", ["chat_id" => $DATA["message"]["chat"]["id"], "text" => $joke, "reply_to_message_id" => $DATA["message"]["message_id"]]);
        } elseif ($t == ".debugRaw") {
            API("sendMessage", ["chat_id" => $DATA["message"]["chat"]["id"], "text" => file_get_contents("php://input"), "reply_to_message_id" => $DATA["message"]["message_id"]]);
            break;
        } elseif ($t == ".git") {
            API("sendMessage", ["chat_id" => $DATA["message"]["chat"]["id"], "text" => "https://github.com/MatMasIt/rainbowBot", "reply_to_message_id" => $DATA["message"]["message_id"]]);
            break;
        } elseif ($t == ".king") {
            if (true) break; //module broken

            if (!moduleOn(".king", $DATA["message"]["chat"]["id"], $DATA, true)) break;
            $u = relgbt();
            API("sendMessage", ["chat_id" => $DATA["message"]["chat"]["id"], "text" => $GLOBALS["config"]["lgbt"]["text"]["todaysKing"] . $u->name . "\n(" . $u->telegramName . ",@" . $u->username . ") ", "reply_to_message_id" => $DATA["message"]["message_id"]]);
        } elseif ($t == ".kq") {

            if (!moduleOn(".kq", $DATA["message"]["chat"]["id"], $DATA, true)) break;
            $q = json_decode(file_get_contents($GLOBALS["config"]["lgbt"]["APIs"]["kanye"]), true)["quote"];
            API("sendMessage", ["chat_id" => $DATA["message"]["chat"]["id"], "text" => $GLOBALS["config"]["lgbt"]["text"]["kanyeSaid"] . $q, "reply_to_message_id" => $DATA["message"]["message_id"]]);
        } elseif ($t == ".lgbtShuffle") {
            if (!moduleOn(".lgbtShuffle", $DATA["message"]["chat"]["id"], $DATA, true)) break;
            API("sendMessage", ["chat_id" => $DATA["message"]["chat"]["id"], "text" => $GLOBALS["config"]["lgbt"]["text"]["lgbtBegin"] . str_shuffle("LGBTQIA"), "reply_to_message_id" => $DATA["message"]["message_id"]]);
        } elseif (explode(" ", $t)[0] == ".CAH") {
            if (!moduleOn(".CAH", $DATA["message"]["chat"]["id"], $DATA, true)) break;
            switch (trim(strtolower(explode(" ", $t)[1]))) {
                case "w":
                case "white":
                    API("sendMessage", ["chat_id" => $DATA["message"]["chat"]["id"], "text" => $GLOBALS["config"]["lgbt"]["text"]["cardsAgainstHumanity"] . CAHrand(true), "reply_to_message_id" => $DATA["message"]["message_id"]]);
                    break;
                default:
                    API("sendMessage", ["chat_id" => $DATA["message"]["chat"]["id"], "text" => $GLOBALS["config"]["lgbt"]["text"]["cardsAgainstHumanity"] . CAHrand(), "reply_to_message_id" => $DATA["message"]["message_id"]]);
                    break;
            }
        } elseif (explode(" ", $t)[0] == ".log") {
            if (!moduleOn(".log", $DATA["message"]["chat"]["id"], $DATA, true)) break;
            if ($DATA["message"]["from"]["id"] != $GLOBALS["config"]["lgbt"]["devId"]) {
                break;
            }
            $chid =  $GLOBALS["config"]["lgbt"]["logChannelId"];
            if (!moduleOn("log", $DATA["message"]["chat"]["id"], $DATA, true)) {
                API("sendMessage", ["chat_id" => $chid, "text" => explode(" ", $t)[1]]);
            }
            API("sendMessage", ["chat_id" => $DATA["message"]["chat"]["id"], "text" => $GLOBALS["config"]["lgbt"]["text"]["messageSent"], "reply_to_message_id" => $DATA["message"]["message_id"]]);
        } elseif ($t == ".ud" || explode(" ", $t)[0] == ".ud") {

            if (!moduleOn(".ud", $DATA["message"]["chat"]["id"], $DATA, true)) break;
            $li = explode(" ", $t, 2);
            $li[1] = $li[1] ?: $DATA["message"]["reply_to_message"]["text"];
            if (empty($li[1])) {
                API("sendMessage", ["chat_id" => $DATA["message"]["chat"]["id"], "text" => $GLOBALS["config"]["lgbt"]["text"]["udHint"], "reply_to_message_id" => $DATA["message"]["message_id"]]);
            } else {
                $url = $GLOBALS["config"]["lgbt"]["APIs"]["ud"] . urlencode($li[1]);
                $plp = file_get_contents($url);
                $plp = substr($plp, 0, strrpos($plp, '}')) . "}";

                $dt = json_decode($plp, true);
                if (!count($dt["list"])) {
                    API("sendMessage", ["chat_id" => $DATA["message"]["chat"]["id"], "text" => $GLOBALS["config"]["lgbt"]["text"]["notFound"], "reply_to_message_id" => $DATA["message"]["message_id"]]);
                } else {
                    API("sendMessage", ["chat_id" => $DATA["message"]["chat"]["id"], "text" => $dt["list"][0]["definition"], "reply_to_message_id" => $DATA["message"]["message_id"]]);
                }
            }
        } elseif (explode("\n", $t)[0] == ".emu") {

            if (!moduleOn(".emu", $DATA["message"]["chat"]["id"], $DATA, true)) break;
            $ex = explode("\n", $t);
            unset($ex[0]);
            array_unshift($ex, $DATA["message"]["chat"]["id"]);
            array_unshift($ex, time());
            $url = "https://mascmt.ddns.net/gb/index.php?auth=passwordLOL&do=" . urlencode(implode("|", $ex));
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($ch, CURLOPT_HEADER, false);
            curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_REFERER, $url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            $result = curl_exec($ch);
            curl_close($ch);
            //	API("sendMessage", ["chat_id" => $DATA["message"]["chat"]["id"], "text" => "Sent to emulator,".urlencode(implode("|",$ex))." ".file_get_contents($url)." ".$url,"reply_to_message_id"=>$DATA["message"]["message_id"]]);

        } elseif ($t == ".ez" || explode(" ", $t)[0] == ".ez") {
            if (!moduleOn(".ez", $DATA["message"]["chat"]["id"], $DATA, true)) break;
            $arr
                /* it, en*/ = explode("#", file_get_contents("ezechiele.txt"));
            if (explode(" ", $t)[1] == "en") {
                $tt = $arr[1];
            } else {
                $tt = $arr[0];
            }
            API("sendMessage", ["chat_id" => $DATA["message"]["chat"]["id"], "text" => $tt, "reply_to_message_id" => $DATA["message"]["message_id"]]);
        } elseif ($t == ".oracolo" || explode(" ", $t)[0] == ".oracolo") {
            if (!moduleOn(".oracolo", $DATA["message"]["chat"]["id"], $DATA, true)) break;
            function stringaOracolo($n)
            {
                $l = file("parolit.txt");
                $f = "";
                for ($i = 0; $i < $n; $i++) {
                    $f .= $l[array_rand($l)] . " ";
                }
                return $f;
            }
            //echo stringaOracolo(10);
            $li = explode(" ", $t);
            $li[1] = $li[1] ?: $DATA["message"]["reply_to_message"]["text"];
            $li[1] = preg_replace("/[^0-9]/", "", $li[1]);
            if (empty($li[1])) {
                API("sendMessage", ["chat_id" => $DATA["message"]["chat"]["id"], "text" => $GLOBALS["config"]["lgbt"]["text"]["whichScp"], "reply_to_message_id" => $DATA["message"]["message_id"]]);
            } else {
                $res = stringaOracolo($li[1]);
                API("sendMessage", ["chat_id" => $DATA["message"]["chat"]["id"], "text" => $res, "reply_to_message_id" => $DATA["message"]["message_id"]]);
            }
        } elseif ($t == ".scp" || explode(" ", $t)[0] == ".scp") {
            if (!moduleOn(".scp", $DATA["message"]["chat"]["id"], $DATA, true)) break;
            $li = explode(" ", $t);
            $li[1] = $li[1] ?: $DATA["message"]["reply_to_message"]["text"];
            $li[1] = preg_replace("/[^0-9]/", "", $li[1]);
            if (empty($li[1])) {
                API("sendMessage", ["chat_id" => $DATA["message"]["chat"]["id"], "text" => $GLOBALS["config"]["lgbt"]["text"]["whichScp"], "reply_to_message_id" => $DATA["message"]["message_id"]]);
            } else {
                $url = $GLOBALS["config"]["lgbt"]["APIs"]["scp"] . $li[1];
                if (!is_404($url)) {
                    API("sendMessage", ["chat_id" => $DATA["message"]["chat"]["id"], "text" => $url, "reply_to_message_id" => $DATA["message"]["message_id"]]);
                } else {
                    API("sendMessage", ["chat_id" => $DATA["message"]["chat"]["id"], "text" => $GLOBALS["config"]["lgbt"]["text"]["scpNotFound"], "reply_to_message_id" => $DATA["message"]["message_id"]]);
                }
            }
        } elseif ($t == ".lookup" || explode(" ", $t)[0] == ".lookup") {
            if (!moduleOn(".lookup", $DATA["message"]["chat"]["id"], $DATA, true)) break;
            $args = explode(" ", $t);
            $rpid = $DATA["message"]["reply_to_message"]["from"]["id"] ?: $uid;

            if (!$people->has($rpid)) {
                API("sendMessage", ["chat_id" => $DATA["message"]["chat"]["id"], "text" => $GLOBALS["config"]["lgbt"]["text"]["notInDbNoUsr"], "reply_to_message_id" => $DATA["message"]["message_id"]]);
            } else {
                //file_put_contents("test","yes");
                $x = $people->get($rpid);
                $x->yearsOld = years($u->birthDate);
                $x->save();

                if (count($args) == 2) {
                    if (in_array($args[1], array_keys($x->toArray()))) {
                        $mt = makeList([$args[1] => $x->toArray()[$args[1]]]);
                        API("sendMessage", ["chat_id" => $DATA["message"]["chat"]["id"], "text" => $mt ?: $GLOBALS["config"]["lgbt"]["text"]["noResults"], "reply_to_message_id" => $DATA["message"]["message_id"]]);
                    } else {
                        API("sendMessage", ["chat_id" => $DATA["message"]["chat"]["id"], "text" => $GLOBALS["config"]["lgbt"]["text"]["parameterNotFound"], "reply_to_message_id" => $DATA["message"]["message_id"]]);
                    }
                } else {
                    $mt = makeList($x->toArray());
                    API("sendMessage", ["chat_id" => $DATA["message"]["chat"]["id"], "text" => $mt ?: $GLOBALS["config"]["lgbt"]["text"]["noResults"], "reply_to_message_id" => $DATA["message"]["message_id"]]);
                }
            }
            break;
        } elseif ($t == ".card" || explode(" ", $t)[0] == ".card") {
            if (!moduleOn(".card", $DATA["message"]["chat"]["id"], $DATA, true)) break;

            $rpid = $DATA["message"]["reply_to_message"]["from"]["id"];
            if ($rpid <= 0) {
                #https://webport.altervista.org/bots/telegram/lgbt/fetchCard.php?pass=737373737361113273&id=".$uid
                $u = $people->get($uid);
                $u->yearsOld = years($u->birthDate);
                $u->save();
                photo_id_by_user($u, $DATA["message"]["chat"]["id"]);
                API("sendChatAction", ["chat_id" => $DATA["message"]["chat"]["id"], "action" => "upload_photo"]);
                API("sendPhoto", ["chat_id" => $DATA["message"]["chat"]["id"], "photo" => "https://webport.altervista.org/bots/telegram/lgbt/gdImg/archive/" . $uid . ".fi.jpeg?int=" . random_int(0, 10000), "reply_to_message_id" => $DATA["message"]["message_id"]]);
            } elseif (!$people->has($rpid)) {
                API("sendMessage", ["chat_id" => $DATA["message"]["chat"]["id"], "text" => $GLOBALS["config"]["lgbt"]["text"]["notInDb"], "reply_to_message_id" => $DATA["message"]["message_id"]]);
            } else {
                $e = $people->get($rpid);
                photo_id_by_user($e, $DATA["message"]["chat"]["id"]);
                API("sendChatAction", ["chat_id" => $DATA["message"]["chat"]["id"], "action" => "upload_photo"]);
                API("sendPhoto", ["chat_id" => $DATA["message"]["chat"]["id"], "photo" => "https://webport.altervista.org/bots/telegram/lgbt/fetchCard.php?pass=737373737361113273&id=" . $e->getId() . "&int=" . random_int(0, 10000), "reply_to_message_id" => $DATA["message"]["message_id"]]);
                //file_put_contents("test","yes");

            }
        } elseif ($t == ".DCJuebox" || explode(" ", $t)[0] == ".DCJuebox") {

            if (!moduleOn(".DCJuebox", $DATA["message"]["chat"]["id"], $DATA, true)) break;
            $a = explode(" ", $t);
            $list = file("DC.txt", FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
            if (count($a) == 2) {
                $i = $a[1];
            } else {
                do {
                    $i = array_rand($list);
                } while (empty(trim($list[$i])));
            }
            API("sendMessage", ["chat_id" => $DATA["message"]["chat"]["id"], "text" => trim($list[$i]), "reply_to_message_id" => $DATA["message"]["message_id"]]);
        } elseif ($t == ".NLG" || explode(" ", $t)[0] == ".NLG") {

            if (!moduleOn(".NLG", $DATA["message"]["chat"]["id"], $DATA, true)) break;
            $a = explode(" ", $t);
            $list = explode("\n#\n", file_get_contents("nolgbt.txt"));
            if (count($a) == 2) {
                $i = $a[1];
            } else {
                do {
                    $i = array_rand($list);
                } while (empty(trim($list[$i])));
            }
            API("sendMessage", ["chat_id" => $DATA["message"]["chat"]["id"], "text" => trim($list[$i]), "reply_to_message_id" => $DATA["message"]["message_id"]]);
        } elseif ($t == ".nr" || explode(" ", $t)[0] == ".nr") {

            if (!moduleOn(".nr", $DATA["message"]["chat"]["id"], $DATA, true)) break;
            $a = explode(" ", $t);
            $list = explode("\n##\n", file_get_contents("nazis"));
            if (count($a) == 2) {
                $i = $a[1];
            } else {
                do {
                    $i = array_rand($list);
                } while (empty(trim($list[$i])));
            }
            API("sendMessage", ["chat_id" => $DATA["message"]["chat"]["id"], "text" => "The Nazi oracle says:\n" . trim($list[$i]), "reply_to_message_id" => $DATA["message"]["message_id"]]);
        } elseif ($t == ".bibbiaJuebox" || explode(" ", $t)[0] == ".bibbiaJuebox") {
            if (!moduleOn(".bibbiaJuebox", $DATA["message"]["chat"]["id"], $DATA, true)) break;
            $a = explode(" ", $t);
            $list = file("bibbia.txt", FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
            if (count($a) == 2) {
                $i = $a[1];
            } else {
                do {
                    $i = array_rand($list);
                } while (empty(trim($list[$i])));
            }
            API("sendMessage", ["chat_id" => $DATA["message"]["chat"]["id"], "text" => trim($list[$i]), "reply_to_message_id" => $DATA["message"]["message_id"]]);
        } elseif ($t == ".helpb") {

            if (!moduleOn(".helpb", $DATA["message"]["chat"]["id"], $DATA, true)) break;
            API("sendMessage", ["chat_id" => $DATA["message"]["chat"]["id"], "text" => $GLOBALS["config"]["lgbt"]["APIs"]["help"], "reply_to_message_id" => $DATA["message"]["message_id"]]);
        } elseif ($t == ".tutti") {

            if (!moduleOn(".tutti", $DATA["message"]["chat"]["id"], $DATA, true)) break;
            API("sendMessage", ["chat_id" => $DATA["message"]["chat"]["id"], "text" => $GLOBALS["config"]["lgbt"]["APIs"]["myTable"], "reply_to_message_id" => $DATA["message"]["message_id"]]);
        } elseif ($t == ".eq") {

            if (!moduleOn(".eq", $DATA["message"]["chat"]["id"], $DATA, true)) break;
            $d = json_decode(file_get_contents("eq.json"), true);
            $e = $d[random_int(0, count($d))];
            API("sendMessage", ["chat_id" => $DATA["message"]["chat"]["id"], "text" => $e, "reply_to_message_id" => $DATA["message"]["message_id"]]);
        } elseif ($t == ".evc" || explode(" ", $t)[0] == ".evc") {

            if (!moduleOn(".evc", $DATA["message"]["chat"]["id"], $DATA, true)) break;
            $n = explode(" ", $t)[1];
            $d = json_decode(file_get_contents("poll.json"), true);
            if (!empty($n) && $n < (count($d) + 1) && $n > (-1) && floor($n) == $n) {
                $in = $n - 1;
            } else {
                $in = random_int(0, count($d));
            }
            $e = $d[$in];
            API("sendMessage", ["chat_id" => $DATA["message"]["chat"]["id"], "text" => "Il " . $e["datetimeUsed"] . $GLOBALS["config"]["lgbt"]["text"]["wiiText"] . ($e["type"] == "worldwide" ? "mondiale " : "") . "(" . ($in + 1) . "/" . count($d) . ") :", "reply_to_message_id" => $DATA["message"]["message_id"]]);
            API("sendPoll", ["chat_id" => $DATA["message"]["chat"]["id"], "question" => $e["question"], "options" => json_encode([$e["options"][0]["name"], $e["options"][1]["name"]]), "reply_to_message_id" => $DATA["message"]["message_id"], "is_anonymous" => false]);
            API("sendMessage", ["chat_id" => $DATA["message"]["chat"]["id"], "text" => $GLOBALS["config"]["lgbt"]["text"]["resultsWere"] . $e["options"][0]["name"] . $e["options"][0]["used"] . "\n" . $e["options"][1]["name"] . $e["options"][1]["used"], "reply_to_message_id" => $DATA["message"]["message_id"]]);
        } elseif ($t == ".RWP") {

            if (!moduleOn(".RWP", $DATA["message"]["chat"]["id"], $DATA, true)) break;
            $ch  =  curl_init();
            curl_setopt($ch,  CURLOPT_URL, $GLOBALS["config"]["lgbt"]["APIs"]["wikiRandom"]);
            curl_setopt($ch,  CURLOPT_RETURNTRANSFER,  1);
            $output  =  curl_exec($ch);
            curl_close($ch);
            API("sendMessage", [
                "chat_id" => $DATA["message"]["chat"]["id"], "text" => /*json_decode(*/
                $output /*,true)["content_urls"]["desktop"]["page"]?:"77"*/, "reply_to_message_id" => $DATA["message"]["message_id"]
            ]);
        } elseif ($t == ".n" || explode(" ", $t)[0] == ".n") {

            if (!moduleOn(".n", $DATA["message"]["chat"]["id"], $DATA, true)) break;
            $a = explode(" ", $t);
            if (count($a) == 2) {
                API("sendMessage", ["chat_id" => $DATA["message"]["chat"]["id"], "text" => file_get_contents($GLOBALS["config"]["lgbt"]["APIs"]["numbers"] . $a[1]), "reply_to_message_id" => $DATA["message"]["message_id"]]);
            } else {
                API("sendMessage", ["chat_id" => $DATA["message"]["chat"]["id"], "text" => file_get_contents($GLOBALS["config"]["lgbt"]["APIs"]["numbers"] . $DATA["message"]["reply_to_message"]["text"]), "reply_to_message_id" => $DATA["message"]["message_id"]]);
            }
        } elseif ($t == ".imgCacheClean") {

            if (!moduleOn(".imgCacheClean", $DATA["message"]["chat"]["id"], $DATA, true)) break;
            if (!is_admin($DATA["message"]["from"]["id"], $DATA["message"]["chat"]["id"])) {
                break;
            }
            foreach (glob("gdImg/archive/*") as $f) {
                unlink($f);
            }
            API("sendMessage", ["chat_id" => $DATA["message"]["chat"]["id"], "text" => $GLOBALS["config"]["lgbt"]["text"]["yesMaster"], "reply_to_message_id" => $DATA["message"]["message_id"]]);
        } elseif ($t == ".soffriggi") {
            if (!moduleOn(".soffriggi", $DATA["message"]["chat"]["id"], $DATA, true)) break;
            if (!is_admin($DATA["message"]["from"]["id"], $DATA["message"]["chat"]["id"])) {
                break;
            }

            $l = "";
            foreach ($people->where("status", "=", "askDm")
                ->results(false) as $pa) {
                $l .= $pa->name . "(" . $pa->telegramName . ",@" . $pa->username . ")\n";
            }
            $l .= $GLOBALS["config"]["lgbt"]["text"]["fry"];

            API("sendMessage", ["chat_id" => $DATA["message"]["chat"]["id"], "text" => $l, "reply_to_message_id" => $DATA["message"]["message_id"]]);
        } elseif ($t == ".prg") {

            if (!moduleOn(".prg", $DATA["message"]["chat"]["id"], $DATA, true)) break;
            if (!is_admin($DATA["message"]["from"]["id"], $DATA["message"]["chat"]["id"])) {
                break;
            }
            API("sendMessage", ["chat_id" => $DATA["message"]["chat"]["id"], "text" => $GLOBALS["config"]["lgbt"]["text"]["purgeBegin"]]);

            $grs = [];
            foreach ($GLOBALS["config"]["lgbt"]["groups"] as $g) {
                $grs[] = $g["id"];
            }
            $ir = purge($people, $grs);
            if (moduleOn("log", $DATA["message"]["chat"]["id"], $DATA, true)) {
                API("sendMessage", ["chat_id" => $GLOBALS["config"]["lgbt"]["logChannelId"], "text" => "$ir " . $GLOBALS["config"]["lgbt"]["text"]["purged"]]);
            }
        } elseif ($t == ".no") {
            if (!moduleOn(".no", $DATA["message"]["chat"]["id"], $DATA, true)) break;
            API("sendMessage", ["chat_id" => $DATA["message"]["chat"]["id"], "text" =>  $GLOBALS["config"]["lgbt"]["text"]["wrongOpinion"], "reply_to_message_id" => $DATA["message"]["message_id"]]);
        } elseif ($t == ".forum") {

            if (!moduleOn(".forum", $DATA["message"]["chat"]["id"], $DATA, true)) break;

            API("sendMessage", ["chat_id" => $DATA["message"]["chat"]["id"], "text" =>  $GLOBALS["config"]["lgbt"]["text"]["cryPlaylist"], "reply_to_message_id" => $DATA["message"]["message_id"]]);
        } elseif ($t == ".anon") {

            if (!moduleOn(".anon", $DATA["message"]["chat"]["id"], $DATA, true)) break;
            $hm = [];
            foreach ($people->findAll() as $p) {
                foreach ($p->toArray() as $k => $v) {
                    $hm[$k][] = $v;
                }
            }

            foreach ($hm as $k => $vl) {
                $hm[$k] = $vl[random_int(0, count($vl))];
            }

            API("sendMessage", ["chat_id" => $DATA["message"]["chat"]["id"], "text" => makeList($hm), "reply_to_message_id" => $DATA["message"]["message_id"]]);
        } elseif ($t == ".marry") {
            if (!moduleOn(".marry", $DATA["message"]["chat"]["id"], $DATA, true)) break;
            $flag = true;
            foreach ($GLOBALS["config"]["lgbt"]["groups"] as $g) {
                if ($g["id"] == $DATA["message"]["chat"]["id"] && $g["modules"]["marry"] == 2) {
                    $flag = false;
                    break;
                }
            }
            if (!$flag) break;
            $k = marry()[$DATA["message"]["chat"]["id"]];
            $pa = $k[0];
            $pb = $k[1];
            $rpid = $DATA["message"]["reply_to_message"]["from"]["id"];
            if (!empty($rpid)) {
                $pa = $people->get($rpid);
            }
            API("sendMessage", ["chat_id" => $DATA["message"]["chat"]["id"], "text" => $GLOBALS["config"]["lgbt"]["text"]["theMarriage"] . $pa->name . "(" . $pa->telegramName . ",@" . $pa->username . ") e " . $pb->name . "(" . $pb->telegramName . ",@" . $pb->username . $GLOBALS["config"]["lgbt"]["text"]["isSet"], "reply_to_message_id" => $DATA["message"]["message_id"]]);
        } elseif ($t == ".stats") {
            if (!moduleOn(".stats", $DATA["message"]["chat"]["id"], $DATA, true)) break;
            $o = orientDerivate($people);
            include("chart.php");
            $fl = [];

            $s =  $GLOBALS["config"]["lgbt"]["text"]["statsDisclaimer"];
            $tot = 0;
            foreach ($o as $k => $v) {
                $tot += $v;
            }
            foreach ($o as $k => $v) {
                $fl[$k] = round($v / $tot * 100, 2);
                $s .= " $k : $v ( " . round($v / $tot * 100, 2) . "%)\n";
            }
            orientChart($fl, "gdImg/chart.png");
            API("sendChatAction", ["chat_id" => $DATA["message"]["chat"]["id"], "action" => "upload_photo"]);
            API("sendPhoto", ["chat_id" => $DATA["message"]["chat"]["id"], "photo" => "https://webport.altervista.org/bots/telegram/lgbt/gdImg/chart.png?a=" . time(), "reply_to_message_id" => $DATA["message"]["message_id"]]);

            API("sendMessage", ["chat_id" => $DATA["message"]["chat"]["id"], "text" => $s, "reply_to_message_id" => $DATA["message"]["message_id"]]);
        } elseif ($t == ".usage") {
            $count = API("getChatMembersCount", ["chat_id" => $DATA["message"]["chat"]["id"]])["result"];
            API("sendMessage", ["chat_id" => $DATA["message"]["chat"]["id"], "text" => "Membri gruppo: " . $count . "\nRegistrati sul bot: " . $people->count() . " (" . floor(($people->count() / $count) * 100) . " %)", "reply_to_message_id" => $DATA["message"]["message_id"]]);
        } elseif ($t == ".pokeJuebox" || explode(" ", $t)[0] == ".pokeJuebox") {
            if (!moduleOn(".pokeJuebox", $DATA["message"]["chat"]["id"], $DATA, true)) break;
            $a = explode(" ", $t);
            $list = file("pokelist");
            if (count($a) == 2) {
                $i = $a[1];
            } else {
                $i = array_rand($list);
            }
            API("sendMessage", ["chat_id" => $DATA["message"]["chat"]["id"], "text" => $list[$i], "reply_to_message_id" => $DATA["message"]["message_id"]]);
        } elseif ($t == ".AAJuebox" || explode(" ", $t)[0] == ".AAJuebox") {
            if (!moduleOn(".AAJuebox", $DATA["message"]["chat"]["id"], $DATA, true)) break;
            $a = explode(" ", $t);
            $list = file("aalist");
            if (count($a) == 2) {
                $i = $a[1];
            } else {
                $i = array_rand($list);
            }
            API("sendMessage", ["chat_id" => $DATA["message"]["chat"]["id"], "text" => $list[$i], "reply_to_message_id" => $DATA["message"]["message_id"]]);
        }
        if (!$people->has($uid)) {
            if (moduleOn("welcome", $DATA["message"]["chat"]["id"], $DATA, true)) {
                API("sendMessage", ["chat_id" => $DATA["message"]["chat"]["id"], "text" =>  $GLOBALS["config"]["lgbt"]["text"]["groupHello"], "reply_to_message_id" => $DATA["message"]["message_id"]]);
                if (moduleOn("log", $DATA["message"]["chat"]["id"], $DATA, true)) {
                    API("sendMessage", ["chat_id" =>  $GLOBALS["config"]["lgbt"]["logChannelId"], "text" => $DATA["message"]["chat"]["title"] . "(@" . $DATA["message"]["chat"]["username"] . ")\nNuovo membro entrato nel gruppo:\n" . $DATA["message"]["from"]["first_name"] . " " . $DATA["message"]["from"]["last_name"] . "\n@" . $DATA["message"]["from"]["username"] . "\nLingua: " . $DATA["message"]["from"]["language_code"]]);
                }
            }
            if (!moduleOn("registerNewUsersInDb", $DATA["message"]["chat"]["id"], $DATA, true)) break;
            $u = $people->get($uid);
            $u->status = "askDm";
            $u->telegramName = $DATA["message"]["from"]["first_name"] . " " . $DATA["message"]["from"]["last_name"];
            $u->username = $DATA["message"]["from"]["username"];

            $u->save();
        } else {
            if (!moduleOn("updateUsernameData", $DATA["message"]["chat"]["id"], $DATA, true)) break;
            $u = $people->get($uid);
            $u->telegramName = $DATA["message"]["from"]["first_name"] . " " . $DATA["message"]["from"]["last_name"];
            $u->username = $DATA["message"]["from"]["username"];
            $u->save();
        }
        if ($DATA["message"]["chat"]["type"] === "private") {

            $u = $people->get($uid);
            switch ($u->status) {
                case "askDm":
                    API("sendMessage", ["chat_id" => $DATA["message"]["chat"]["id"], "text" => $GLOBALS["config"]["lgbt"]["text"]["privacy"], "reply_to_message_id" => $DATA["message"]["message_id"]]);
                    $u->status = "consent";
                    $u->save();
                    break;
                case "consent":
                    $a = strtolower(trim($t));
                    if ($a == "sì" || $a == "si" || $a == "ok" || $a == "yes") {
                        askNameFirst($DATA, $u->oneEdit == "askDm");
                        $u->status = "askName";
                        $u->save();
                    } else {
                        API("sendMessage", ["chat_id" => $DATA["message"]["chat"]["id"], "text" =>  $GLOBALS["config"]["lgbt"]["text"]["privacyNo"], "reply_to_message_id" => $DATA["message"]["message_id"]]);
                    }
                    break;
                case "askName":
                    if ($u->oneEdit != $u->status) {
                        if (isNo($t)) {
                            $u->name = false;
                            $u->save();
                            API("sendMessage", ["chat_id" => $DATA["message"]["chat"]["id"], "text" =>  $GLOBALS["config"]["lgbt"]["text"]["noProblem"], "reply_to_message_id" => $DATA["message"]["message_id"]]);
                        } else {
                            $u->name = $t;
                            $u->save();
                            API("sendMessage", ["chat_id" => $DATA["message"]["chat"]["id"], "text" =>  $GLOBALS["config"]["lgbt"]["text"]["thanks"], "reply_to_message_id" => $DATA["message"]["message_id"]]);
                        }
                    }
                    if ($u->oneEdit != $u->status && $u->oneEdit) {
                        $u->oneEdit = false;
                        $u->status = "end";
                        $u->save();
                        API("sendMessage", ["chat_id" => $DATA["message"]["chat"]["id"], "text" =>  $GLOBALS["config"]["lgbt"]["text"]["updatedData"], "reply_to_message_id" => $DATA["message"]["message_id"]]);

                        endM($DATA);
                        break;
                    }
                    $u->status = "askBirth";
                    $u->save();
                    API("sendMessage", ["chat_id" => $DATA["message"]["chat"]["id"], "text" =>  $GLOBALS["config"]["lgbt"]["text"]["birthAsk"], "reply_to_message_id" => $DATA["message"]["message_id"]]);

                    break;
                case "askBirth":
                    if ($u->oneEdit != $u->status) {

                        if (isNo($t)) {
                            $u->birthDate = false;
                            $u->save();
                            API("sendMessage", ["chat_id" => $DATA["message"]["chat"]["id"], "text" =>   $GLOBALS["config"]["lgbt"]["text"]["noProblem"], "reply_to_message_id" => $DATA["message"]["message_id"]]);
                        } else {
                            $e = explode("/", dateStrip($t));
                            if (!checkdate($e[1], $e[0], $e[2])) {
                                API("sendMessage", ["chat_id" => $DATA["message"]["chat"]["id"], "text" =>  $GLOBALS["config"]["lgbt"]["text"]["invalidDate"], "reply_to_message_id" => $DATA["message"]["message_id"]]);
                                API("sendMessage", ["chat_id" => $DATA["message"]["chat"]["id"], "text" =>   $GLOBALS["config"]["lgbt"]["text"]["birthAsk"], "reply_to_message_id" => $DATA["message"]["message_id"]]);
                                break;
                            }
                            $u->birthDate = dateStrip($t);
                            $u->save();
                            API("sendMessage", ["chat_id" => $DATA["message"]["chat"]["id"], "text" =>   $GLOBALS["config"]["lgbt"]["text"]["thanks"], "reply_to_message_id" => $DATA["message"]["message_id"]]);
                        }
                    }
                    if ($u->oneEdit != $u->status && $u->oneEdit) {
                        $u->oneEdit = false;
                        $u->status = "end";
                        $u->save();
                        API("sendMessage", ["chat_id" => $DATA["message"]["chat"]["id"], "text" =>   $GLOBALS["config"]["lgbt"]["text"]["updatedData"], "reply_to_message_id" => $DATA["message"]["message_id"]]);

                        endM($DATA);
                        break;
                    }
                    $u->status = "askGender";
                    $u->save();
                    API("sendMessage", ["chat_id" => $DATA["message"]["chat"]["id"], "text" => $GLOBALS["config"]["lgbt"]["text"]["genderAsk"], "reply_to_message_id" => $DATA["message"]["message_id"]]);

                    break;
                case "askGender":
                    if ($u->oneEdit != $u->status) {

                        if (isNo($t)) {
                            $u->gender = false;
                            $u->save();
                            API("sendMessage", ["chat_id" => $DATA["message"]["chat"]["id"], "text" =>   $GLOBALS["config"]["lgbt"]["text"]["noProblem"], "reply_to_message_id" => $DATA["message"]["message_id"]]);
                        } else {
                            $u->gender = $t;
                            $u->save();
                            API("sendMessage", ["chat_id" => $DATA["message"]["chat"]["id"], "text" =>   $GLOBALS["config"]["lgbt"]["text"]["thanks"], "reply_to_message_id" => $DATA["message"]["message_id"]]);
                        }
                    }
                    if ($u->oneEdit != $u->status && $u->oneEdit) {
                        $u->oneEdit = false;
                        $u->status = "end";
                        $u->save();
                        API("sendMessage", ["chat_id" => $DATA["message"]["chat"]["id"], "text" =>   $GLOBALS["config"]["lgbt"]["text"]["updatedData"], "reply_to_message_id" => $DATA["message"]["message_id"]]);

                        endM($DATA);
                        break;
                    }

                    $u->status = "askOrient";
                    $u->save();
                    API("sendMessage", ["chat_id" => $DATA["message"]["chat"]["id"], "text" =>   $GLOBALS["config"]["lgbt"]["text"]["orientAsk"], "reply_to_message_id" => $DATA["message"]["message_id"]]);

                    break;
                case "askOrient":
                    if ($u->oneEdit != $u->status) {

                        if (isNo($t)) {
                            $u->orient = false;
                            $u->save();
                            API("sendMessage", ["chat_id" => $DATA["message"]["chat"]["id"], "text" =>   $GLOBALS["config"]["lgbt"]["text"]["noProblem"], "reply_to_message_id" => $DATA["message"]["message_id"]]);
                        } else {
                            $u->orient = $t;
                            $u->save();
                            API("sendMessage", ["chat_id" => $DATA["message"]["chat"]["id"], "text" =>   $GLOBALS["config"]["lgbt"]["text"]["thanks"], "reply_to_message_id" => $DATA["message"]["message_id"]]);
                        }
                    }
                    if ($u->oneEdit != $u->status && $u->oneEdit) {
                        $u->oneEdit = false;
                        $u->status = "end";
                        $u->save();
                        API("sendMessage", ["chat_id" => $DATA["message"]["chat"]["id"], "text" =>   $GLOBALS["config"]["lgbt"]["text"]["updatedData"], "reply_to_message_id" => $DATA["message"]["message_id"]]);

                        endM($DATA);
                        break;
                    }
                    $u->status = "askWhere";
                    $u->save();
                    API("sendMessage", ["chat_id" => $DATA["message"]["chat"]["id"], "text" =>  $GLOBALS["config"]["lgbt"]["text"]["whereFrom"], "reply_to_message_id" => $DATA["message"]["message_id"]]);

                    break;
                case "askWhere":
                    if ($u->oneEdit != $u->status) {

                        if (isNo($t)) {
                            $u->where = false;
                            $u->save();
                            API("sendMessage", ["chat_id" => $DATA["message"]["chat"]["id"], "text" =>   $GLOBALS["config"]["lgbt"]["text"]["noProblem"], "reply_to_message_id" => $DATA["message"]["message_id"]]);
                        } else {
                            $u->where = $t;
                            $u->save();
                            API("sendMessage", ["chat_id" => $DATA["message"]["chat"]["id"], "text" =>   $GLOBALS["config"]["lgbt"]["text"]["thanks"], "reply_to_message_id" => $DATA["message"]["message_id"]]);
                        }
                    }
                    if ($u->oneEdit != $u->status && $u->oneEdit) {
                        $u->oneEdit = false;
                        $u->status = "end";
                        $u->save();
                        API("sendMessage", ["chat_id" => $DATA["message"]["chat"]["id"], "text" =>   $GLOBALS["config"]["lgbt"]["text"]["updatedData"], "reply_to_message_id" => $DATA["message"]["message_id"]]);

                        endM($DATA);
                        break;
                    }
                    $u->status = "askPVT";
                    $u->save();
                    API("sendMessage", ["chat_id" => $DATA["message"]["chat"]["id"], "text" => $GLOBALS["config"]["lgbt"]["text"]["shortDescription"], "reply_to_message_id" => $DATA["message"]["message_id"]]);

                    break;
                case "askPVT":
                    if ($u->oneEdit != $u->status) {

                        $u->pvtNotice = $t;
                    }
                    if ($u->oneEdit != $u->status && $u->oneEdit) {
                        $u->oneEdit = false;
                        $u->status = "end";
                        $u->save();
                        API("sendMessage", ["chat_id" => $DATA["message"]["chat"]["id"], "text" =>   $GLOBALS["config"]["lgbt"]["text"]["updatedData"], "reply_to_message_id" => $DATA["message"]["message_id"]]);

                        endM($DATA);
                        break;
                    }
                    $u->status = "askrel";
                    $u->save();
                    API("sendMessage", ["chat_id" => $DATA["message"]["chat"]["id"], "text" => $GLOBALS["config"]["lgbt"]["text"]["relM"], "reply_to_message_id" => $DATA["message"]["message_id"]]);

                    break;
                case "askrel":
                    if ($u->oneEdit != $u->status) {
                        if (isNo($t)) {
                            $u->rel = false;
                            $u->save();
                            API("sendMessage", ["chat_id" => $DATA["message"]["chat"]["id"], "text" =>   $GLOBALS["config"]["lgbt"]["text"]["noProblem"], "reply_to_message_id" => $DATA["message"]["message_id"]]);
                        } else {
                            $u->rel = $t;
                            $u->save();
                            API("sendMessage", ["chat_id" => $DATA["message"]["chat"]["id"], "text" =>   $GLOBALS["config"]["lgbt"]["text"]["thanks"], "reply_to_message_id" => $DATA["message"]["message_id"]]);
                        }
                    }

                    if ($u->oneEdit != $u->status && $u->oneEdit) {
                        $u->oneEdit = false;
                        $u->status = "end";
                        $u->save();
                        API("sendMessage", ["chat_id" => $DATA["message"]["chat"]["id"], "text" =>   $GLOBALS["config"]["lgbt"]["text"]["updatedData"], "reply_to_message_id" => $DATA["message"]["message_id"]]);

                        endM($DATA);
                        break;
                    }
                    $u->status = "askBio";
                    $u->save();
                    API("sendMessage", ["chat_id" => $DATA["message"]["chat"]["id"], "text" => $GLOBALS["config"]["lgbt"]["text"]["bioM"], "reply_to_message_id" => $DATA["message"]["message_id"]]);

                    break;
                case "askBio":
                    if ($u->oneEdit != $u->status) {

                        if (isNo($t)) {
                            $u->bio = false;
                            $u->save();
                            API("sendMessage", ["chat_id" => $DATA["message"]["chat"]["id"], "text" =>   $GLOBALS["config"]["lgbt"]["text"]["noProblem"], "reply_to_message_id" => $DATA["message"]["message_id"]]);
                        } else {
                            $u->bio = $t;
                            $u->save();
                            API("sendMessage", ["chat_id" => $DATA["message"]["chat"]["id"], "text" =>   $GLOBALS["config"]["lgbt"]["text"]["thanks"], "reply_to_message_id" => $DATA["message"]["message_id"]]);
                        }
                    }
                    if ($u->oneEdit != $u->status && $u->oneEdit) {
                        $u->oneEdit = false;
                        $u->status = "end";
                        $u->save();
                        API("sendMessage", ["chat_id" => $DATA["message"]["chat"]["id"], "text" =>   $GLOBALS["config"]["lgbt"]["text"]["updatedData"], "reply_to_message_id" => $DATA["message"]["message_id"]]);

                        endM($DATA);
                        break;
                    }
                    $u->status = "end";
                    $u->save();
                    endM($DATA);
                    break;
                case "sndSecret":
                    if ($t != "q") {
                        API("sendMessage", ["chat_id" => $GLOBALS["config"]["lgbt"]["secretMessagesGroupId"], "text" => $GLOBALS["config"]["lgbt"]["text"]["secret"] . $t]);

                        API("sendMessage", ["chat_id" => $DATA["message"]["chat"]["id"], "text" => $GLOBALS["config"]["lgbt"]["text"]["done"], "reply_to_message_id" => $DATA["message"]["message_id"]]);
                    } else {
                        API("sendMessage", ["chat_id" => $DATA["message"]["chat"]["id"], "text" => $GLOBALS["config"]["lgbt"]["text"]["cancelled"], "reply_to_message_id" => $DATA["message"]["message_id"]]);
                    }
                    $u->status = "end";
                    $u->tMessage = null;
                    $u->save();
                    break;

                case "snd":
                    if ($DATA["message"]["from"]["id"] != $GLOBALS["config"]["lgbt"]["devId"]) {
                        break;
                    }
                    if ($t != ":q") {
                        foreach (explode(",", $t) as $e) {
                            API("sendMessage", ["chat_id" => $u->groups[$e - 1][0], "text" => $u->tMessage]);
                        }
                        API("sendMessage", ["chat_id" => $DATA["message"]["chat"]["id"], "text" => $GLOBALS["config"]["lgbt"]["text"]["done"], "reply_to_message_id" => $DATA["message"]["message_id"]]);
                    } else {
                        API("sendMessage", ["chat_id" => $DATA["message"]["chat"]["id"], "text" => $GLOBALS["config"]["lgbt"]["text"]["cancelled"], "reply_to_message_id" => $DATA["message"]["message_id"]]);
                    }
                    $u->status = "end";
                    $u->tMessage = null;
                    $u->save();
                    break;

                case "sndPre":
                    if ($t != ":q") {
                        $str = "";
                        $groups = []; // [id,name],...
                        $i = 1;
                        foreach (file("groups") as $fg) {
                            $g = explode(",", $fg); // id, name
                            $groups[] = $g;
                            $str .= $i . ".\t" . $g[1] . "\n";
                            $i++;
                        }
                        API("sendMessage", ["chat_id" => $DATA["message"]["chat"]["id"], "text" => $str . "\n" . $GLOBALS["config"]["lgbt"]["text"]["csvTell"], "reply_to_message_id" => $DATA["message"]["message_id"]]);
                        $u->status = "snd";
                        $u->groups = $groups;
                        $u->tMessage = $t;
                    } else {
                        API("sendMessage", ["chat_id" => $DATA["message"]["chat"]["id"], "text" => $GLOBALS["config"]["lgbt"]["text"]["cancelled"], "reply_to_message_id" => $DATA["message"]["message_id"]]);
                        $u->status = "end";
                        $u->tMessage = null;
                    }
                    $u->save();
                    break;
                case "editCheck":
                    $c = count($correl);
                    $ch = (int)$t;
                    if ($ch > $c || $ch <= 0) {
                        API("sendMessage", ["chat_id" => $DATA["message"]["chat"]["id"], "text" => $GLOBALS["config"]["lgbt"]["text"]["invalidChoice"], "reply_to_message_id" => $DATA["message"]["message_id"]]);
                        $final = whatToModify($correl);
                        API("sendMessage", ["chat_id" => $DATA["message"]["chat"]["id"], "text" => $final, "reply_to_message_id" => $DATA["message"]["message_id"]]);
                        $u->status = "editCheck";
                        $u->save();
                        break;
                    }
                    API("sendMessage", ["chat_id" => $DATA["message"]["chat"]["id"], "text" => $GLOBALS["config"]["lgbt"]["text"]["ok"], "reply_to_message_id" => $DATA["message"]["message_id"]]);
                    $action = $correl[$ch - 1][1];

                    $u->oneEdit = $action;
                    $u->status = $action;
                    $u->save();
                    goto beg;
                    break;
                case "end":
                    $t = preg_replace("/[^A-Za-z0-9]/", '', $t);
                    $t = mb_strtolower($t);
                    $t = trim($t);
                    switch ($t) {
                        case "start":
                            API("sendMessage", ["chat_id" => $DATA["message"]["chat"]["id"], "text" =>   $GLOBALS["config"]["lgbt"]["text"]["thanks"], "reply_to_message_id" => $DATA["message"]["message_id"]]);
                            break;
                        case "secret":

                            API("sendMessage", ["chat_id" => $DATA["message"]["chat"]["id"], "text" => $GLOBALS["config"]["lgbt"]["text"]["writeYourSecret"], "reply_to_message_id" => $DATA["message"]["message_id"]]);
                            $u->status = "sndSecret";
                            $u->save();
                            break;

                        case "send":
                            if ($DATA["message"]["from"]["id"] != $GLOBALS["config"]["lgbt"]["devId"]) {
                                break;
                            }
                            API("sendMessage", ["chat_id" => $DATA["message"]["chat"]["id"], "text" => $GLOBALS["config"]["lgbt"]["text"]["willSend"], "reply_to_message_id" => $DATA["message"]["message_id"]]);
                            $u->status = "sndPre";
                            $u->save();
                            break;

                        case "me":
                            API("sendMessage", ["chat_id" => $DATA["message"]["chat"]["id"], "text" => makeList($u->toArray()), "reply_to_message_id" => $DATA["message"]["message_id"]]);
                            break;
                        case "edit":
                            $i = 1;
                            $final = whatToModify($correl);
                            $u->status = "editCheck";
                            $u->save();
                            API("sendMessage", ["chat_id" => $DATA["message"]["chat"]["id"], "text" => $final, "reply_to_message_id" => $DATA["message"]["message_id"]]);

                            break;
                        case "redo":
                            askNameFirst($DATA);
                            $u->status = "askName";
                            $u->save();
                            //API("sendMessage", ["chat_id" => $DATA["message"]["chat"]["id"], "text" => "Il bot ti ricontatterà  appena non scriverai qualcosa sul gruppo.","reply_to_message_id"=>$DATA["message"]["message_id"]]);

                            break;
                        case "optout":
                            API("sendMessage", ["chat_id" => $DATA["message"]["chat"]["id"], "text" => $GLOBALS["config"]["lgbt"]["text"]["dataErased"], "reply_to_message_id" => $DATA["message"]["message_id"]]);
                            $people->get($uid)->delete();
                            break;
                        default:
                            endM($DATA);
                            break;
                    }
                    break;
            }
        } else {
            //update every time the group name
            $lines = "";
            foreach (file("groups") as $l) {
                $g = explode(",", $l);
                if ($g[0] == $DATA["message"]["chat"]["id"]) {
                    continue;
                }
                $lines .= $l;
            }
           file_put_contents("groups", $lines. $DATA["message"]["chat"]["id"] . ", " . $DATA["message"]["chat"]["title"] . "\n");

        }
        break;
}



exit;


foreach ($people->findAll() as $p) {
    // dd/mm/YYYY must delete "/YYYY" 5 chars
    if (!empty($p->birthDate)) {
        $p->yearsOld = years($p->birthDate);
    }

    if (substr($p->birthDate, 0, 5) == date("d/m")) {
        if (!$p->saidBday) {
            foreach ($GLOBALS["config"]["lgbt"]["groups"] as $g) {
                if ($g["modules"]["happyBirthday"] == 1 && !isInGroup($g["id"], $p->getId()) || $g["modules"]["happyBirthday"] == 2) continue;
                API("sendMessage", ["chat_id" => $g["id"], "text" => $GLOBALS["config"]["lgbt"]["text"]["happyBirthdayTo"] . $p->name . " ( " . $p->telegramName . " , @" . $p->username . " ) !\n" . $p->yearsOld . " anni"]);
                $p->saidBday = true;
            }
        }
    } else {
        $p->saidBday = false;
    }

    $p->save();
}

if (file_get_contents("kingDate") != date("dmY")) {
    $rs = relgbt();
    foreach ($rs as $chid => $u) {
        API("sendMessage", ["chat_id" => $chid, "text" => $GLOBALS["config"]["lgbt"]["text"]["todaysKing"] . $u->name . "\n(" . $u->telegramName . ",@" . $u->username . ") "]);
        file_put_contents("kingDate", date("dmY"));
        //API("sendMessage", ["chat_id" => $ii, "text" => "Starting the P U R G E.\n this may take a while; the bot may become unresponsive"]);

    }
    $li = marry();
    foreach ($li as $chid => $ee) {
        $pa = $ee[0];
        $pb = $ee[1];
        $mm = $GLOBALS["config"]["lgbt"]["text"]["theMarriage"] . $pa->name . "(" . $pa->telegramName . ",@" . $pa->username . ") e " . $pb->name . "(" . $pb->telegramName . ",@" . $pb->username . $GLOBALS["config"]["lgbt"]["text"]["isSet"];
        API("sendMessage", ["chat_id" => $chid, "text" => $mm]);
    }

    foreach (glob("gdImg/archive/*") as $f) {
        unlink($f);
    }
    foreach ($GLOBALS["config"]["lgbt"]["groups"] as $g) {
        $grs[] = $g["id"];
    }
    $ir = purge($people, $grs);
    if (moduleOn("log", $DATA["message"]["chat"]["id"], $DATA, true)) {
        API("sendMessage", ["chat_id" => $GLOBALS["config"]["lgbt"]["logChannelId"], "text" => "$ir Old records P U R G E D."]);
    }
}
