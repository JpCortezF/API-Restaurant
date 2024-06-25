<?php
class Producto
{
    public $id_producto;
    public $descripcion;    // (vino, cerveza, empanadas, etc.)
    public $precio;
    public $id_sector;    // (barra de tragos y vinos, barra de choperas, cocina, Candy Bar)
    public $estado;

    public function NuevoProducto()
    {
        $objAccesoDatos = AccesoDatos::obtenerInstancia();
        $consulta = $objAccesoDatos->prepararConsulta("INSERT INTO productos (descripcion, precio, id_sector, estado)
         VALUES (:descripcion, :precio, :id_sector, :estado)");

        $consulta->bindValue(':descripcion', $this->descripcion, PDO::PARAM_STR);
        $consulta->bindValue(':precio', $this->precio, PDO::PARAM_STR);
        $consulta->bindValue(':id_sector', $this->id_sector, PDO::PARAM_STR);
        $consulta->bindValue(':estado', 1, PDO::PARAM_INT);
        $consulta->execute();

        return $objAccesoDatos->obtenerUltimoId();
    }

    public static function TraerProductos()
    {
        try {
            $objAccesoDatos = AccesoDatos::obtenerInstancia();
            $consulta = $objAccesoDatos->prepararConsulta("SELECT p.id_producto, p.descripcion, p.precio, p.id_sector, s.sector, p.estado
            FROM productos p
            INNER JOIN sectores s ON p.id_sector = s.id_sector
            WHERE p.estado = 1 ");
            $consulta->execute();
            $productos = $consulta->fetchAll(PDO::FETCH_OBJ);
            return $productos;
        } catch (Exception $e) {
            throw new Exception("Error al traer productos: " . $e->getMessage());
        }
    }

    public static function TraerProducto($id_producto)
    {
        try {
            $objAccesoDatos = AccesoDatos::obtenerInstancia();
            $consulta = $objAccesoDatos->prepararConsulta("SELECT p.id_producto, p.descripcion, p.precio, s.sector, p.estado
            FROM productos p
            INNER JOIN sectores s ON p.id_sector = s.id_sector
            WHERE p.id_producto = :id_producto AND p.estado = 1");
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

    public static function ObtenerSectorProducto($id_producto)
    {
        $objAccesoDatos = AccesoDatos::obtenerInstancia();
        $consulta = $objAccesoDatos->prepararConsulta("SELECT p.descripcion, s.sector
        FROM productos p
        INNER JOIN sectores s ON p.id_sector = s.id_sector
        WHERE id_producto = :id_producto");
        $consulta->bindValue(':id_producto', $id_producto, PDO::PARAM_INT);
        $consulta->execute();

        $resultado = $consulta->fetch(PDO::FETCH_OBJ);
        return $resultado ? array('descripcion' => $resultado->descripcion, 'sector' => $resultado->sector) : null;
    }

    public static function ObtenerSectorPorIdProducto($id_producto)
    {
        $objetoAccesoDato = AccesoDatos::obtenerInstancia();
        $consulta = $objetoAccesoDato->prepararConsulta("SELECT id_sector FROM productos WHERE id_producto = :id_producto");
        $consulta->bindValue(':id_producto', $id_producto, PDO::PARAM_INT);
        $consulta->execute();

        return $consulta->fetchColumn();
    }

    public static function ModificarProducto($producto)
    {
        $objAccesoDato = AccesoDatos::obtenerInstancia();
        $consulta = $objAccesoDato->prepararConsulta("UPDATE productos SET descripcion = :descripcion, precio = :precio, id_sector = :id_sector, id_empleado = :id_empleado, estado = :estado WHERE id_producto = :id_producto");
        $consulta->bindValue(':id_producto', $producto->id_producto, PDO::PARAM_INT);
        $consulta->bindValue(':descripcion', $producto->descripcion, PDO::PARAM_STR);
        $consulta->bindValue(':precio', $producto->precio, PDO::PARAM_INT);
        $consulta->bindValue(':id_sector', $producto->id_sector, PDO::PARAM_INT);
        $consulta->bindValue(':estado', $producto->estado, PDO::PARAM_STR);

        $consulta->execute();
    }

    public static function BorrarProducto($id_producto)
    {
        $objAccesoDato = AccesoDatos::obtenerInstancia();
        $consulta = $objAccesoDato->prepararConsulta("UPDATE productos SET estado = '0' WHERE id_producto = :id_producto");
        $consulta->bindValue(':id_producto', $id_producto, PDO::PARAM_INT);
        $consulta->execute();
    }
}
