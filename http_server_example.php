<?php
include __DIR__.'/lib/init_libs.php';

class Application implements \HttpServer\SwooleHttpApplication {

    public $router;

    public function __construct(\Router\Router $router) {
        $this->router = $router;
    }

    public function handle($request, $response) {
        $request = new \HttpServer\SwooleRequest($request);
        $result = $this->router->route($request);
        $response->end($result.PHP_EOL);
    }

}

$router = new \Router\Router();

// Routes
$router->get('/users', function() {
    return 'Hi!';
});
// End Routes

$application = new Application($router);

$server = new \HttpServer\Server($application);
$server->launch();