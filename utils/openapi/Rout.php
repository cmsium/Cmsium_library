<?php
/**
 * Created by PhpStorm.
 * User: nick
 * Date: 2/14/19
 * Time: 4:40 PM
 */

namespace Openapi;


class Rout {
    public $name;
    public $class;
    public $method;
    public $routerCreator;

    public function __construct($routName, $class, $method, $routerCreator) {
        $this->name = $routName;
        $this->class = $class;
        $this->method = $method;
        $this->routerCreator = $routerCreator;
    }

    public function getString($HTTPmethod){
        return "\$router->$HTTPmethod(\"{$this->name}\", \"".ucfirst($this->class)."\", \"{$this->method}\");".PHP_EOL;
    }
}