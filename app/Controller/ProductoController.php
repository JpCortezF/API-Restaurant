<?php
require './Model/Producto.php';

class ProductoController implements IApiUsable
{

    public function GuardarUno($request, $response, $args)
    {
        $parametros = $request->getParsedBody();
        $producto = new Producto();

        $producto->descripcion = $parametros['descripcion'];
        $producto->precio = $parametros['precio'];
        $producto->id_sector = $parametros['id_sector'];
        error_log(print_r($parametros, true));
        error_log(print_r($producto, true));
        $producto->NuevoProducto();

        $payload = json_encode(array("mensaje" => "Producto creado con exito"));

        $response->getBody()->write($payload);
        return $response->withHeader('Content-Type', 'application/json');
    }

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

    public function ModificarUno($request, $response, $args)
    {
        $params = $request->getParsedBody();
        $producto = Producto::ObtenerProducto($params['id_producto']);
        if ($producto && isset($params['descripcion'], $params['precio'], $params['id_sector'], $params['estado'])) {
            $producto->descripcion = $params['descripcion'];
            $producto->precio = $params['precio'];
            $producto->id_sector = $params['id_sector'];
            $producto->estado = $params['estado'];

            Producto::ModificarProducto($producto);
            $payload = json_encode(array("mensaje" => "Producto modificado con éxito"));
        } else {
            $payload = json_encode(array("error" => "Datos del producto incompletos"));
        }
        $response->getBody()->write($payload);
        return $response->withHeader('Content-Type', 'application/json');
    }

    public function BorrarUno($request, $response, $args)
    {
        $parametros = $request->getParsedBody();
        if (!isset($parametros['id_producto'])) {
            $payload = json_encode(array("error" => "id_producto no proporcionado"));
            $response->getBody()->write($payload);
            return $response->withHeader('Content-Type', 'application/json')->withStatus(400);
        }
        $id_producto = $parametros['id_producto'];
        Producto::BorrarProducto($id_producto);

        $payload = json_encode(array("mensaje" => "Producto borrado con exito"));

        $response->getBody()->write($payload);
        return $response->withHeader('Content-Type', 'application/json');
    }
}
