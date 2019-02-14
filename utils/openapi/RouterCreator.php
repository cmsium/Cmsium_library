<?php
/**
 * Created by PhpStorm.
 * User: nick
 * Date: 2/14/19
 * Time: 4:19 PM
 */

namespace Openapi;

class RouterCreator {
    public $namespace;
    public $routsPath;
    public $controllersPath;

    public $routs = [];
    public $controllers = [];

    public function __construct($routsPath, $controllersPath, $namespace = "Routs") {
        $this->routsPath = $routsPath;
        $this->controllersPath = $controllersPath;
        $this->namespace = $namespace;
    }

    public function create($HTTPmethod, $routName, $class, $method) {
        $this->routs[$HTTPmethod][] = new Rout($routName, $class, $method, $this);
    }

    public function save() {
        $str =
            "<?php".PHP_EOL.
            "\$router = new Router();" . PHP_EOL;
        foreach ($this->routs as $HTTPmethod => $routs) {
            foreach ($routs as $rout) {
                $str .= $rout->getString($HTTPmethod);
                if (!key_exists($rout->class, $this->controllers)){
                    $this->controllers[$rout->class] = new Controller($rout->class, $this);
                }
                $this->controllers[$rout->class]->addMethod($rout->method);
            }
        }
        var_dump(realpath($this->routsPath));
        file_put_contents($this->routsPath, $str);
        foreach ($this->controllers as $controller){
            $controller->save();
        }
    }
}