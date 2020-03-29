<?php
class A{
    public $t1 = 123;
    public static $t2 = 321;
    function foo(){
        $a = 1;
        $b = 2;
        $c = 3;
        echo $c;
    }
}
$obj = new A();
$obj->foo();
?>
