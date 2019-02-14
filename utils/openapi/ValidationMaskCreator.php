<?php
namespace Openapi;


class ValidationMaskCreator {
    public $path;
    public $namespace;
    public $masks=[];

    public function __construct($savePath, $namespace = "Validation\masks") {
        $this->path = $savePath;
        $this->namespace = $namespace;
    }

    public function create(string $maskName,array $maskData,string $maskType = null) {
        if (!$maskType){
            $maskType = " OpenAPIParameters";
        }
        $mask = new ValidationMask($maskName,$maskData, $maskType, $this);
        $mask->createString();
        $mask->save($this->path);
        $this->masks[$maskType][$maskName] = $mask;
    }

}