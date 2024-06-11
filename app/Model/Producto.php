<?php
class Producto
{
    public $id_producto;
    public $id_pedido;
    public $descripcion;    // (vino, cerveza, empanadas, etc.)
    public $precio;
    public $id_sector;    // (barra de tragos y vinos, barra de choperas, cocina, Candy Bar)
    public $id_empleado;
    public $estado;
    public $tiempo_producto;

    public function NuevoProducto()
    {
        $objAccesoDatos = AccesoDatos::obtenerInstancia();
        $consulta = $objAccesoDatos->prepararConsulta("INSERT INTO productos (id_pedido, descripcion, precio, id_sector, id_empleado, tiempo_producto)
         VALUES (:id_pedido, :descripcion, :precio, :id_sector, :id_empleado, :tiempo_producto)");

        $consulta->bindValue(':id_pedido', $this->id_pedido, PDO::PARAM_STR);
        $consulta->bindValue(':descripcion', $this->descripcion, PDO::PARAM_STR);
        $consulta->bindValue(':precio', $this->precio, PDO::PARAM_STR);
        $consulta->bindValue(':id_sector', $this->id_sector, PDO::PARAM_STR);
        $consulta->bindValue(':id_empleado', $this->id_empleado, PDO::PARAM_STR);
        $consulta->bindValue(':tiempo_producto', $this->tiempo_producto, PDO::PARAM_STR);
        $consulta->execute();

        return $objAccesoDatos->obtenerUltimoId();
    }

    public static function TraerProductos()
    {
        try {
            $objAccesoDatos = AccesoDatos::obtenerInstancia();
            $consulta = $objAccesoDatos->prepararConsulta("SELECT prod.id_producto, ped.codigo, prod.descripcion, prod.precio, s.sector, e.nombre AS nombre_empleado, prod.estado, prod.tiempo_producto
            FROM productos prod
            INNER JOIN pedidos ped ON prod.id_pedido = ped.id_pedido
            INNER JOIN sectores s ON prod.id_sector = s.id_sector
            INNER JOIN empleados e ON prod.id_empleado = e.id_empleado");
            $consulta->execute();
            $productos = $consulta->fetchAll(PDO::FETCH_OBJ);
            return $productos;
        } catch (Exception $e) {
            throw new Exception("Error al traer empleados: " . $e->getMessage());
        }
    }

    public static function TraerProducto($id_producto)
    {
        try {
            $objAccesoDatos = AccesoDatos::obtenerInstancia();
            $consulta = $objAccesoDatos->prepararConsulta("SELECT prod.id_producto, ped.codigo AS pedido_codigo, prod.descripcion, prod.precio, s.sector, e.nombre AS nombre_empleado
            FROM productos prod
            INNER JOIN pedidos ped ON prod.id_pedido = ped.id_pedido
            INNER JOIN sectores s ON prod.id_sector = s.id_sector
            INNER JOIN empleados e ON prod.id_empleado = e.id_empleado
            WHERE prod.id_producto = :id_producto");
            $consulta->bindValue(':id_producto', $id_producto, PDO::PARAM_INT);
            $consulta->execute();
            $producto = $consulta->fetch(PDO::FETCH_ASSOC);
            return $producto;
        } catch (Exception $e) {
            throw new Exception("Error al traer el producto: " . $e->getMessage());
        }
    }

    public static function ObtenerProducto($id_producto)
    {
        $objAccesoDatos = AccesoDatos::obtenerInstancia();
        $consulta = $objAccesoDatos->prepararConsulta("SELECT * FROM productos WHERE id_producto = :id_producto");
        $consulta->bindValue(':id_producto', $id_producto, PDO::PARAM_INT);
        $consulta->execute();

        return $consulta->fetchObject('Producto');
    }

    public static function ModificarProducto($producto)
    {
        $objAccesoDato = AccesoDatos::obtenerInstancia();
        $consulta = $objAccesoDato->prepararConsulta("UPDATE productos SET id_pedido = :id_pedido, descripcion = :descripcion, precio = :precio, id_sector = :id_sector, id_empleado = :id_empleado, tiempo_producto = :tiempo_producto, estado = :estado WHERE id_producto = :id_producto");
        $consulta->bindValue(':id_producto', $producto->id_producto, PDO::PARAM_INT);
        $consulta->bindValue(':id_pedido', $producto->id_pedido, PDO::PARAM_INT);
        $consulta->bindValue(':descripcion', $producto->descripcion, PDO::PARAM_STR);
        $consulta->bindValue(':precio', $producto->precio, PDO::PARAM_INT);
        $consulta->bindValue(':id_sector', $producto->id_sector, PDO::PARAM_INT);
        $consulta->bindValue(':id_empleado', $producto->id_empleado, PDO::PARAM_INT);
        $consulta->bindValue(':tiempo_producto', $producto->tiempo_producto, PDO::PARAM_STR);
        $consulta->bindValue(':estado', $producto->estado, PDO::PARAM_STR);

        $consulta->execute();
    }

    public static function BorrarProducto($id_producto)
    {
        $objAccesoDato = AccesoDatos::obtenerInstancia();
        $consulta = $objAccesoDato->prepararConsulta("UPDATE productos SET estado = 'cancelado' WHERE id_producto = :id_producto");
        $consulta->bindValue(':id_producto', $id_producto, PDO::PARAM_INT);
        $consulta->execute();
    }
}
