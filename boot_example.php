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

// Routes
$router->get('/test', function() {
    return ['hi' => 'mark'];
});
// End Routes

function app() {
    return \Webgear\Swoole\Application::getInstance();
}