<?php
include __DIR__.'/lib/init_libs.php';

$router = new \Router\Router();
$application = \Webgear\Swoole\Application::getInstance($router);

// Routes
$router->get('/users', function() {
    return 'Hi!';
});

$router->get('/test/array', function() {
    $application = \Webgear\Swoole\Application::getInstance();
    $application->setHeader('X-Hello', 'World');
    $testVar = 'hi!';
    echo 'asdasdasdas';
    ob_start();
    include __DIR__.'/template.html.php';
    $output = ob_get_clean();
    return $output;
});
// End Routes

$server = new \HttpServer\Server($application);
$server->launch();