<?php
require './Model/Pedido.php';

class PedidoController
{

    public function GuardarUno($request, $response, $args)
    {
        $parametros = $request->getParsedBody();

        $pedido = new Pedido();
        $pedido->codigo = $parametros['codigo'];
        $pedido->nombre_cliente = $parametros['nombre_cliente'];
        $pedido->id_mesa = $parametros['id_mesa'];
        $pedido->tiempo_estimado = $parametros['tiempo_estimado'];
        $pedido->NuevoPedido();

        $payload = json_encode(array("mensaje" => "Pedido creado con exito"));

        $response->getBody()->write($payload);
        return $response->withHeader('Content-Type', 'application/json');
    }

    public function TraerTodos($request, $response, $args)
    {
        $lista = Pedido::TraerPedidos();
        $payload = json_encode(array("listaPedidos" => $lista));

        $response->getBody()->write($payload);
        return $response->withHeader('Content-Type', 'application/json');
    }

    public function TraerUno($request, $response, $args)
    {
        try {
            $id_pedido = $args['id_pedido'];
            $pedido = Pedido::TraerPedido($id_pedido);
            $payload = json_encode($pedido);

            $response->getBody()->write($payload);
            return $response->withHeader('Content-Type', 'application/json');
        } catch (Exception $e) {
            return $response->withStatus(500)->write('Error interno del servidor');
        }
    }
}
