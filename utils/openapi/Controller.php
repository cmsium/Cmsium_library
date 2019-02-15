<?php
/**
 * Created by PhpStorm.
 * User: nick
 * Date: 2/14/19
 * Time: 5:23 PM
 */

namespace Openapi;


class Controller {
    public $name;
    public $methods=[];

    public $routerCreator;

    public function __construct($class, $routerCreator) {
        $this->name = $class;
        $this->routerCreator = $routerCreator;
    }

    public function addMethod($method) {
        if (!in_array($method, $this->methods))
            $this->methods[] = $method;
    }

    public function save(){
        $str =
            "<?php".PHP_EOL.
            "namespace {$this->routerCreator->namespace};".PHP_EOL.PHP_EOL.
            "class ".ucfirst($this->name)." {".PHP_EOL.
            "use Routable;".PHP_EOL;
        foreach ($this->methods as $method) {
            $str .= "public function {$method} () {}".PHP_EOL;
        }
        $str .= "}";
        file_put_contents($this->routerCreator->controllersPath."/".ucfirst($this->name).".php", $str);
        echo "  ".$this->name.PHP_EOL;
    }
}