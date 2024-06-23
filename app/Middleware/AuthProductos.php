<?php

use Slim\Psr7\Response as ResponseClass;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\RequestHandlerInterface as RequestHandler;

class AuthProductos
{
    public static function ValidarRol(Request $request, RequestHandler $requestHandler)
    {
        $params = $request->getParsedBody();
        $id_empleado = $params['id_empleado'];
        $id_producto = $params['id_producto'];

        $empleado = Empleado::EmpleadoPorID($id_empleado);
        $producto = Producto::ObtenerProducto($id_producto);

        if ($empleado && $producto && self::EsEmpleadoAutorizado($empleado, $producto)) {
            $response = $requestHandler->handle($request);
        } else {
            $response = new ResponseClass();
            $response->getBody()->write(json_encode(array("error" => "Empleado no autorizado para modificar/borrar este producto")));
            return $response->withHeader('Content-Type', 'application/json');
        }
        return $response;
    }

    private static function EsEmpleadoAutorizado($empleado, $producto)
    {
        $sectorProducto = $producto->id_sector;
        $sectorEmpleado = $empleado->id_rol;

        $sectoresPermitidos = [
            1 => [2],           // Barra de tragos y vinos => Bartender
            2 => [3],           // Barra de choperas => Cervecero
            3 => [4],           // Cocina => Cocinero
            4 => [4],           // Candy Bar => Cocinero
        ];

        return isset($sectoresPermitidos[$sectorProducto]) && in_array($sectorEmpleado, $sectoresPermitidos[$sectorProducto]);
    }
}
