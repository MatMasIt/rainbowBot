<?php
require "database/DatabaseUser.php";
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");
$data = json_decode($_GET["data"], true);


?>
<div class="card">
    <img src="<?php echo htmlentities($data["img"]); ?>" alt="Immagine" id="pic">
    <header class="eva-heading" id="cen">
        <h2 class="eva-heading__title"><img src="https://images.emojiterra.com/google/android-10/512px/1f3f3-1f308.png" style="width:20px"> Rainbow Bot <img src="https://images.emojiterra.com/google/android-10/512px/1f3f3-1f308.png" style="width:20px"></h2><br />
        <h4 class="eva-heading__episode-title">
            <?php if ($data["name"]) {
            ?>
                <i>Nome</i>:&nbsp;&nbsp;&nbsp;<?php echo htmlentities($data["name"]); ?> <br />
            <?php
            }
            if ($data["birth"]) {
            ?>
                <i>Data di Nascita</i>:&nbsp;&nbsp;&nbsp;<?php echo itdate($data["birth"]) . " (" . DatabaseUser::calcAge((int)$data["birth"]) . " anni)" ?><br />
            <?php
            }
            if ($data["gender"]) {
            ?>
                <i>Genere</i>:&nbsp;&nbsp;&nbsp;<?php echo htmlentities($data["gender"]); ?> <br />
            <?php
            }
            if ($data["orientation"]) {
            ?>
                <i>Orientamento</i>:&nbsp;&nbsp;&nbsp;<?php echo htmlentities($data["orientation"]); ?><br />
            <?php
            }
            if ($data["where"]) {
            ?>
                <i>Provenienza</i>:&nbsp;&nbsp;&nbsp;<?php echo htmlentities($data["where"]); ?><br />
            <?php
            }
            if ($data["pvt"]) {
            ?>
                <i>Messaggi privati</i>:&nbsp;&nbsp;&nbsp;<?php echo htmlentities($data["pvt"]); ?><br />
            <?php
            }
            if ($data["rel"]) {
            ?>
                <i>Relazioni</i>:&nbsp;&nbsp;&nbsp;<?php echo htmlentities($data["rel"]); ?><br />
            <?php
            }
            ?>
        </h4>
        <br />
        <?php
        if ($data["isDev"]) $data["flags"][] = "dev";
        foreach ($data["flags"] as $flag) {
        ?>
            <img src="flags/<?php echo htmlentities($flag); ?>.png" class="flag">
        <?php
        }
        ?>
    </header>
</div>
<style>
    .flag {
        width: 100px;
    }

    .card {
        width: 600px;
        height: 478px;
        /*border: 1px solid white;*/
    }

    * {
        margin: 0;
        padding: 0;
        color: white;
        font-size: 25px;
    }



    #pic {
        width: 200px;
        height: 200px;
        float: right;
    }

    #cen {
        margin-top: 32px;
        margin-left: 32px;
    }

    body {
        background-color: black;
    }

    h2 {
        font-size: 30px;
    }
</style>