<?php

namespace Openapi;

class OpenApiGenerator {
    public $configPath = "config.ini";
    public $openapiPath;
    public $masksPath;
    public $routesPath;
    public $controllersPath;

    public $maskCreator;
    public $routerCreator;

    public $openapi;

    public function __construct() {
        $this->openapiPath = $this->getConfig("openapiPath");
        $this->masksPath = $this->getConfig("masksPath");
        $this->routesPath = $this->getConfig("routesPath");
        $this->controllersPath = $this->getConfig("controllersPath");

        $this->maskCreator = new ValidationMaskCreator("masks");
        $this->routerCreator = new RouterCreator("routes/routes.php","controllers");
    }

    public function getConfig($config_name) {
        if (file_exists($this->configPath)) {
            $config = parse_ini_file($this->configPath);
            return $config[$config_name];
        } else {
            die('Config file not found!'.PHP_EOL);
        }
    }

    public function parse() {
        $str = file_get_contents($this->openapiPath);
        if ($str) {
            $this->openapi = json_decode($str);
        } else {
            die("OpenAPI manifest not found!");
        }
    }

    public function generateRoutes() {
        foreach ($this->openapi->paths as $pathName => $path){
            foreach (get_object_vars($path) as $method => $props){
                $this->routerCreator->create($method,$pathName, $props->tags[0],$props->operationId);
            }
        }
        $this->routerCreator->save();
    }

    public function generateMasks() {
        echo "masks: ".PHP_EOL;
        foreach ($this->openapi->paths as $pathName => $path){
            foreach (get_object_vars($path) as $method => $props){
                if (isset($props->parameters)){
                    $data = json_decode(json_encode($props->parameters), true);
                    $this->maskCreator->create(ucfirst($props->operationId), $data, "OpenAPIParameters");
                }
                if (isset($props->requestBody)){
                    foreach ($props->requestBody->content as $contentType => $value){
                        switch ($contentType){
                            case 'application/json':
                                $data = json_decode(json_encode($value->schema), true);
                                $this->maskCreator->create(ucfirst($props->operationId), $data, "OpenAPIContent");
                                break;
                        }
                    }
                }
            }
        }
    }
}