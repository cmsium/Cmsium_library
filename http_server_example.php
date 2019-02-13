<?php
include __DIR__.'/lib/init_libs.php';

$router = new \Router\Router();

$router->get('/users', function() {
    return 'Hi!';
});

$server = new \HttpServer\Server($router);
$server->launch();