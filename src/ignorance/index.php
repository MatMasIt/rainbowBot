<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">
<meta name="robots" content="noindex" />
<title>Le persiane</title>

</head>
<body>
<h1>Epistolario</h1>

<p>La raccolta di lettere di "ANTI-LGBT گروه ضد", un savio gruppo di telegram dell'anno MMXX fascicolate, una lettura appassionante, ricca di tenore e di suspence, ma anche di riflessione profonda e filosofica</p>
<i>Per la traduzione, attivate google traduttore su Chrome (persiano->italiano)</i>
<p>Prefazione, ovvero estratto del canale ×𝗡𝗢 𝗙𝗔𝗚𝗚𝗢𝗧×</p>
<b>Nota dell'Autore:</b>
<blockquote>
𝐍𝐎 𝐅𝐀𝐆𝐆𝐎𝐓𝐒 𝐀𝐑𝐄 𝐀𝐋𝐋𝐎𝐖𝐄𝐃 𝐈𝐍 𝐇𝐄𝐑𝐄+

𝐄𝐕𝐄𝐑𝐘 𝐁𝐎𝐃𝐘 𝐒𝐇𝐎𝐔𝐋𝐃 𝐇𝐄𝐋𝐏 𝐄𝐀𝐂𝐇𝐎𝐓𝐇𝐄𝐑 𝐓𝐎 𝐃𝐄𝐒𝐓𝐑𝐎𝐘 𝐓𝐇𝐄𝐒𝐄 𝐒𝐈𝐂𝐊 𝐋𝐆𝐁𝐓 𝐅𝐀𝐆𝐆𝐒

🔥𝐉𝐎𝐈𝐍 𝐔𝐒 𝐓𝐎𝐃𝐀𝐘🔥
</blockquote>


<h3>Canale ×𝗡𝗢 𝗙𝗔𝗚𝗚𝗢𝗧×</h3>

<ul>
<?php
$i=1;
$a=glob("data/channel/*.html");
natsort($a);
foreach($a as $el){
?><li><a href="<?php echo $el;?>">TOMO <?php echo $i; ?></a></li><?php
$i++;
}
?>
</ul>

<h3>Gruppo ANTI-LGBT گروه ضد</h3>

<ul>
<?php
$i=1;
$a=glob("data/group/*.html");
natsort($a);
foreach($a as $el){
?><li><a href="<?php echo $el;?>">TOMO <?php echo $i; ?></a></li><?php
$i++;
}
?>
</ul>
<p>Editore <a href="https://t.me/MtMsdns">Mat Mas</a>, II edizione</p>
</body>

</html>
