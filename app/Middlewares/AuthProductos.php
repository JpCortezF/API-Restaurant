<?php

use Slim\Psr7\Response as ResponseClass;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\RequestHandlerInterface as RequestHandler;

class AuthProductos
{
    public static function ValidarRol(Request $request, RequestHandler $handler)
    {
        $header = $request->getHeader('Authorization');
        if (empty($header)) {
            return self::RespuestaError('Token no proporcionado');
        }

        $token = str_replace('Bearer ', '', $header[0]);
        try {
            $datos = AutentificadorJWT::ObtenerData($token);
        } catch (Exception $e) {
            return self::RespuestaError('Token inválido');
        }

        $params = $request->getParsedBody();
        $id = $params['id'];
        $productopedido = ProductoPedido::TraerPorId($id);
        $producto = Producto::ObtenerProducto($productopedido->id_producto);

        if ($producto && self::EsEmpleadoAutorizado($datos, $producto)) {
            $response = $handler->handle($request);
        } else {
            return self::RespuestaError('Empleado no autorizado para modificar/borrar este producto');
        }


        return $response;
    }

    private static function EsEmpleadoAutorizado($datos, $producto)
    {
        $sectorProducto = $producto->id_sector;
        $sectorEmpleado = $datos->id_rol;

        $sectoresPermitidos = [
            1 => [2], // Barra de tragos y vinos => Bartender
            2 => [3], // Barra de choperas => Cervecero
            3 => [4], // Cocina => Cocinero
            4 => [4], // Candy Bar => Cocinero
        ];

        return isset($sectoresPermitidos[$sectorProducto]) && in_array($sectorEmpleado, $sectoresPermitidos[$sectorProducto]);
    }

    private static function RespuestaError($message): ResponseClass
    {
        $response = new ResponseClass();
        $response->getBody()->write(json_encode(['error' => $message]));
        return $response->withHeader('Content-Type', 'application/json')->withStatus(401);
    }
}
