<?php


namespace Router;
use Request;

trait Routable{
    public $request;

    public function __construct(Request $request){
        $this->request = $request;
    }
}