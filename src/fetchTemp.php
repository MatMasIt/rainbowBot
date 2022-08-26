<?php
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");
$file = 'temp/'.preg_replace("/[^A-Za-z0-9 ]/", '', $_GET["id"]).'.jpg'; 
$type = 'image/jpeg';
header('Content-Type:'.$type);
header('Content-Length: ' . filesize($file));
readfile($file);
unlink($file);
unlink('temp/'.$_GET["id"].'.jpg');
