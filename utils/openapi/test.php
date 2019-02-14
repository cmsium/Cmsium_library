<?php
namespace Openapi;

include "ValidationMask.php";
include "ValidationMaskCreator.php";
include "RouterCreator.php";
include "Rout.php";
include "Controller.php";

//TODO classes and console

$maskCreator = new ValidationMaskCreator("masks");
$routerCreator = new RouterCreator("routes/routes.php","controllers");

$root = json_decode(file_get_contents("../../../Cmsium_files/api.json"));

foreach ($root->paths as $pathName => $path){
    foreach (get_object_vars($path) as $method => $props){
        $routerCreator->create($method,$pathName, $props->tags[0],$props->operationId);

        if (isset($props->parameters)){
            $data = json_decode(json_encode($props->parameters), true);
            $maskCreator->create(ucfirst($props->operationId), $data, "OpenAPIParameters");
        }
        if (isset($props->requestBody)){
            foreach ($props->requestBody->content as $contentType => $value){
                switch ($contentType){
                    case 'application/json':
                        $data = json_decode(json_encode($value->schema), true);
                        $maskCreator->create(ucfirst($props->operationId), $data, "OpenAPIContent");
                        break;
                }
            }
        }
    }
}
$routerCreator->save();
