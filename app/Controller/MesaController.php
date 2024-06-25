<?php
require_once './Model/Mesa.php';

class MesaController implements IApiUsable
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

    public function BorrarUno($request, $response, $args)
    {
        $parametros = $request->getParsedBody();
        $id_mesa = $parametros['id_mesa'];
        $mesa = Mesa::TraerMesa($id_mesa);
        if ($mesa) {
            if (Mesa::EliminarMesa($id_mesa)) {
                $payload = json_encode(array("mensaje" => "Mesa eliminado con exito"));
            }
        } else {
            $payload = json_encode(array("mensaje" => "Error en eliminar Mesa"));
        }
        $response->getBody()->write($payload);
        return $response->withHeader('Content-Type', 'application/json');
    }

    public function ModificarUno($request, $response, $args)
    {
        $parametros = $request->getParsedBody();
        $id_mesa = $parametros['id_mesa'];
        $mesa = Mesa::TraerMesa($id_mesa);
        if ($mesa) {
            if (isset($parametros['estado'])) {
                $mesa->estado = $parametros['estado'];
                Mesa::ActualizarEstadoMesa($id_mesa, $mesa->estado);
                $payload = json_encode(array("mensaje" => "Mesa modificado con exito"));
            } else {
                $payload = json_encode(array("mensaje" => "Parametros insuficientes"));
            }
        } else {
            $payload = json_encode(array("mensaje" => "Error en modificar Mesa"));
        }
        $response->getBody()->write($payload);
        return $response->withHeader('Content-Type', 'application/json');
    }

    public static function CambiarEstadoMesaPorPedido($id_pedido)
    {
        $pedido = Pedido::TraerPedido($id_pedido);
        $mesa = Mesa::TraerMesa($pedido->id_mesa);
        $estadoMesa = "con cliente esperando pedido";
        switch ($pedido->estado) {
            case "Entregado":
                $estadoMesa = "con cliente comiendo";
                Mesa::ActualizarEstadoMesa($mesa->id_mesa, $estadoMesa);
                break;
            default:
                Mesa::ActualizarEstadoMesa($mesa->id_mesa, $estadoMesa);
                break;
        }
    }

    public function CerrarMesa($request, $response, $args)
    {
        $parametros = $request->getParsedBody();
        $id_mesa = $parametros['id_mesa'];
        $mesa = Mesa::TraerMesa($id_mesa);
        if ($mesa) {
            // $mesa->estado = "Sin clientes";
            Mesa::ActualizarEstadoMesa($id_mesa, "Sin clientes");
            $payload = json_encode(array("mensaje" => "Mesa cerrada con exito"));
        } else {
            $payload = json_encode(array("mensaje" => "Error en cerrar Mesa"));
        }
        $response->getBody()->write($payload);
        return $response->withHeader('Content-Type', 'application/json');
    }

    public static function TraerMasUsada($request, $response, $args)
    {
        $resultado = Mesa::TraerMasUsada();
        if ($resultado == false) {
            $payload = json_encode(array("mensaje" => "No se encontró la mesa"));
        } else {
            $payload = json_encode(array("mesa" => $resultado['id_mesa'], "cantidad" => $resultado['cantidad']));
        }

        $response->getBody()->write($payload);
        return $response->withHeader('Content-Type', 'application/json');
    }
}
