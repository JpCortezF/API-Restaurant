<?php

require_once './Model/ProductoPedido.php';
require_once './Model/Producto.php';
require_once './Model/Pedido.php';

class ProductoPedidoController
{
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
        try {
            $id_empleado = AuthEmpleados::ObtenerIdEmpleadoDelToken($request);
        } catch (Exception $e) {
            $payload = json_encode(array("error" => $e->getMessage()));
            $response->getBody()->write($payload);
            return $response->withHeader('Content-Type', 'application/json');
        }

        $empleado = Empleado::EmpleadoPorID($id_empleado);
        $id_rol = $empleado->id_rol;

        $sectorMapping = [
            2 => [1],        // Bartender => Barra de tragos y vinos
            3 => [2],        // Cervecero => Barra de choperas   
            4 => [3, 4],     // Cocinero => Cocina, Candy Bar
        ];

        if (isset($sectorMapping[$id_rol])) {
            $sectores = $sectorMapping[$id_rol];
            $productosPendientes = ProductoPedido::PedidoProductoPendientesPorSectores($sectores);

            $payload = json_encode(array("productosPendientes" => $productosPendientes));
        } else {
            $payload = json_encode(array("error" => "El rol del empleado no tiene sectores asignados."));
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

    public function ProductoMasVendido($request, $response, $args)
    {
        $mas_vendido = ProductoPedido::ProductoMasVendido();
        $payload = json_encode(array('Producto mas vendido' => $mas_vendido));

        $response->getBody()->write($payload);
        return $response->withHeader('Content-Type', 'application/json');
    }
    public function ProductoMenosVendido($request, $response, $args)
    {
        $menos_vendido = ProductoPedido::ProductoMenosVendido();
        $payload = json_encode(array('Producto menos vendido' => $menos_vendido));

        $response->getBody()->write($payload);
        return $response->withHeader('Content-Type', 'application/json');
    }

    public function EmpleadoTomaProducto($request, $response, $args)
    {
        $params = $request->getParsedBody();
        $id = $params['id'];
        $estado = $params['estado'];

        try {
            $id_empleado = AuthEmpleados::ObtenerIdEmpleadoDelToken($request);
        } catch (Exception $e) {
            $payload = json_encode(array("error" => $e->getMessage()));
            $response->getBody()->write($payload);
            return $response->withHeader('Content-Type', 'application/json');
        }

        $productopedido = ProductoPedido::TraerPorId($id);
        if ($productopedido === false) {
            $payload = json_encode(array("mensaje" => "No se pudo encontrar el producto pedido."));
        } else {
            if ($productopedido->estado == 'Pendiente') {
                $empleado_con_productos = ProductoPedido::EmpleadoConProductos($id_empleado, 'En Preparacion');
                if (count($empleado_con_productos) >= 3) {
                    $payload = json_encode(array("mensaje" => "No puede tomar más de 3 productos a la vez."));
                } else {
                    if (ProductoPedido::ActualizarEstadoProducto($id, $estado)) {
                        ProductoPedido::SetEmpleadoProducto($id, $id_empleado);
                        Pedido::ActualizarEstadoPedido($productopedido->id_pedido, 'En Preparacion');
                        $payload = json_encode(array("mensaje" => "Se ha modificado el estado del producto."));
                    } else {
                        $payload = json_encode(array("mensaje" => "No se modificó nada."));
                    }
                }
            } else {
                $payload = json_encode(array("mensaje" => "El producto ya fue tomado por un empleado."));
            }
        }

        $response->getBody()->write($payload);
        return $response->withHeader('Content-Type', 'application/json');
    }

    public function ListoParaServir($request, $response, $args)
    {
        $params = $request->getParsedBody();
        $id = $params['id'];

        $productopedido = ProductoPedido::TraerPorId($id);
        if ($productopedido === false) {
            $payload = json_encode(array("mensaje" => "No se pudo encontrar el producto pedido."));
        } else {
            if ($productopedido->estado == 'En Preparacion') {
                ProductoPedido::ActualizarEstadoPorId($productopedido->id, 'Listo para servir');

                $productosPedido = ProductoPedido::TraerPorIdPedido($productopedido->id_pedido);
                $todosListos = true;

                foreach ($productosPedido as $producto) {
                    if ($producto->estado != 'Listo para servir') {
                        $todosListos = false;
                        break;
                    }
                }

                if ($todosListos) {
                    Pedido::ActualizarEstadoPedido($productopedido->id_pedido, 'Listo para servir');
                    $payload = json_encode(array("mensaje" => "Todos los productos del pedido están listos para servir"));
                } else {
                    $payload = json_encode(array("mensaje" => "Producto marcado como listo para servir"));
                }
            } else {
                $payload = json_encode(array("mensaje" => "Primero deberia pereparar el producto"));
            }
        }

        $response->getBody()->write($payload);
        return $response->withHeader('Content-Type', 'application/json');
    }
}
