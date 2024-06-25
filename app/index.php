<?php
// Error Handling
error_reporting(-1);
ini_set('display_errors', 1);

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\RequestHandlerInterface as RequestHandler;
use Slim\Psr7\Response as ResponseClass;
use Slim\Factory\AppFactory;
use Slim\Routing\RouteCollectorProxy;
use Slim\Routing\RouteContext;
use Dotenv\Dotenv;

require_once '../vendor/autoload.php';
require './Controller/EmpleadoController.php';
require './Controller/ProductoController.php';
require './Controller/ProductoPedidoController.php';
require './Controller/PedidoController.php';
require './Controller/MesaController.php';
require './Controller/EncuestaController.php';
require './Controller/LoginController.php';
require './Middleware/AuthEmpleados.php';
require './Middleware/AuthProductos.php';
require './Middleware/AuthMiddleware.php';
require './Middleware/LoggerMW.php';
require_once './DataBase/AccesoDatos.php';

// Load ENV
$dotenv = Dotenv::createImmutable(__DIR__);
$dotenv->load();

$app = AppFactory::create();

// Add error middleware
$app->addErrorMiddleware(true, true, true);

// Add parse body
$app->addBodyParsingMiddleware();

$app->group('/auth', function (RouteCollectorProxy $group) {
    $group->post('/login', \LoginController::class . ':Login');
});

$app->group('/empleados', function (RouteCollectorProxy $group) {
    $group->get('[/]', \EmpleadoController::class . ':TraerTodos');
    $group->get('/{usuario}', \EmpleadoController::class . ':TraerUno');
    $group->post('[/]', \EmpleadoController::class . ':GuardarUno')->add(\AuthEmpleados::class . ':ValidarCampos')
        ->add(new AuthMiddleware('socio'));
    $group->put('[/]', \EmpleadoController::class . ':ModificarUno')->add(\AuthEmpleados::class . ':ValidarCampos')
        ->add(new AuthMiddleware('socio'));
    $group->delete('[/]', \EmpleadoController::class . ':BorrarUno');
})->add(new LoggerMW());


$app->group('/productos', function (RouteCollectorProxy $group) {
    $group->get('[/]', \ProductoController::class . ':TraerTodos');
    $group->get('/{id_producto}', \ProductoController::class . ':TraerUno');
    $group->post('[/]', \ProductoController::class . ':GuardarUno');
    $group->put('[/]', \ProductoController::class . ':ModificarUno')->add(\AuthProductos::class . ':ValidarRol')
        ->add(new LoggerMW());
    $group->delete('[/]', \ProductoController::class . ':BorrarUno')->add(\AuthProductos::class . ':ValidarRol')
        ->add(new LoggerMW());
});

$app->group('/productopedidos', function (RouteCollectorProxy $group) {
    $group->get('[/]', \ProductoPedidoController::class . ':ObtenerProductoPedidos');
    $group->get('/listar', \ProductoPedidoController::class . ':ListadoProductosPorSector');
    $group->get('/sector', \ProductoPedidoController::class . ':TraerSectorProducto')->add(new LoggerMW());
    $group->post('[/]', \ProductoPedidoController::class . ':AltaProductoPedido');
    $group->post('/realizar', \ProductoPedidoController::class . ':EmpleadoTomaProducto')->add(\AuthProductos::class . ':ValidarRol')
        ->add(new LoggerMW());
    $group->post('/servir', \ProductoPedidoController::class . ':ListoParaServir');
});

$app->group('/pedidos', function (RouteCollectorProxy $group) {
    $group->get('[/]', \PedidoController::class . ':TraerTodos')->add(new AuthMiddleware('socio'));
    $group->get('/tiempo', \PedidoController::class . ':ObtenerTiempo');
    $group->get('/{id_pedido}', \PedidoController::class . ':TraerUno');
    $group->post('[/]', \PedidoController::class . ':GuardarUno')->add(new AuthMiddleware('mozo'));
    $group->post('/entregar', \PedidoController::class . ':EntregarPedido')->add(new AuthMiddleware('mozo'));
    $group->post('/cobrar', \PedidoController::class . ':CobrarPedido')->add(new AuthMiddleware('mozo'));
    $group->post('/detalle', \PedidoController::class . ':ObtenerDesglosePedido');
    $group->put('[/]', \PedidoController::class . ':ModificarUno')->add(new AuthMiddleware('mozo'));
    $group->delete('[/]', \PedidoController::class . ':BorrarUno');
});

$app->group('/mesas', function (RouteCollectorProxy $group) {
    $group->get('/popular', \MesaController::class . ':TraerMasUsada')->add(new AuthMiddleware('socio'));
    $group->get('[/]', \MesaController::class . ':TraerTodos')->add(new AuthMiddleware('socio'));
    $group->get('/{id_mesa}', \MesaController::class . ':TraerUno')->add(new AuthMiddleware('socio'));
    $group->post('[/]', \MesaController::class . ':GuardarUno')->add(new AuthMiddleware('socio'));
    $group->post('/cerrar', \MesaController::class . ':CerrarMesa')->add(new AuthMiddleware('socio'));
    $group->put('[/]', \MesaController::class . ':ModificarUno')->add(new AuthMiddleware('mozo'));
    $group->delete('[/]', \MesaController::class . ':BorrarUno');
});

$app->group('/encuestas', function (RouteCollectorProxy $group) {
    $group->get('/comentarios', \EncuestaController::class . ':TraerMejoresComentarios')->add(new AuthMiddleware('socio'));
    $group->get('[/]', \EncuestaController::class . ':ObtenerEncuestas');
    $group->get('/{id}', \EncuestaController::class . ':ObtenerUnaEncuesta');
    $group->post('[/]', \EncuestaController::class . ':AltaEncuesta');
});

$app->group('/estadisticas', function (RouteCollectorProxy $group) {
    $group->get('/promedio', \PedidoController::class . ':PromedioIngresos30Dias');
});

$app->group('/csv', function (RouteCollectorProxy $group) {
    $group->get('/guardar/empleados', \EmpleadoController::class . ':GuardarEmpleados');
    $group->get('/cargar/empleados', \EmpleadoController::class . ':CargarEmpleados');
    $group->get('/guardar/productos', \ProductoController::class . ':GuardarProductos');
    $group->get('/cargar/productos', \ProductoController::class . ':CargarProductos');
});

$app->run();
