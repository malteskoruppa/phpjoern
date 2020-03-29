<?php
function bar(){
    $a = woo();
    echo $a;
    echo '123';
    $b = '456';
    echo $a+$b;
}

function woo(){
    $c = $_GET['id'];
    echo $c;
    return $c;	    
}
?>
