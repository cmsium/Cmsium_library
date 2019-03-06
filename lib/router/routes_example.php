<?php
namespace Router;
use PHPUnit\Runner\Exception;

foreach (glob(__DIR__."/exceptions/*.php") as $value){
    require_once $value;
}
require_once "Router.php";
require_once "Routable.php";
require_once "Route.php";
require_once "Request.php";

$request = new Request();

$router = new Router();
$router->get("/users/{user}", function ($user) use ($request) {
    var_dump($user, $request->getArgs());
});

$router->post("/pets", "petsController", "create");


$router->route($request);