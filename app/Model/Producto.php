<?php
class Producto
{
    const ESTADO_EN_PREPARACION = 'en preparacion';
    const ESTADO_LISTO_PARA_SERVIR = 'listo para servir';
    const ESTADO_SERVIDO = 'servido';
    const ESTADO_CANCELADO = 'cancelado';

    private $_id_producto;
    private $_id_pedido;
    private $_descripcion;    // (vino, cerveza, empanadas, etc.)
    private $_id_sector;    // (barra de tragos y vinos, barra de choperas, cocina, Candy Bar)
    private $_id_empleado;
    private $_estado;

    public function __construct($id_pedido, $descripcion, $id_sector, $id_empleado)
    {
        $this->_id_pedido = $id_pedido;
        $this->_descripcion = $descripcion;
        $this->_id_sector = $id_sector;
        $this->_id_empleado = $id_empleado;
    }
    public function NuevoProducto()
    {
        $objAccesoDatos = AccesoDatos::obtenerInstancia();
        $consulta = $objAccesoDatos->prepararConsulta("INSERT INTO productos (id_pedido, descripcion, id_sector, id_empleado)
         VALUES (:id_pedido, :descripcion, :id_sector, :id_empleado)");

        $consulta->bindValue(':id_pedido', $this->_id_pedido, PDO::PARAM_STR);
        $consulta->bindValue(':descripcion', $this->_descripcion, PDO::PARAM_STR);
        $consulta->bindValue(':id_sector', $this->_id_sector, PDO::PARAM_STR);
        $consulta->bindValue(':id_empleado', $this->_id_empleado, PDO::PARAM_STR);
        $consulta->execute();

        return $objAccesoDatos->obtenerUltimoId();
    }

    public static function TraerProductos()
    {
        try {
            $objAccesoDatos = AccesoDatos::obtenerInstancia();
            $consulta = $objAccesoDatos->prepararConsulta("SELECT prod.id_producto, ped.codigo, prod.descripcion, s.sector, e.nombre AS nombre_empleado
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
            $consulta = $objAccesoDatos->prepararConsulta("SELECT prod.id_producto, ped.codigo AS pedido_codigo, prod.descripcion, s.sector, e.nombre AS nombre_empleado
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
}
