<?php

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\RequestHandlerInterface;
use Slim\Psr7\Response as ResponseMw;
use Psr\Http\Server\RequestHandlerInterface as RequestHandler;

class AuthMiddleware
{
    private $rol; // paramestros para hacer mas reutilizable

    public function __construct($rol)
    {
        $this->rol = $rol;
    }
    public function __invoke(Request $request, RequestHandler $requestHandler)
    {
        $response = new ResponseMw();
        $params = $request->getQueryParams();

        if (isset($params["credencial"])) {
            $credenciales = $params["credencial"];
            if ($credenciales === $this->rol) {
                $response = $requestHandler->handle($request);
            } else {
                $response->getBody()->write(json_encode(array("error" => "No sos " . $this->rol)));
            }
        } else {
            $response->getBody()->write(json_encode(array("error" => "No envio credenciales")));
        }

        return $response;
    }
}
