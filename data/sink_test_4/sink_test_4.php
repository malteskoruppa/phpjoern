<?php
function foo(){
    $a = moo();
    $b = woo();
    echo $a+$b+bar();
}

function moo(){
    $x = 1;
    return $x;
}
function woo(){
    $y = 2;
    return $y;
}

function bar(){
    $z = 3;
    return $z;
}
?>
