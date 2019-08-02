<?php
include __DIR__.'/lib/init_libs.php';

function app() {
    return \HttpServer\Server::getInstance()->currentApp();
}

// Create test pipeline pre
$plumber = \Plumber\Plumber::getInstance();
$pipeline = $plumber->buildPipeline('webgear.pre');
$pipeline->addPipe(function ($request) {
    $application = app();
    if (!isset($application->pool)) {
        $application->pool = \DB\Pool\SwooleMysql::getInstance(
            '\DB\SwooleMysqlConnection',
            5
        );
    }
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

//$application->errorHandler = new \Errors\AppErrorHandler($application, new \Presenter\PageBuilder(), 'error');

// Routes
$router->get('/hello', function() {
    //// Testing App
    $application = app();
    $application->setHeader('Content-Type', 'text/plain');
    $application->setCookie('hi', 'value', 1000, '/some/path', 'some.domain', true, true);
    return $application->respondFile(ROOTDIR.'/INFO', 0, 0);
});

$router->get('/test', function() {
    $application = app();

    $application->setHeader('X-Hello', 'World');

    $dbConnection = $application->pool->fetch();
    $dbConnection->insert("INSERT INTO cmsium_library.staff(name, phone) VALUES ('hello', '1236859478')");
    $application->pool->free($dbConnection);

    return 'HI!';
});

$router->get('/test/callbacks', function() { return 'Hello!'; })->before('callbacks.test');
// End Routes

// Add startup callback
$application->registerStartupCallback(function () { var_dump('I AM STARTUP CALLBACK!'); });

$server = \HttpServer\Server::getInstance($application);
$server->launch();