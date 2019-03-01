<?php
include __DIR__.'/lib/init_libs.php';

$router = new \Router\Router();
$application = \Webgear\Swoole\Application::getInstance($router);

// Routes
$router->get('/users', function() {
    return 'Hi!';
});

$router->get('/test', function() {
    $application = \Webgear\Swoole\Application::getInstance();
    $application->setHeader('X-Hello', 'World');

    $testVar = 'Hello!';

    // Using Presenter lib to render stuff
    $page = \Presenter\PageBuilder::getInstance()->build('template');
    $pageOutput = $page->with(compact('testVar'))->render();

    return $pageOutput;
});
// End Routes

$server = new \HttpServer\Server($application);
$server->launch();