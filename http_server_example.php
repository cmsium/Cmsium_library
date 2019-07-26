<?php
include __DIR__.'/lib/init_libs.php';

// Create test pipeline pre
$plumber = \Plumber\Plumber::getInstance();
$pipeline = $plumber->buildPipeline('webgear.pre');
$pipeline->addPipe(function ($request) {
//    var_dump($request);
});

// Create test pipeline pre
$pipelinePost = $plumber->buildPipeline('webgear.post');
$pipelinePost->addPipe(function ($request) {
//    var_dump($request);
});

// Callback test pipeline
$callbackPipeline = $plumber->buildPipeline('callbacks.test');
$callbackPipeline->addPipe(function () {
    var_dump('WRYYYYY');
});

// Create router and application
$router = new \Router\Router();
$application = \Webgear\Swoole\Application::getInstance($router);

// Routes
$router->get('/hello', function() {
    //// Testing App
    $application = \Webgear\Swoole\Application::getInstance();
    $application->setHeader('Content-Type', 'text/plain');
    $application->setCookie('hi', 'value', 1000, '/some/path', 'some.domain', true, true);
    return $application->respondFile(ROOTDIR.'/INFO', 0, 0);
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

$router->get('/test/callbacks', function() { return 'Hello!'; })->before('callbacks.test');
// End Routes

// Add startup callback
$application->registerStartupCallback(function () { var_dump('I AM STARTUP CALLBACK!'); });

$server = new \HttpServer\Server($application);
$server->launch();