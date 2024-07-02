<?php

use Slim\Psr7\Response as ResponseClass;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\RequestHandlerInterface as RequestHandler;

class AuthEmpleados
{
    public static function ValidarCampos(Request $request, RequestHandler $requestHandler)
    {
        $params = $request->getParsedBody();

        if ((isset($params['nombre'], $params['usuario'], $params['clave'], $params['id_rol'])) &&
            !empty($params['nombre']) && !empty($params['usuario']) && !empty($params['clave']) && !empty($params['id_rol'])
        ) {
            $response = $requestHandler->handle($request);
        } else {
            $response = new ResponseClass();
            $response->getBody()->write(json_encode(array("error" => "Parametros incorrectos")));
            return $response->withHeader('Content-Type', 'application/json');
        }
        return $response;
    }
    public static function ObtenerIdEmpleadoDelToken($request)
    {
        $header = $request->getHeader('Authorization');
        if (empty($header)) {
            throw new Exception('Token no proporcionado');
        }

        $token = str_replace('Bearer ', '', $header[0]);
        try {
            $datos = AutentificadorJWT::ObtenerData($token);
            return $datos->id_empleado;
        } catch (Exception $e) {
            throw new Exception('Token inválido');
        }
    }
}
