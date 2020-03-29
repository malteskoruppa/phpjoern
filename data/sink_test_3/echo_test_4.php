<?php
class stu{
    public function __construct(){
       $this->var = 666;
    }
    private function foo(){
       echo 123;
    }
    public function bar(){
       echo $this->var;
    }
}
$o = new stu();
$o->bar();
?>
