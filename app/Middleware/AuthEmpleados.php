<?php

use Slim\Psr7\Response as ResponseClass;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\RequestHandlerInterface as RequestHandler;

class AuthEmpleados
{
    public static function ValidarCampos(Request $request, RequestHandler $requestHandler)
    {
        $params = $request->getParsedBody();

        if ((isset($params['nombre'], $params['clave'], $params['id_rol'])) &&
            !empty($params['nombre']) && !empty($params['clave']) && !empty($params['id_rol'])
        ) {
            $response = $requestHandler->handle($request);
        } else {
            $response = new ResponseClass();
            $response->getBody()->write(json_encode(array("error" => "Parametros incorrectos")));
            return $response->withHeader('Content-Type', 'application/json');
        }
        return $response;
    }
}
