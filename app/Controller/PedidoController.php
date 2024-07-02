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
        $productos = $parametros['productos'];

        $mesa = Mesa::TraerMesa($pedido->id_mesa);
        if ($mesa && $mesa['estado'] == 'Sin clientes') {
            if (Empleado::EsMozo($pedido->id_mozo)) {

                if (isset($_FILES['foto_mesa']) && $_FILES['foto_mesa'] != null) {
                    $foto_mesa = Pedido::GuardarImagenPedido("./images/", $_FILES['foto_mesa'], $pedido->id_mesa, $pedido->nombre_cliente);
                    $foto_mesa = str_replace('./images/', '', $foto_mesa);
                } else {
                    $foto_mesa = "-";
                }
                $pedido->foto_mesa = $foto_mesa;

                $pedido_id = $pedido->NuevoPedido();
                Mesa::ActualizarEstadoMesa($pedido->id_mesa, 'Con cliente esperando pedido');

                foreach ($productos as $prod) {
                    $productoPedido = new ProductoPedido();
                    $productoPedido->id_pedido = $pedido_id;
                    $productoPedido->id_producto = $prod['id_producto'];
                    $productoPedido->cantidad = $prod['cantidad'];
                    $productoPedido->estado = 'Pendiente';
                    $productoPedido->tiempo_estimado = $prod['tiempo_estimado'];
                    $productoPedido->CrearProductoPedido();
                }

                $tiempo_pedido = ProductoPedido::ObtenerTiempo($pedido_id);
                Pedido::ActualizarEstadoYTiempo($pedido_id, $tiempo_pedido);

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

    public function FotoMesa($request, $response, $args)
    {
        $parametros = $request->getParsedBody();
        $id_pedido = $parametros['id_pedido'];

        if (isset($_FILES['foto_mesa']) && $_FILES['foto_mesa'] != null) {
            $pedido = Pedido::TraerPedido($id_pedido);
            if ($pedido) {
                $foto_mesa = Pedido::GuardarImagenPedido("./images/", $_FILES['foto_mesa'], $pedido->id_mesa, $pedido->nombre_cliente);
                $foto_mesa = str_replace('./images/', '', $foto_mesa);
                Pedido::AgregarFotoPedido($id_pedido, $foto_mesa);
                $payload = json_encode(array("mensaje" => "Foto agregada con exito"));
            } else {
                $payload = json_encode(array("error" => "No se encontro el pedido"));
            }
        } else {
            $foto_mesa = "-";
            $payload = json_encode(array("error" => "No se agrego foto al pedido"));
        }
        $pedido->foto_mesa = $foto_mesa;

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
            $tiempo_estimado = $pedido->tiempo_estimado;
            $payload = json_encode(array("mensaje" => "El tiempo estimado del pedido es de $tiempo_estimado minutos"));
        } else {
            $payload = json_encode(array("mensaje" => "No se encontro la mesa"));
        }
        $response->getBody()->write($payload);
        return $response->withHeader('Content-Type', 'application/json');
    }

    public function ClienteObtieneTiempo($request, $response, $args)
    {
        $parametros = $request->getQueryParams();
        $id_pedido = $parametros['id_pedido'];
        $codigo_mesa = $parametros['codigo_mesa'];
        $pedido = Pedido::TraerPedido($id_pedido);
        if ($pedido && $pedido->id_pedido == $id_pedido) {
            if ($pedido->codigo_mesa == $codigo_mesa) {
                $payload = json_encode(array("mensaje" => "El tiempo estimado del pedido es de $pedido->tiempo_estimado minutos"));
            } else {
                $payload = json_encode(array("mensaje" => "Codigo de mesa incorrecto"));
            }
        } else {
            $payload = json_encode(array("mensaje" => "No se encontro el pedido"));
        }

        $response->getBody()->write($payload);
        return $response->withHeader('Content-Type', 'application/json');
    }

    public function EntregarPedido($request, $response, $args)
    {
        $params = $request->getParsedBody();
        $id_pedido = $params['id_pedido'];
        $pedido = Pedido::ObtenerListosParaServir($id_pedido, 'Listo para servir');
        if ($pedido) {
            if (ProductoPedido::ActualizarEstadoPorPedido($pedido->id_pedido, 'Entregado')) {
                Pedido::ActualizarEstadoPedido($pedido->id_pedido, 'Entregado');
                Mesa::ActualizarEstadoMesa($pedido->id_mesa, 'Con cliente comiendo');

                $payload = json_encode(array("mensaje" => "Pedido entregado. Estado de la Mesa actualizada"));
            } else {
                $payload = json_encode(array("error" => "No se pudo actualizar el pedido, ni el estado de la Mesa"));
            }
        } else {
            $payload = json_encode(array("error" => "No se encontró un pedido en estado 'Listo para servir'."));
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

    public function PromedioIngresos30Dias($request, $response, $args)
    {
        $fecha_actual = date("Y-m-d H:i:s");
        $fecha_actualObj = new DateTime($fecha_actual);
        $fecha_limite = $fecha_actualObj->modify('-30 days');
        $pedidos = Pedido::ObtenerPedidoSegunEstado("Finalizado");
        $acumulador = 0;
        foreach ($pedidos as $pedido) {
            $fecha_cierre = new DateTime($pedido->fecha_cierre);
            if ($fecha_cierre >= $fecha_limite) {
                $acumulador +=  Pedido::ObtenerPrecioFinal($pedido->id_pedido);
            }
        }
        $promedio = $acumulador / 30;

        $payload = json_encode(array("mensaje" => "El importe promedio en los ultimos 30 dias fue de: " . $promedio));
        $response->getBody()->write($payload);
        return $response->withHeader('Content-Type', 'application/json');
    }

    public function FacturaEntreDias($request, $response, $args)
    {
        $params = $request->getQueryParams();
        $fecha_desde = $params['fecha_desde'];
        $fecha_hasta = $params['fecha_hasta'];

        $pedidos = Pedido::PedidosEntreFechas($fecha_desde, $fecha_hasta);
        var_dump(count($pedidos));
        $facturado = 0;
        foreach ($pedidos as $pedido) {
            $facturado += Pedido::ObtenerPrecioFinal($pedido->id_pedido);
        }

        $payload = json_encode(array("mensaje" => "Lo facturado entre las dos fechas es: " . $facturado));
        $response->getBody()->write($payload);
        return $response->withHeader('Content-Type', 'application/json');
    }
}
