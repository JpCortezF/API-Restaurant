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
        $consulta->execute();
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

    public static function ActualizarEstadoYTiempo($id, $nuevoEstado, $tiempo_estimado)
    {
        $objetoAccesoDato = AccesoDatos::obtenerInstancia();

        $consulta = $objetoAccesoDato->prepararConsulta("UPDATE productopedidos SET estado = :estado, tiempo_estimado = :tiempo_estimado WHERE id = :id");

        $consulta->bindValue(':id', $id, PDO::PARAM_INT);
        $consulta->bindValue(':estado', $nuevoEstado, PDO::PARAM_STR);
        $consulta->bindValue(':tiempo_estimado', $tiempo_estimado, PDO::PARAM_INT);

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

    public static function SetEmpleadoProducto($id, $id_empleado)
    {
        $objetoAccesoDato = AccesoDatos::obtenerInstancia();
        $consulta = $objetoAccesoDato->prepararConsulta("UPDATE productopedidos SET id_empleado = :id_empleado WHERE id = :id");
        $consulta->bindValue(':id', $id, PDO::PARAM_INT);
        $consulta->bindValue(':id_empleado', $id_empleado, PDO::PARAM_INT);

        return $consulta->execute();
    }

    public static function PedidoProductoPorIdEmpleado($id_empleado)
    {
        $objetoAccesoDato = AccesoDatos::obtenerInstancia();
        $consulta = $objetoAccesoDato->prepararConsulta("SELECT id_producto FROM productopedidos WHERE id_empleado = :id_empleado");
        $consulta->bindValue(':id_empleado', $id_empleado, PDO::PARAM_INT);
        $consulta->execute();

        return $consulta->fetchColumn(); // Devolvemos solo el id_producto
    }
}
