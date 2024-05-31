<?php
class Pedido
{
    public $id;
    public $codigo;
    public $nombre_cliente;
    public $id_mesa;
    public $tiempo_estimado;

    public function NuevoPedido()
    {
        $objAccesoDatos = AccesoDatos::obtenerInstancia();
        $consulta = $objAccesoDatos->prepararConsulta("INSERT INTO pedidos (codigo, nombre_cliente, id_mesa, tiempo_estimado)
        VALUES (:codigo, :nombre_cliente, :id_mesa, :tiempo_estimado)");

        $consulta->bindValue(':codigo', $this->codigo, PDO::PARAM_STR);
        $consulta->bindValue(':nombre_cliente', $this->nombre_cliente, PDO::PARAM_STR);
        $consulta->bindValue(':id_mesa', $this->id_mesa, PDO::PARAM_INT);
        $consulta->bindValue(':tiempo_estimado', $this->tiempo_estimado, PDO::PARAM_STR);
        $consulta->execute();

        return $objAccesoDatos->obtenerUltimoId();
    }

    public static function TraerPedidos()
    {
        try {
            $objAccesoDatos = AccesoDatos::obtenerInstancia();
            $consulta = $objAccesoDatos->prepararConsulta("SELECT p.id_pedido, p.codigo as codigo_pedido, p.nombre_cliente, m.codigo as codigo_mesa, p.estado as estado_pedido, p.tiempo_estimado, p.foto_mesa
            FROM pedidos p
            INNER JOIN mesas m ON p.id_mesa = m.id_mesa");
            $consulta->execute();
            $productos = $consulta->fetchAll(PDO::FETCH_OBJ);
            return $productos;
        } catch (Exception $e) {
            throw new Exception("Error al traer los pedidos: " . $e->getMessage());
        }
    }

    public static function TraerPedido($id_pedido)
    {
        try {
            $objAccesoDatos = AccesoDatos::obtenerInstancia();
            $consulta = $objAccesoDatos->prepararConsulta("SELECT p.id_pedido, p.codigo as codigo_pedido, p.nombre_cliente, m.codigo as codigo_mesa, p.estado as estado_pedido, p.tiempo_estimado, p.foto_mesa
            FROM pedidos p
            INNER JOIN mesas m ON p.id_mesa = m.id_mesa
            WHERE p.id_pedido = :id_pedido");
            $consulta->bindValue(':id_pedido', $id_pedido, PDO::PARAM_INT);
            $consulta->execute();
            $empleado = $consulta->fetch(PDO::FETCH_ASSOC);
            return $empleado;
        } catch (Exception $e) {
            throw new Exception("Error al traer el pedido: " . $e->getMessage());
        }
    }
}
