<?php
namespace Errors\Traits;

trait AppException {
    protected $code = 500;
    protected $message = "Some error";

    public function handle($app) {
        $app->setStatusCode($this->code);
    }

    public function getMessage() {
        return $this->message();
    }
}