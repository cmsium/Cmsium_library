<?php


class ErrorHandler {
    public $exceptions = [];
    public $logger;

    public function __construct($logger){
        $this->logger = $logger;
    }

    public function log($e){
        $this->logger->log($e);
    }

    public function handle(Exception $e, ...$args){
        $this->exceptions[] = $e;
        $this->log($e);
        if (method_exists($e, 'handle'))
            $e->handle(...$args);
    }

}