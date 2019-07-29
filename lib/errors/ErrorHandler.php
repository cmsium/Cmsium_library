<?php
namespace Errors;


class ErrorHandler {
    protected $app;
    protected $logger;

    public function __construct($app, $logger = null){
        $this->app = $app;
        $this->logger = $logger;
    }

    public function log($e){
        if ($this->logger) {
            $this->logger->log($e);
        }
    }

    public function handle(\Exception $e, $response){
        $this->log($e);
        if (method_exists($e, 'handle')) {
            $e->handle($this->app);
        }
        return $this->formatOutput($response, $e);
    }

    public function formatOutput($response, \Exception $e) {
        switch ($response->getHeader('Content-Type')){
            case 'application/json': return $e->get(); break;
            case 'text/html': return $this->formatPage($e); break;
            default: return $this->formatPage($e);
        }
    }

    public function formatPage(\Exception $e) {
        return $e->get();
    }
}