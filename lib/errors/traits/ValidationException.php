<?php
namespace Errors\Traits;

class ValidationException {
    use AppException;

    protected $code = 422;
    protected $message = "Validation error";
    protected $template = "validation_error";
    public $errors;

    public function __construct(array $errors, \Exception $previous = null) {
        parent::__construct($this->message, $this->code, $previous);
        $this->errors = $errors;
    }

    public function getMessage() {
        return $this->errors;
    }
}