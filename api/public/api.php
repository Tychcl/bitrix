<?php
use Core\Router;
use Core\Response;
use Core\Request;
use Core\Routes;

require_once dirname(__DIR__) . '/vendor/autoload.php';
Dotenv\Dotenv::createUnsafeImmutable(__DIR__ . '/../')->load();

$routes = new Routes();
$routes->add('Api\DiskController');

$request = new Request();
$route = $routes->exists($request);
if($route === null){
    $response = new Response(404, ['error' => 'Wrong api route or method']);
    $response->send();
    return;
}

$request->route = $route;
$router = new Router();

$response = $router->dispatch($request);
$response->send();
?>