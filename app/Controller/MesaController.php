<?php
require './Model/Mesa.php';

class MesaController
{

    public function GuardarUno($request, $response, $args)
    {
        // $parametros = $request->getParsedBody();
        $mesa = new Mesa();
        $mesa->codigo = Mesa::CodigoAlphaNumerico();
        $mesa->NuevaMesa();

        $payload = json_encode(array("mensaje" => "Mesa creada con exito"));

        $response->getBody()->write($payload);
        return $response->withHeader('Content-Type', 'application/json');
    }

    public function TraerTodos($request, $response, $args)
    {
        $lista = Mesa::TraerMesas();
        $payload = json_encode(array("listaMesas" => $lista));

        $response->getBody()->write($payload);
        return $response->withHeader('Content-Type', 'application/json');
    }

    public function TraerUno($request, $response, $args)
    {
        try {
            $id_mesa = $args['id_mesa'];
            $mesa = Mesa::TraerMesa($id_mesa);
            $payload = json_encode($mesa);

            $response->getBody()->write($payload);
            return $response->withHeader('Content-Type', 'application/json');
        } catch (Exception $e) {
            return $response->withStatus(500)->write('Error interno del servidor');
        }
    }
}
