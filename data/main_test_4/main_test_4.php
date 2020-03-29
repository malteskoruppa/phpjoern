<?php
class A{
    public $t1 = 123;
    public $t2 = 321;
    function foo(){
        $a = 1;
        $b = 2;
        $c = 3;
        echo $c;
    }
    function bar(){
        $e = 4;
        $f = 5;
        $g = 6;
        echo $f;
    }
}

class B{
    public $t1 = 123;
    public $t2 = 321;
    function woo(){
        $a = 1;
        $b = 2;
        $c = 3;
        echo $c;
    }
    function moo(){
        $e = 4;
        $f = 5;
        $g = 6;
        echo $f;
    }
}

$t1 = 123;
$t2 = 321;
$obj = new A();
$obj->foo();
echo $t1;

function foo(){
    $t1 = 123;
    echo $t1;
}

function bar(){
    $t2 = 321;
    echo $t2;
}
?>
