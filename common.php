<?php
function itdate($unix)
{
    $daysIT = ["Lunedì", "Martedì", "Mercoledì", "Giovedì", "Venerdì", "Sabato", "Domenica"];
    $monthsIT = ["Gennaio", "Febbraio", "Marzo", "Aprile", "Maggio", "Giugno", "Luglio", "Agosto", "Settembre", "Ottobre", "Novembre", "Dicembre"];
    return  $daysIT[date("N", $unix) - 1] . " " . date("d", $unix) . " " . $monthsIT[date("m", $unix) - 1] . " " . date("Y", $unix);
}
function contains($needle, $haystack)
{
    return (strpos($haystack, $needle) !== false);
}
function ellipses(string $string, int $len = 40, $ellipses = "...")
{
    $slen = $len - strlen($ellipses);
    return strlen($string) > $slen ? substr($string, 0, $slen) . "..." : $string;
}
