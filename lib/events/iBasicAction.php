<?php
interface iBasicAction{
    public function __construct($method);
    public function before($event);
    public function after($event);
}