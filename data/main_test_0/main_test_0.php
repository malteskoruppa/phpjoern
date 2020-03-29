<?php
class A{
    public $t = 123;
    function foo(){
        $a = 1;
        $b = 2;
        $c = 3;
        echo $c;
    }

    function bar(){
        $x = 1;
        $y = 2;
        $z = 3;
        echo $y;
    }
}
class B{
    public $t2 = 123;
    function woo(){
        $a2 = 1;
        $b2 = 2;
        $c2 = 3;
        echo $c2;
    }
}

$obj = new A();
$obj->foo();
?>
