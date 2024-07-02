<?php

class ProductoPedido
{
    public $id;
    public $id_pedido;
    public $id_producto;
    public $cantidad;
    public $estado;
    public $tiempo_estimado;
    public $id_empleado;

    // Agregados para evitar el warning
    public $producto_descripcion;
    public $id_sector;

    public function CrearProductoPedido()
    {
        $objAccesoDatos = AccesoDatos::obtenerInstancia();
        $consulta = $objAccesoDatos->prepararConsulta("INSERT INTO productopedidos (id_pedido,id_producto,cantidad,estado,tiempo_estimado)
        VALUES (:id_pedido,:id_producto,:cantidad,:estado,:tiempo_estimado)");
        $consulta->bindValue(':id_pedido', $this->id_pedido);
        $consulta->bindValue(':id_producto', $this->id_producto);
        $consulta->bindValue(':cantidad', $this->cantidad);
        $consulta->bindValue(':estado', $this->estado);
        $consulta->bindValue(':tiempo_estimado', $this->tiempo_estimado);
        $this->id = $objAccesoDatos->obtenerUltimoId();
        $consulta->execute();

        return $objAccesoDatos->obtenerUltimoId();
    }

    public static function TraerProductoPedidos()
    {
        $objAccesoDatos = AccesoDatos::obtenerInstancia();
        $consulta = $objAccesoDatos->prepararConsulta("SELECT * FROM productopedidos");
        $consulta->execute();

        return $consulta->fetchAll(PDO::FETCH_CLASS, 'ProductoPedido');
    }

    public static function TraerPorId($id)
    {
        $objAccesoDatos = AccesoDatos::obtenerInstancia();
        $consulta = $objAccesoDatos->prepararConsulta("SELECT * FROM productopedidos WHERE id = :id");
        $consulta->bindValue(':id', $id, PDO::PARAM_INT);
        $consulta->execute();

        return $consulta->fetchObject('ProductoPedido');
    }

    public static function TraerPorIdPedido($id_pedido)
    {
        $objAccesoDatos = AccesoDatos::obtenerInstancia();
        $consulta = $objAccesoDatos->prepararConsulta("SELECT * FROM productopedidos WHERE id_pedido = :id_pedido");
        $consulta->bindValue(':id_pedido', $id_pedido);
        $consulta->execute();

        return $consulta->fetchAll(PDO::FETCH_CLASS, 'ProductoPedido');
    }

    public static function ActualizarEstadoProducto($id, $nuevoEstado)
    {
        $objetoAccesoDato = AccesoDatos::obtenerInstancia();

        $consulta = $objetoAccesoDato->prepararConsulta("UPDATE productopedidos SET estado = :estado WHERE id = :id");

        $consulta->bindValue(':id', $id, PDO::PARAM_INT);
        $consulta->bindValue(':estado', $nuevoEstado, PDO::PARAM_STR);

        return $consulta->execute();
    }
    public static function ActualizarEstadoPorPedido($id_pedido, $nuevo_estado)
    {
        $objetoAccesoDato = AccesoDatos::obtenerInstancia();
        $consulta = $objetoAccesoDato->prepararConsulta("UPDATE productopedidos SET estado = :estado WHERE id_pedido = :id_pedido");

        $consulta->bindValue(':id_pedido', $id_pedido, PDO::PARAM_INT);
        $consulta->bindValue(':estado', $nuevo_estado, PDO::PARAM_STR);

        return $consulta->execute();
    }
    public static function ActualizarEstadoPorId($id, $nuevo_estado)
    {
        $objetoAccesoDato = AccesoDatos::obtenerInstancia();
        $consulta = $objetoAccesoDato->prepararConsulta("UPDATE productopedidos SET estado = :estado WHERE id = :id");

        $consulta->bindValue(':id', $id, PDO::PARAM_INT);
        $consulta->bindValue(':estado', $nuevo_estado, PDO::PARAM_STR);

        return $consulta->execute();
    }

    public static function EmpleadoConProductos($id_empleado, $estado)
    {
        $objetoAccesoDato = AccesoDatos::obtenerInstancia();
        $consulta = $objetoAccesoDato->prepararConsulta("SELECT * FROM productopedidos WHERE id_empleado = :id_empleado AND estado = :estado");
        $consulta->bindValue(':id_empleado', $id_empleado, PDO::PARAM_INT);
        $consulta->bindValue(':estado', $estado, PDO::PARAM_STR);
        $consulta->execute();
        return $consulta->fetchAll(PDO::FETCH_OBJ);
    }

    public static function SetEmpleadoProducto($id, $id_empleado)
    {
        $objetoAccesoDato = AccesoDatos::obtenerInstancia();
        $consulta = $objetoAccesoDato->prepararConsulta("UPDATE productopedidos SET id_empleado = :id_empleado WHERE id = :id");
        $consulta->bindValue(':id', $id, PDO::PARAM_INT);
        $consulta->bindValue(':id_empleado', $id_empleado, PDO::PARAM_INT);

        return $consulta->execute();
    }

    public static function ObtenerTiempo($id_pedido)
    {
        $objetoAccesoDato = AccesoDatos::obtenerInstancia();
        $consulta = $objetoAccesoDato->prepararConsulta("SELECT tiempo_estimado FROM productopedidos WHERE id_pedido = :id_pedido ORDER BY tiempo_estimado DESC LIMIT 1");
        $consulta->bindValue(':id_pedido', $id_pedido, PDO::PARAM_INT);
        $consulta->execute();

        return $consulta->fetchColumn();
    }

    public static function PedidoProductoPorIdEmpleado($id_empleado)
    {
        $objetoAccesoDato = AccesoDatos::obtenerInstancia();
        $consulta = $objetoAccesoDato->prepararConsulta("SELECT id_producto FROM productopedidos WHERE id_empleado = :id_empleado");
        $consulta->bindValue(':id_empleado', $id_empleado, PDO::PARAM_INT);
        $consulta->execute();

        return $consulta->fetchColumn();
    }

    public static function PedidoProductoPendientesPorSectores($sectores)
    {
        $sectorPlaceholders = implode(',', array_fill(0, count($sectores), '?'));
        $objAccesoDatos = AccesoDatos::obtenerInstancia();
        $consulta = $objAccesoDatos->prepararConsulta("SELECT pp.*, p.descripcion AS producto_descripcion, p.id_sector
        FROM productopedidos pp
        INNER JOIN productos p ON pp.id_producto = p.id_producto
        WHERE pp.estado = 'Pendiente' AND p.id_sector IN ($sectorPlaceholders)");
        foreach ($sectores as $index => $sector) {
            $consulta->bindValue($index + 1, $sector, PDO::PARAM_INT);
        }
        $consulta->execute();

        return $consulta->fetchAll(PDO::FETCH_CLASS, 'ProductoPedido');
    }

    public static function ProductoMasVendido()
    {
        $objAccesoDatos = AccesoDatos::obtenerInstancia();
        $consulta = $objAccesoDatos->prepararConsulta("SELECT p.id_producto, p.descripcion, SUM(pp.cantidad) as total_vendidos
        FROM productopedidos pp
        INNER JOIN productos p ON pp.id_producto = p.id_producto
        GROUP BY p.id_producto, p.descripcion
        ORDER BY total_vendidos DESC");
        $consulta->execute();

        return $consulta->fetchObject();
    }
    public static function ProductoMenosVendido()
    {
        $objAccesoDatos = AccesoDatos::obtenerInstancia();
        $consulta = $objAccesoDatos->prepararConsulta("SELECT p.id_producto, p.descripcion, SUM(pp.cantidad) as total_vendidos
        FROM productopedidos pp
        INNER JOIN productos p ON pp.id_producto = p.id_producto
        GROUP BY p.id_producto, p.descripcion
        ORDER BY total_vendidos");
        $consulta->execute();

        return $consulta->fetchObject();
    }
}
