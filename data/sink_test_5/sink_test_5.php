<?php
class sink{
    public function foo($x=1){
        $a = $x;
        $b = 2;
        echo $a+$this->bar();
    }
   
    public function bar(){
        $c = 3;
        return $c; 
    }
}

$obj = new sink();
$obj->foo(6);

?>
