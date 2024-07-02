<?php

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\RequestHandlerInterface;
use Slim\Psr7\Response as ResponseMw;
use Psr\Http\Server\RequestHandlerInterface as RequestHandler;

class AuthMiddleware
{
    private $perfilesPermitidos;

    public function __construct($perfiles)
    {
        $this->perfilesPermitidos = is_array($perfiles) ? $perfiles : [$perfiles];
    }

    public function __invoke(Request $request, RequestHandler $handler): Response
    {
        $header = $request->getHeader('Authorization');
        if (empty($header)) {
            return $this->RespuestaError('Token no proporcionado');
        }

        $token = str_replace('Bearer ', '', $header[0]);
        try {
            $datos = AutentificadorJWT::ObtenerData($token);
        } catch (Exception $e) {
            return $this->RespuestaError('Token inválido');
        }

        if (!in_array($datos->id_rol, $this->perfilesPermitidos)) {
            return $this->RespuestaError('Perfil no autorizado');
        }

        return $handler->handle($request);
    }

    private function RespuestaError($message): ResponseMw
    {
        $response = new ResponseMw();
        $response->getBody()->write(json_encode(['error' => $message]));
        return $response->withHeader('Content-Type', 'application/json')->withStatus(401);
    }
}
