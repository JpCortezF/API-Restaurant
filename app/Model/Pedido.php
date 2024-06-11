<?php
class Pedido
{
    public $id;
    public $codigo;
    public $nombre_cliente;
    public $estado;
    public $id_mesa;
    public $foto_mesa;

    public function NuevoPedido()
    {
        $objAccesoDatos = AccesoDatos::obtenerInstancia();
        $consulta = $objAccesoDatos->prepararConsulta("INSERT INTO pedidos (codigo, nombre_cliente, id_mesa)
        VALUES (:codigo, :nombre_cliente, :id_mesa)");

        $consulta->bindValue(':codigo', $this->codigo, PDO::PARAM_STR);
        $consulta->bindValue(':nombre_cliente', $this->nombre_cliente, PDO::PARAM_STR);
        $consulta->bindValue(':id_mesa', $this->id_mesa, PDO::PARAM_INT);
        $consulta->execute();

        return $objAccesoDatos->obtenerUltimoId();
    }

    public static function TraerPedidos()
    {
        try {
            $objAccesoDatos = AccesoDatos::obtenerInstancia();
            $consulta = $objAccesoDatos->prepararConsulta("SELECT p.id_pedido, p.codigo as codigo_pedido, p.nombre_cliente, m.codigo as codigo_mesa, p.estado as estado_pedido, p.foto_mesa
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
            $consulta = $objAccesoDatos->prepararConsulta("SELECT p.id_pedido, p.codigo as codigo_pedido, p.nombre_cliente, m.codigo as codigo_mesa, p.estado as estado_pedido, p.foto_mesa
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

    public static function CodigoAlphaNumerico($length = 5)
    {
        $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $charactersLength = strlen($characters);
        $randomString = '';

        for ($i = 0; $i < $length; $i++) {
            $randomString .= $characters[rand(0, $charactersLength - 1)];
        }
        return $randomString;
    }
}
