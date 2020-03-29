<?php
class A{
    public function __construct(){
      echo $a;
   }
    public function foo(){
      echo $b+$c;
    }
}


class B{
    private function bar(){
      $e = 123;
      echo htmlspecialchars($d)+$e;
     }
}
