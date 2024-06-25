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

    public static function GuardarProductos($request, $response, $args)
    {
        $path = "productos.csv";
        $arrayProductos = array();
        $productos = Producto::TraerProductos();

        foreach ($productos as $p) {
            $producto = array($p->id_producto, $p->descripcion, $p->precio, $p->id_sector, $p->estado);
            $arrayProductos[] = $producto;
        }

        $archivo = fopen($path, "w");
        if ($archivo === false) {
            $retorno = json_encode(array("mensaje" => "Error al abrir el archivo para escribir"));
            $response->getBody()->write($retorno);
            return $response->withStatus(500);
        }

        if (!empty($arrayProductos)) {
            $encabezado = array("id_producto", "descripcion", "precio", "id_sector", "estado");
            fputcsv($archivo, $encabezado);
            foreach ($arrayProductos as $fila) {
                fputcsv($archivo, $fila);
            }
        }

        fclose($archivo);
        $retorno = json_encode(array("mensaje" => "Productos guardados en CSV con exito"));

        $response->getBody()->write($retorno);
        return $response;
    }
    public static function CargarProductos($request, $response, $args)
    {
        $path = "productos.csv";
        $archivo = fopen($path, "r");

        if ($archivo === false) {
            $retorno = json_encode(array("mensaje" => "Error al abrir el archivo para leer"));
            $response->getBody()->write($retorno);
            return $response->withStatus(500);
        }

        $encabezado = fgets($archivo); // Leer la primera línea (encabezado)

        while (!feof($archivo)) {
            $linea = fgets($archivo);
            if ($linea === false || trim($linea) == "") {
                continue; // Saltear líneas vacías
            }

            $datos = str_getcsv($linea);
            if (count($datos) < 5) {
                continue; // Saltear líneas incompletas
            }

            $producto = new Producto();
            $producto->id_producto = $datos[0];
            $producto->descripcion = $datos[1];
            $producto->precio = $datos[2];
            $producto->id_sector = $datos[3];
            $producto->estado = $datos[4];
            $producto->NuevoProducto();
        }

        fclose($archivo);

        $retorno = json_encode(array("mensaje" => "Productos guardados en la base de datos con exito"));
        $response->getBody()->write($retorno);
        return $response;
    }
}
