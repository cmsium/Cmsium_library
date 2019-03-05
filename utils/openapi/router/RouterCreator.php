<?php
/**
 * Created by PhpStorm.
 * User: nick
 * Date: 2/14/19
 * Time: 4:19 PM
 */

namespace Openapi\Router;

class RouterCreator {
    public $namespace;
    public $routesPath;
    public $controllersPath;

    public $routes = [];
    public $controllers = [];
    public $tags = [];

    public function __construct($routesPath, $controllersPath, $namespace = "Router") {
        $this->routesPath = $routesPath;
        $this->controllersPath = $controllersPath;
        $this->namespace = $namespace;
    }

    public function addTags($tags) {
        foreach ($tags as $tag){
            $this->tags[$tag->name] = $tag;
        }
    }

    public function createTag($name, $description) {
        $tag = new \StdClass();
        $tag->name = $name;
        $tag->description = $description;
        $this->tags[$name] = $tag;
    }

    public function create($HTTPmethod, $routName, $class, $method, $summary, $description) {
        $route = new RouteCreator($routName, $HTTPmethod);
        $route->attachMethod($class, $method);
        $route->attachMethodMeta($summary, $description);
        $this->routes[$HTTPmethod][$routName] = $route;
    }

    public function read() {
        $router = new Router();
        include $this->routesPath;
        $routes = $router->routes;
        foreach ($routes as $HTTPmethod => $paths){
           foreach ($paths as $path){
               $class = $this->namespace."\\".$path->class;
               $method = $path->method;
               $doc = new DocBlockParser($class, $method);
               $summary = $doc->getMethodSummary();
               $description = $doc->getMethodDescription();
               $classDescription = $doc->getClassDescription();
               $this->createTag($path->class, $classDescription);
               $this->create($HTTPmethod, $path->path, $path->class, $path->method,$summary, $description);
           }
        }
        return $this->routes;
    }

    public function save() {
        echo "routes: ".PHP_EOL;
        $str =
            "<?php".PHP_EOL;
        foreach ($this->routes as $HTTPmethod => $routes) {
            foreach ($routes as $rout) {
                $str .= $rout->getString($HTTPmethod);
                if (!key_exists($rout->class, $this->controllers)){
                    $this->controllers[$rout->class] = new Controller(
                        $rout->class,
                        $this->namespace,
                        $this->controllersPath,
                        $this->tags[$rout->class]->description);
                }
                $this->controllers[$rout->class]->addMethod($rout->method, $rout->summary, $rout->description);
                echo "  ".$rout->path.PHP_EOL;
            }
        }
        file_put_contents($this->routesPath, $str);
        echo "controllers: ".PHP_EOL;
        foreach ($this->controllers as $controller){
            $controller->save();
        }
    }
}