<?php
function evaSplit($text)
{
    $text = chunk_split($text, 38, "\n");
    $list = explode("\n", $text);
    array_filter($list);
    $f = "";
    foreach ($list as $el) {
        $f .= htmlentities(trim($el)) . "<br />";
    }
    return $f;
}
$data = json_decode($_GET["data"]);
if ($data == null) {
?>
    <header class="eva-heading">
        <h2 class="eva-heading__title">NEON</h2>
        <h2 class="eva-heading__title">GENESIS</h2>
        <h1 class="eva-heading__title">EVANGELION</h1>
        <h3 class="eva-heading__episode-number">EPISODE: YES</h3>
        <h4 class="eva-heading__episode-title">
            Application Error, call NERV
        </h4>
    </header>
<?php
} else {
?>
    <header class="eva-heading">
        <h2 class="eva-heading__title"><?php echo htmlentities($data[0]); ?></h2>
        <h2 class="eva-heading__title"><?php echo htmlentities($data[1]); ?></h2>
        <h1 class="eva-heading__title"><?php echo htmlentities($data[2]); ?></h1>
        <h3 class="eva-heading__episode-number"><?php echo htmlentities($data[3]); ?></h3>
        <h4 class="eva-heading__episode-title">
            <?php echo evaSplit($data[4]); ?>
        </h4>
    </header>
<?php
}
?>
<style>
    * {
        margin: 0;
        padding: 0;
    }

    body {
        background-color: black;
    }

    .eva-heading {
        padding: 32px;
        align-self: baseline;
        font-family: serif;
        color: white;
        text-shadow: 0 0 2px #e19a86, 0 0 1.5px #854535, 0 0 1.5px #5c150c;
        width: 600px;
        height: 478px;
    }

    .eva-heading>h1 {
        font-size: 500%;
    }

    .eva-heading>h2 {
        font-size: 300%;
    }

    .eva-heading__title {
        transform: scale(1, 1.5);
        line-height: 1.2em;
        letter-spacing: -.03em;
    }

    .eva-heading__episode-number {
        font-family: sans-serif;
        font-size: 180%;
        transform: scale(1, 1.5);
        letter-spacing: -.06em;
        margin: 10px 0 26px 0;
    }

    .eva-heading__episode-title {
        transform: scale(1, 1.3);
        font-size: 170%;
        line-height: 1em;
    }
</style>