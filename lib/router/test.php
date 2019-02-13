<?php


namespace Router;

class test{
    use Routable;

    public function hello($user,$some){
        var_dump("test",$user,$some,$this->request);
    }
}