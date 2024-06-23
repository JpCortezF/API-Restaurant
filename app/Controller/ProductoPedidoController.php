<?php

require_once './Model/ProductoPedido.php';
require_once './Model/Producto.php';
require_once './Model/Pedido.php';

class ProductoPedidoController
{
    public function AltaProductoPedido($request, $response, $args)
    {
        $parametros = $request->getParsedBody();
        $id_producto = $parametros['id_producto'];
        $id_pedido =  $parametros['id_pedido'];
        $cantidad =  $parametros['cantidad'];
        $tiempo_estimado =  $parametros['tiempo_estimado'];
        $producto = Producto::ObtenerProducto($id_producto);
        $pedido = Pedido::TraerPedido($id_pedido);
        if ($pedido && $producto) {
            if ($pedido->estado_pedido == 'Pendiente') {
                $productoPedido = new ProductoPedido();
                $productoPedido->id_pedido = $id_pedido;
                $productoPedido->id_producto = $id_producto;
                $productoPedido->cantidad = $cantidad;
                $productoPedido->estado = "Pendiente";
                $productoPedido->tiempo_estimado = $tiempo_estimado;

                // Pedido::ObtenerPrecioFinal($id_pedido, $producto->precio);

                $productoPedido->CrearProductoPedido();
                $payload = json_encode(array("mensaje" => "ProductoPedido creado con exito."));
            } else {
                $payload = json_encode(array("error" => "El numero de pedido no se encuentra 'Pendiente'."));
            }
        } else {
            $payload = json_encode(array("error" => "No se pudo crear el ProductoPedido."));
        }

        $response->getBody()->write($payload);
        return $response->withHeader('Content-Type', 'application/json');
    }

    public function ObtenerProductoPedidos($request, $response, $args)
    {
        $lista = ProductoPedido::TraerProductoPedidos();

        $payload = json_encode($lista);

        $response->getBody()->write($payload);
        return $response
            ->withHeader('Content-Type', 'application/json');
    }

    public static function ListadoProductosPorSector($request, $response, $args)
    {
        $parametros = $request->getQueryParams();
        $id_empleado = $parametros['id_empleado'];
        $empleado = Empleado::EmpleadoPorID($id_empleado);

        if ($empleado) {
            $id_producto = ProductoPedido::PedidoProductoPorIdEmpleado($id_empleado);
            if ($id_producto) {
                $id_sector = Producto::ObtenerSectorPorIdProducto($id_producto);
                if ($id_sector) {
                    $sectorEmpleado = $empleado->id_rol;

                    $sectorMapping = [
                        2 => [1],           // Bartender => Barra de tragos y vinos
                        3 => [2],           // Cervecero => Barra de choperas
                        4 => [3, 4],        // Cocinero => Cocina, Candy Bar
                    ];

                    if (isset($sectorMapping[$sectorEmpleado])) {
                        $sectores = $sectorMapping[$sectorEmpleado];
                        $pedidos = Pedido::ObtenerPedidosPorSectorYPendiente($sectores);

                        $payload = json_encode(array("pedidos" => $pedidos));
                    } else {
                        $payload = json_encode(array("error" => "El rol del empleado no tiene sectores asignados."));
                    }
                } else {
                    $payload = json_encode(array("error" => "No se pudo determinar el sector del producto."));
                }
            } else {
                $payload = json_encode(array("error" => "No se pudo encontrar un producto asociado al empleado."));
            }
        } else {
            $payload = json_encode(array("error" => "No se pudo encontrar el empleado."));
        }

        $response->getBody()->write($payload);
        return $response->withHeader('Content-Type', 'application/json');
    }

    public function TraerSectorProducto($request, $response, $args)
    {
        $parametros = $request->getQueryParams();
        $id = $parametros['id'];
        $productoPedido = ProductoPedido::TraerPorId($id);
        if ($productoPedido) {
            $sector = Producto::ObtenerSectorProducto($productoPedido->id_producto);
            if ($sector) {
                $payload = json_encode(array('Sector' => $sector));
            } else {
                $payload = json_encode(array('error' => 'No se pudo encontrar el sector del producto.'));
            }
        } else {
            $payload = json_encode(array("error" => "No se pudo encontrar productos pendientes."));
        }

        $response->getBody()->write($payload);
        return $response->withHeader('Content-Type', 'application/json');
    }

    public function EmpleadoTomaProducto($request, $response, $args)
    {
        $params = $request->getParsedBody();

        $id = $params['id'];
        $estado = $params['estado'];
        $tiempo_estimado = $params['tiempo_estimado'];
        $id_empleado = $params['id_empleado'];

        if (ProductoPedido::ActualizarEstadoYTiempo($id, $estado, $tiempo_estimado)) {
            ProductoPedido::SetEmpleadoProducto($id, $id_empleado);
            $productopedido = ProductoPedido::TraerPorId($id);
            if ($productopedido === false) {
                $payload = json_encode(array("error" => "No se pudo encontrar el producto pedido."));
            } else {
                $payload = json_encode(array("message" => "Producto pedido actualizado.", "productopedido" => $productopedido));

                Pedido::ActualizarEstadoPedido($productopedido->id_pedido, 'En Preparacion');

                $payload = json_encode(array("mensaje" => "Se han modificado el Estado y el Tiempo Estimado del Producto"));
            }
        } else {
            $payload = json_encode(array("mensaje" => "No se modifico nada"));
        }

        $response->getBody()->write($payload);

        return $response->withHeader('Content-Type', 'application/json');
    }

    public function ListoParaServir($request, $response, $args)
    {
        $params = $request->getParsedBody();
        $id_pedido = $params['id_pedido'];

        $pedido = Pedido::TraerPedido($id_pedido);
        if ($pedido) {
            if (ProductoPedido::ActualizarEstadoPorPedido($id_pedido, 'Listo para servir')) {

                Pedido::ActualizarEstadoPedido($id_pedido, 'Listo para servir');
                $payload = json_encode(array("mensaje" => "Todos los productos del pedido están listos para servir"));
            } else {
                $payload = json_encode(array("error" => "No se pudo actualizar el estado de los productos del pedido"));
            }
        } else {
            $payload = json_encode(array("error" => "No se encontró el pedido"));
        }

        $response->getBody()->write($payload);
        return $response->withHeader('Content-Type', 'application/json');
    }
}
