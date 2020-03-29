<?php
function bar($x, $y) {
    echo $x+$y;
}

function moo() {
    $a = $_GET["mid"];
    $b = 1;
    bar($a, $b);
}

function woo() {
    $a = 2;
    $b = $_GET["wid"];   
    bar($a, $b);
}
?>
