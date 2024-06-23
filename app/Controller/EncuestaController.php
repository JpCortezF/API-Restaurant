<?php
require_once './Model/Encuesta.php';

class EncuestaController
{
    public function AltaEncuesta($request, $response, $args)
    {
        $p = $request->getParsedBody();

        if (isset(
            $p['id_mesa'],
            $p['id_pedido'],
            $p['nombre_cliente'],
            $p['puntuacion_mesa'],
            $p['puntuacion_mozo'],
            $p['puntuacion_cocinero'],
            $p['puntuacion_restaurante'],
            $p['comentario']
        )) {
            $encuesta = new Encuesta();
            $encuesta->id_mesa = $p['id_mesa'];
            $encuesta->id_pedido = $p['id_pedido'];
            $encuesta->nombre_cliente = $p['nombre_cliente'];
            $encuesta->puntuacion_mesa = $p['puntuacion_mesa'];
            $encuesta->puntuacion_mozo = $p['puntuacion_mozo'];
            $encuesta->puntuacion_cocinero = $p['puntuacion_cocinero'];
            $encuesta->puntuacion_restaurant = $p['puntuacion_restaurante'];
            $encuesta->comentario = $p['comentario'];

            Encuesta::CrearEncuesta($encuesta);
            $payload = json_encode(array("mensaje" => "Encuesta creado con exito."));
        } else {
            $payload = json_encode(array("error" => "No se pudo crear la encuesta."));
        }

        $response->getBody()->write($payload);
        return $response
            ->withHeader('Content-Type', 'application/json');
    }

    public function ObtenerEncuestas($request, $response, $args)
    {
        $lista = Encuesta::TraerEncuestas();

        $payload = json_encode($lista);

        $response->getBody()->write($payload);
        return $response
            ->withHeader('Content-Type', 'application/json');
    }

    public function ObtenerUnaEncuesta($request, $response, $args)
    {
        $parametros = $request->getParsedBody();
        $id = $parametros['id'];
        $encuesta = Encuesta::TraerPorId($id);
        $payload = json_encode($encuesta);

        $response->getBody()->write($payload);
        return $response
            ->withHeader('Content-Type', 'application/json');
    }

    public function TraerMejoresComentarios($request, $response, $args)
    {
        $lista = Encuesta::TraerMejoresComentarios();

        $payload = json_encode($lista);

        $response->getBody()->write($payload);
        return $response
            ->withHeader('Content-Type', 'application/json');
    }
}
