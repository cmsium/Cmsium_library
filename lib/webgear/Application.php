<?php

namespace Webgear;

/**
 * Class Application (Singleton)
 *
 * @package Webgear
 */
class Application {

    public $router;
    public $request;
    public $response;

    public static $instance;

    public static function getInstance($router = null) {
        if (static::$instance != null) {
            return static::$instance;
        }

        static::$instance = new static($router);
        return static::$instance;
    }

    public function __construct($router) {
        $this->router = $router;

        // Register psr-4 autoloader for app classes
        $this->registerAppClassesLoader('app');
    }

    public function handle($request, $response) {
        $this->request = $request;
        $this->response = $response;
    }

    private function registerAppClassesLoader($directory) {
        $loader = new Autoloader;
        $loader->addNamespace('App', ROOTDIR."/$directory");
        $loader->register();
    }

}