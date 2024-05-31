<?php
// Error Handling
error_reporting(-1);
ini_set('display_errors', 1);

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Factory\AppFactory;
use Slim\Routing\RouteCollectorProxy;
use Slim\Routing\RouteContext;
use Dotenv\Dotenv;

require_once '../vendor/autoload.php';
require './Controller/EmpleadoController.php';
require './Controller/ProductoController.php';
require './Controller/MesaController.php';
require './Controller/PedidoController.php';
require_once './DataBase/AccesoDatos.php';

// Load ENV
$dotenv = Dotenv::createImmutable(__DIR__);
$dotenv->load();

$app = AppFactory::create();

// Add error middleware
$app->addErrorMiddleware(true, true, true);

// Add parse body
$app->addBodyParsingMiddleware();

$app->group('/empleados', function (RouteCollectorProxy $group) {
    $group->get('[/]', \EmpleadoController::class . ':TraerTodos');
    $group->get('/{nombre}', \EmpleadoController::class . ':TraerUno');
    $group->post('[/]', \EmpleadoController::class . ':GuardarUno');
    $group->put('[/]', \EmpleadoController::class . ':ModificarUno');
    $group->delete('[/]', \EmpleadoController::class . ':BorrarUno');
});

$app->group('/productos', function (RouteCollectorProxy $group) {
    $group->get('[/]', \ProductoController::class . ':TraerTodos');
    $group->get('/{id_producto}', \ProductoController::class . ':TraerUno');
    $group->post('[/]', \ProductoController::class . ':GuardarUno');
    // $group->put('/{id}', \UsuarioController::class . ':ModificarUno');
    // $group->delete('/{id}', \UsuarioController::class . ':BorrarUno');
});

$app->group('/pedidos', function (RouteCollectorProxy $group) {
    $group->get('[/]', \PedidoController::class . ':TraerTodos');
    $group->get('/{id_pedido}', \PedidoController::class . ':TraerUno');
    $group->post('[/]', \PedidoController::class . ':GuardarUno');
    // $group->put('/{id}', \UsuarioController::class . ':ModificarUno');
    // $group->delete('/{id}', \UsuarioController::class . ':BorrarUno');
});

$app->group('/mesas', function (RouteCollectorProxy $group) {
    $group->get('[/]', \MesaController::class . ':TraerTodos');
    $group->get('/{id_mesa}', \MesaController::class . ':TraerUno');
    $group->post('[/]', \MesaController::class . ':GuardarUno');
    // $group->put('/{id}', \UsuarioController::class . ':ModificarUno');
    // $group->delete('/{id}', \UsuarioController::class . ':BorrarUno');
});

$app->get('/test', function (Request $request, Response $response, array $args) {
    // $_GET
    $params = $request->getQueryParams();

    $response->getBody()->write(json_encode($params));

    return $response;
});


$app->run();
