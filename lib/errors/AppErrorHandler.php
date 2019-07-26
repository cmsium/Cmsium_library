<?php
namespace Errors;


class AppErrorHandler extends ErrorHandler {
    protected $pageBuilder;
    protected $defaultErrorTemplate;

    public function __construct($app, $pageBuilder, $defaultErrorTemplate, $logger = null){
        $this->app = $app;
        $this->logger = $logger;
        $this->pageBuilder = $pageBuilder;
        $this->defaultErrorTemplate = $defaultErrorTemplate;
    }

    public function formatPage($data) {
        $template = $e->template ?? $this->defaultErrorTemplate;
        $page = $this->pageBuilder->build($template);
        return $page->with(['error' => $data])->render();
    }
}