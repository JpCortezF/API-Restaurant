<?php
require './Model/Producto.php';

class ProductoController
{
    public function TraerUno($request, $response, $args)
    {
        try {
            $id_producto = $args['id_producto'];
            $producto = Producto::TraerProducto($id_producto);
            $payload = json_encode($producto);

            $response->getBody()->write($payload);
            return $response->withHeader('Content-Type', 'application/json');
        } catch (Exception $e) {
            return $response->withStatus(500)->write('Error interno del servidor');
        }
    }

    public function TraerTodos($request, $response, $args)
    {
        $lista = Producto::TraerProductos();
        $payload = json_encode(array("listaProductos" => $lista));

        $response->getBody()->write($payload);
        return $response->withHeader('Content-Type', 'application/json');
    }

    public function GuardarUno($request, $response, $args)
    {
        $parametros = $request->getParsedBody();

        $id_pedido = $parametros['id_pedido'];
        $descripcion = $parametros['descripcion'];
        $id_sector = $parametros['id_sector'];
        $id_empleado = $parametros['id_empleado'];
        $producto = new Producto($id_pedido, $descripcion, $id_sector, $id_empleado);
        error_log(print_r($parametros, true));
        error_log(print_r($producto, true));
        $producto->NuevoProducto();

        $payload = json_encode(array("mensaje" => "Producto creado con exito"));

        $response->getBody()->write($payload);
        return $response->withHeader('Content-Type', 'application/json');
    }
}
