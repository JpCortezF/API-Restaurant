<?php
date_default_timezone_set('America/Argentina/Buenos_Aires');
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
require './Middlewares/AuthEmpleados.php';
require './Middlewares/AuthProductos.php';
require './Middlewares/AuthMiddleware.php';

require './Middlewares/LoggerMW.php';
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
    $group->post('/registro', \LoginController::class . ':Registro')->add(\AuthEmpleados::class . ':ValidarCampos');
    $group->post('/login', \LoginController::class . ':Login');
});

$app->group('/empleados', function (RouteCollectorProxy $group) {
    $group->get('[/]', \EmpleadoController::class . ':TraerTodos');
    $group->get('/{usuario}', \EmpleadoController::class . ':TraerUno');
    $group->post('[/]', \EmpleadoController::class . ':GuardarUno')->add(\AuthEmpleados::class . ':ValidarCampos')->add(new AuthMiddleware('5'));
    $group->put('[/]', \EmpleadoController::class . ':ModificarUno')->add(\AuthEmpleados::class . ':ValidarCampos')->add(new AuthMiddleware('5'));
    $group->delete('[/]', \EmpleadoController::class . ':BorrarUno')->add(new AuthMiddleware('5'));
})->add(new LoggerMW());

$app->group('/productos', function (RouteCollectorProxy $group) {
    $group->get('[/]', \ProductoController::class . ':TraerTodos');
    $group->get('/{id_producto}', \ProductoController::class . ':TraerUno');
    $group->post('[/]', \ProductoController::class . ':GuardarUno')->add(new AuthMiddleware('5'));
    $group->put('[/]', \ProductoController::class . ':ModificarUno')->add(\AuthProductos::class . ':ValidarRol')->add(new LoggerMW());
    $group->delete('[/]', \ProductoController::class . ':BorrarUno')->add(\AuthProductos::class . ':ValidarRol')->add(new LoggerMW());
});

$app->group('/productopedidos', function (RouteCollectorProxy $group) {
    $group->get('[/]', \ProductoPedidoController::class . ':ObtenerProductoPedidos');
    $group->get('/listar', \ProductoPedidoController::class . ':ListadoProductosPorSector')->add(new AuthMiddleware(['2', '3', '4']));
    $group->get('/sector', \ProductoPedidoController::class . ':TraerSectorProducto')->add(new AuthMiddleware(['2', '3', '4']));
    $group->get('/vendido', \ProductoPedidoController::class . ':ProductoMasVendido')->add(new AuthMiddleware('5'));
    $group->get('/mas-vendido', \ProductoPedidoController::class . ':ProductoMasVendido')->add(new AuthMiddleware('5'));
    $group->get('/menos-vendido', \ProductoPedidoController::class . ':ProductoMenosVendido')->add(new AuthMiddleware('5'));
    $group->post('/realizar', \ProductoPedidoController::class . ':EmpleadoTomaProducto')->add(\AuthProductos::class . ':ValidarRol')->add(new LoggerMW());
    $group->post('/servir', \ProductoPedidoController::class . ':ListoParaServir')->add(\AuthProductos::class . ':ValidarRol')->add(new LoggerMW());
});

$app->group('/pedidos', function (RouteCollectorProxy $group) {
    $group->group('', function (RouteCollectorProxy $group) {
        $group->get('[/]', \PedidoController::class . ':TraerTodos');
        $group->get('/tiempo', \PedidoController::class . ':ObtenerTiempo');
        $group->post('/detalle', \PedidoController::class . ':ObtenerDesglosePedido');
        $group->delete('[/]', \PedidoController::class . ':BorrarUno');
    })->add(new AuthMiddleware('5'));

    $group->group('', function (RouteCollectorProxy $group) {
        $group->post('[/]', \PedidoController::class . ':GuardarUno');
        $group->post('/foto', \PedidoController::class . ':FotoMesa');
        $group->post('/cobrar', \PedidoController::class . ':CobrarPedido');
        $group->put('/entregar', \PedidoController::class . ':EntregarPedido');
        $group->put('[/]', \PedidoController::class . ':ModificarUno');
    })->add(new AuthMiddleware('1'));

    $group->get('/tiempoCliente', \PedidoController::class . ':ClienteObtieneTiempo');
    $group->get('/{id_pedido}', \PedidoController::class . ':TraerUno');
});

$app->group('/mesas', function (RouteCollectorProxy $group) {
    $group->group('', function (RouteCollectorProxy $group) {
        $group->get('/popular', \MesaController::class . ':TraerMasUsada');
        $group->get('/inpopular', \MesaController::class . ':TraerMenosUsada');
        $group->get('/mas-facturo', \MesaController::class . ':ObtenerMesaMasFacturo');
        $group->get('/menos-facturo', \MesaController::class . ':ObtenerMesaMenosFacturo');
        $group->get('[/]', \MesaController::class . ':TraerTodos');
        $group->get('/{id_mesa}', \MesaController::class . ':TraerUno');

        $group->post('[/]', \MesaController::class . ':GuardarUno');
        $group->post('/cerrar', \MesaController::class . ':CerrarMesa');
    })->add(new AuthMiddleware('5'));

    $group->put('/limpiar', \MesaController::class . ':LimpiarMesa')->add(new AuthMiddleware('1'));

    $group->put('[/]', \MesaController::class . ':ModificarUno');
    $group->delete('[/]', \MesaController::class . ':BorrarUno');
});

$app->group('/encuestas', function (RouteCollectorProxy $group) {
    $group->get('/mejores', \EncuestaController::class . ':TraerMejoresComentarios');
    $group->get('/peores', \EncuestaController::class . ':TraerPeoresComentarios');
    $group->get('[/]', \EncuestaController::class . ':ObtenerEncuestas');
    $group->get('/{id}', \EncuestaController::class . ':ObtenerUnaEncuesta');
    $group->post('[/]', \EncuestaController::class . ':AltaEncuesta');
})->add(new AuthMiddleware('5'));

$app->group('/estadisticas', function (RouteCollectorProxy $group) {
    $group->get('/promedio', \PedidoController::class . ':PromedioIngresos30Dias');
    $group->get('/fechas', \PedidoController::class . ':FacturaEntreDias');
});

$app->group('/archivos', function (RouteCollectorProxy $group) {
    $group->get('/pdf', \EmpleadoController::class . ':DescargarPDF');
    $group->group('/csv', function (RouteCollectorProxy $group) {
        $group->get('/guardar/empleados', \EmpleadoController::class . ':GuardarEmpleados');
        $group->get('/cargar/empleados', \EmpleadoController::class . ':CargarEmpleados');
        $group->get('/guardar/productos', \ProductoController::class . ':GuardarProductos');
        $group->get('/cargar/productos', \ProductoController::class . ':CargarProductos');
    });
});

$app->run();
