<?php
/**
 * Created by PhpStorm.
 * User: nick
 * Date: 2/14/19
 * Time: 5:23 PM
 */

namespace Openapi\Router;


class Controller {
    public $name;
    public $namespace;
    public $controllersPath;
    public $description;
    public $methods=[];

    public function __construct($class, $namespace, $controllersPath, $description) {
        $this->name = $class;
        $this->namespace = $namespace;
        $this->controllersPath = $controllersPath;
        $this->description = $description;
    }

    public function addMethod($method, $summary, $description) {
        if (!key_exists($method, $this->methods))
            $this->methods[$method] = ['summary' => $summary, 'description' => $description];
    }

    public function save(){
        $str =
            "<?php".PHP_EOL.
            "namespace {$this->namespace};".PHP_EOL.PHP_EOL.
            "/**".PHP_EOL.
            " * @description {$this->description}".PHP_EOL.
            " */".PHP_EOL.
            "class ".ucfirst($this->name)." {".PHP_EOL.
            "   use Routable;".PHP_EOL;
        foreach ($this->methods as $method => $data) {
            $str .=
                PHP_EOL.
                "   /**".PHP_EOL.
                "    * @summary {$data['summary']}".PHP_EOL.
                "    * @description {$data['description']}".PHP_EOL.
                "    */".
                PHP_EOL."   public function {$method} () {}".PHP_EOL;
        }
        $str .= "}";
        file_put_contents($this->controllersPath."/".ucfirst($this->name).".php", $str);
        echo "  ".$this->name.PHP_EOL;
    }
}