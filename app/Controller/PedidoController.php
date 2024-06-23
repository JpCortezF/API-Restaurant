<?php
require_once './Model/Pedido.php';
require_once './Model/Mesa.php';
require_once './Model/Producto.php';
require_once './Model/ProductoPedido.php';
require_once './Model/Empleado.php';

class PedidoController implements IApiUsable
{

    public function GuardarUno($request, $response, $args)
    {
        $parametros = $request->getParsedBody();

        $pedido = new Pedido();
        $pedido->id_mozo = $parametros['id_mozo'];
        $pedido->codigo = Pedido::CodigoAlphaNumerico();
        $pedido->nombre_cliente = $parametros['nombre_cliente'];
        $pedido->id_mesa = $parametros['id_mesa'];
        $pedido->estado = 'Pendiente';
        $pedido->tiempo_estimado = $parametros['tiempo_estimado'];
        if (Mesa::TraerMesa($pedido->id_mesa)) {
            if (Empleado::EsMozo($pedido->id_mozo)) {

                if (isset($_FILES['foto_mesa']) && $_FILES['foto_mesa'] != null) {
                    $foto_mesa = Pedido::GuardarImagenPedido("./images/", $_FILES['foto_mesa'], $pedido->id_mesa, $pedido->nombre_cliente);
                } else {
                    $foto_mesa = "-";
                }
                $pedido->foto_mesa = $foto_mesa;

                $pedido->NuevoPedido();
                Mesa::ActualizarEstadoMesa($pedido->id_mesa, 'Con cliente esperando pedido');
                $payload = json_encode(array("mensaje" => "Pedido creado con exito"));
            } else {
                $payload = json_encode(array("error" => "El empleado que toma el pedido no es Mozo del local"));
            }
        } else {
            $payload = json_encode(array("error" => "No se pudo crear el pedido"));
        }

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

    public function BorrarUno($request, $response, $args)
    {
        $parametros = $request->getParsedBody();
        $id_pedido = $parametros['id_pedido'];
        $pedido = Pedido::TraerPedido($id_pedido);
        if ($pedido) {
            if (Pedido::EliminarPedido($id_pedido)) {
                $payload = json_encode(array("mensaje" => "Pedido eliminado con exito"));
            }
        } else {
            $payload = json_encode(array("mensaje" => "Error al eliminar Pedido"));
        }
        $response->getBody()->write($payload);
        return $response->withHeader('Content-Type', 'application/json');
    }

    public function ModificarUno($request, $response, $args)
    {
        $parametros = $request->getParsedBody();
        $id_pedido = $parametros['id_pedido'];
        $pedido = Pedido::TraerPedido($id_pedido);
        if ($pedido) {
            if (isset($parametros['estado'])) {
                $pedido->estado = $parametros['estado'];
                Pedido::ActualizarEstadoPedido($id_pedido, $pedido->estado);
                $payload = json_encode(array("mensaje" => "Pedido modificado con exito"));
            } else {
                $payload = json_encode(array("mensaje" => "Parametros insuficientes"));
            }
        } else {
            $payload = json_encode(array("mensaje" => "Error al modificar Pedido"));
        }
        $response->getBody()->write($payload);
        return $response->withHeader('Content-Type', 'application/json');
    }

    public function ObtenerTiempo($request, $response, $args)
    {
        $parametros = $request->getQueryParams();
        $id_pedido = $parametros['id_pedido'];
        $pedido = Pedido::TraerPedido($id_pedido);
        if ($pedido) {
            $tiempo_estimado = $pedido['tiempo_estimado'];
            $payload = json_encode(array("mensaje" => "El tiempo estimado del pedido es de $tiempo_estimado minutos"));
        } else {
            $payload = json_encode(array("mensaje" => "No se encontró la mesa"));
        }
        $response->getBody()->write($payload);
        return $response->withHeader('Content-Type', 'application/json');
    }

    public function EntregarPedido($request, $response, $args)
    {
        $params = $request->getParsedBody();
        $id_pedido = $params['id_pedido'];

        $pedido = Pedido::TraerPedido($id_pedido);
        if ($pedido) {
            if (ProductoPedido::ActualizarEstadoPorPedido($id_pedido, 'Entregado')) {

                Pedido::ActualizarEstadoPedido($id_pedido, 'Entregado');
                Mesa::ActualizarEstadoMesa($pedido->id_mesa, 'Con cliente comiendo');

                $payload = json_encode(array("mensaje" => "Todos los productos del pedido están listos para servir"));
            } else {
                $payload = json_encode(array("error" => "No se pudo actualizar el estado de los productos del pedido"));
            }
            $payload = json_encode(array("mensaje" => "Se ha entregado el pedido correctamente"));
        }

        $response->getBody()->write($payload);

        return $response->withHeader('Content-Type', 'application/json');
    }
    public function CobrarPedido($request, $response, $args)
    {
        $params = $request->getParsedBody();

        $id_pedido = $params['id_pedido'];

        $pedido = Pedido::TraerPedido($id_pedido);
        if ($pedido) {
            Mesa::ActualizarEstadoMesa($pedido->id_mesa, 'Con cliente pagando');
            ProductoPedido::ActualizarEstadoPorPedido($id_pedido, 'Finalizado');
            Pedido::ActualizarEstadoPedido($id_pedido, 'Finalizado');
            $total = Pedido::ObtenerPrecioFinal($id_pedido);
            $payload = json_encode(array("cuenta" => 'El total a pagar es ' . $total));
        } else {
            $payload = json_encode(array("error" => "No se encontró la mesa"));
        }

        $response->getBody()->write($payload);

        return $response->withHeader('Content-Type', 'application/json');
    }

    public function ObtenerDesglosePedido($request, $response, $args)
    {
        $params = $request->getParsedBody();
        $id_pedido = $params['id_pedido'];

        $pedido = Pedido::TraerPedido($id_pedido);
        if ($pedido) {
            $detalles = Pedido::ObtenerDesglosePedido($id_pedido);
            $total = 0;
            $detalleArray = array();

            foreach ($detalles as $detalle) {
                $detalleArray[] = array(
                    "nombre" => $detalle->descripcion,
                    "cantidad" => $detalle->cantidad,
                    "precio" => $detalle->precio,
                );
                $total += $detalle->total_producto;
            }

            $payload = json_encode(array(
                "detalle" => $detalleArray,
                "total" => $total
            ));
        } else {
            $payload = json_encode(array("error" => "No se encontró el pedido"));
        }

        $response->getBody()->write($payload);
        return $response->withHeader('Content-Type', 'application/json');
    }
}
