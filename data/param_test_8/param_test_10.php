<?php

class A{
    public $a;

    public function bar(){
        $this->a =  $_GET[1];
    }

    public  function foo(){
         echo $this->a;
    }
}
