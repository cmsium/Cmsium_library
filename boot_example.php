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

// Create router and application
$router = new \Router\Router();
$application = \Webgear\Swoole\Application::getInstance($router);

// Insert DB connection
$application->db = new \DB\MysqlConnection;

// Routes
$router->get('/test', function() {
    app()->setHeader('Content-Type', 'application/json');
    app()->setCookie('foo', 'bar');

    app()->db->insert("INSERT INTO staff(id, name, phone) VALUES (1, 'Richard', '9746356473')");
    return ['hi' => 'mark'];
});

$router->delete('/test', function() {
    return ['test' => 'ok'];
});
// End Routes

function app(): \Webgear\Swoole\Application {
    return \Webgear\Swoole\Application::getInstance();
}