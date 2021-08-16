<?php
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");
if($_GET["pass"]!="737373737361113273") exit;
$file = 'gdImg/archive/'.$_GET["id"].'.fi.jpeg'; 
$type = 'image/jpeg'; header('Content-Type:'.$type); header('Content-Length: ' . filesize($file));
readfile($file);
unlink($file);
unlink('gdImg/archive/'.$_GET["id"].'.pp.jpeg');