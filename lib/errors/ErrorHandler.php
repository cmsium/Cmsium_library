<?php
namespace Errors;


class ErrorHandler {
    protected $app;
    protected $logger;

    public function __construct($logger = null){
        $this->logger = $logger;
    }

    public function log($e){
        if ($this->logger) {
            $this->logger->log($e);
        }
    }

    public function handle($app, \Exception $e){
        $this->log($e);
        if (method_exists($e, 'handle')) {
            $e->handle($app);
        }
        return $this->formatOutput($app, $e);
    }

    public function formatOutput($app, \Exception $e) {
        switch ($app->getHeader('Content-Type')){
            case 'application/json': return $e->get(); break;
            case 'text/html': return $this->formatPage($e); break;
            default: return $this->formatPage($e);
        }
    }

    public function formatPage(\Exception $e) {
        return $e->get();
    }
}