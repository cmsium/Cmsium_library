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

    public function handle(\Exception $e, $request){
        $this->log($e);
        if (method_exists($e, 'handle')) {
            $e->handle($this->app);
        }
        return $this->formatOutput($request, $e);
    }

    public function formatOutput($request, \Exception $e) {
        switch ($request->output_type){
            case 'string': return $e->getMessage(); break;
            case 'page': return $this->formatPage($e->getMessage()); break;
            default: return $this->formatPage($e->getMessage());
        }
    }

    public function formatPage($data) {
        return $data;
    }
}