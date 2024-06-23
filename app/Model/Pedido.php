<?php
class Pedido
{
    public $id;
    public $codigo;
    public $id_mozo;
    public $nombre_cliente;
    public $id_mesa;
    public $estado;
    public $tiempo_estimado;
    public $foto_mesa;

    public function NuevoPedido()
    {
        $objAccesoDatos = AccesoDatos::obtenerInstancia();
        $consulta = $objAccesoDatos->prepararConsulta("INSERT INTO pedidos (id_mozo,codigo,nombre_cliente,id_mesa,estado,tiempo_estimado,foto_mesa)
        VALUES (:id_mozo,:codigo,:nombre_cliente,:id_mesa,:estado,:tiempo_estimado,:foto_mesa)");

        $consulta->bindValue(':id_mozo', $this->id_mozo, PDO::PARAM_INT);
        $consulta->bindValue(':codigo', $this->codigo, PDO::PARAM_STR);
        $consulta->bindValue(':nombre_cliente', $this->nombre_cliente, PDO::PARAM_STR);
        $consulta->bindValue(':id_mesa', $this->id_mesa, PDO::PARAM_INT);
        $consulta->bindValue(':estado', "Pendiente", PDO::PARAM_STR);
        $consulta->bindValue(':tiempo_estimado', $this->tiempo_estimado, PDO::PARAM_INT);
        $consulta->bindValue(':foto_mesa', $this->foto_mesa, PDO::PARAM_STR);
        $consulta->execute();

        return $objAccesoDatos->obtenerUltimoId();
    }

    public static function TraerPedidos()
    {
        try {
            $objAccesoDatos = AccesoDatos::obtenerInstancia();
            $consulta = $objAccesoDatos->prepararConsulta("SELECT p.id_pedido, p.id_mozo, p.codigo as codigo_pedido, p.nombre_cliente, p.id_mesa, m.codigo as codigo_mesa, p.estado as estado_pedido, p.tiempo_estimado, p.foto_mesa
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
            $consulta = $objAccesoDatos->prepararConsulta("SELECT p.id_pedido, p.id_mozo, p.codigo as codigo_pedido, p.nombre_cliente, p.id_mesa, m.codigo as codigo_mesa, p.estado as estado_pedido, p.tiempo_estimado, p.foto_mesa
            FROM pedidos p
            INNER JOIN mesas m ON p.id_mesa = m.id_mesa
            WHERE p.id_pedido = :id_pedido");
            $consulta->bindValue(':id_pedido', $id_pedido, PDO::PARAM_INT);
            $consulta->execute();
            $pedido = $consulta->fetch(PDO::FETCH_OBJ);
            return $pedido;
        } catch (Exception $e) {
            throw new Exception("Error al traer el pedido: " . $e->getMessage());
        }
    }

    public static function EliminarPedido($id_pedido)
    {
        $objAccesoDatos = AccesoDatos::obtenerInstancia();
        $consulta = $objAccesoDatos->prepararConsulta("UPDATE pedidos SET activo = 'Eliminado' WHERE id_pedido = :id_pedido");
        $consulta->bindValue(':id_pedido', $id_pedido, PDO::PARAM_INT);
        return $consulta->execute();
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

    public static function GuardarImagenPedido($ruta, $urlImagen, $id_mesa, $nombre_cliente)
    {
        if (!file_exists($ruta)) {
            mkdir($ruta, 0777, true); // Create the directory with appropriate permissions
        }

        $destino = $ruta . DIRECTORY_SEPARATOR . $id_mesa . "-" . $nombre_cliente . ".jpg";
        // Move the uploaded file to the destination
        if (move_uploaded_file($urlImagen["tmp_name"], $destino)) {
            return $destino;
        } else {
            throw new Exception("Error al mover la imagen.");
        }
    }

    public static function ActualizarEstadoPedido($id_pedido, $estado)
    {
        $objetoAccesoDato = AccesoDatos::obtenerInstancia();
        $consulta = $objetoAccesoDato->prepararConsulta("UPDATE pedidos SET estado = :estado WHERE id_pedido = :id_pedido");

        $consulta->bindValue(':id_pedido', $id_pedido, PDO::PARAM_INT);
        $consulta->bindValue(':estado', $estado, PDO::PARAM_STR);
        return $consulta->execute();
    }

    public static function ObtenerPedidosPorSectorYPendiente($sectores)
    {
        $objetoAccesoDato = AccesoDatos::obtenerInstancia();
        $sectoresPlaceholder = implode(',', array_fill(0, count($sectores), '?'));
        var_dump($sectoresPlaceholder);
        $consulta = $objetoAccesoDato->prepararConsulta("SELECT p.*
        FROM pedidos p
        INNER JOIN productopedidos pp ON p.id_pedido = pp.id_pedido
        INNER JOIN productos prod ON pp.id_producto = prod.id_producto
        INNER JOIN sectores s ON prod.id_sector = s.id_sector
        WHERE p.estado = 'Pendiente' AND s.id_sector IN ($sectoresPlaceholder)");

        foreach ($sectores as $index => $sector) {
            $consulta->bindValue($index + 1, $sector, PDO::PARAM_INT);
        }

        $consulta->execute();
        return $consulta->fetchAll(PDO::FETCH_OBJ);
    }

    public static function ObtenerPrecioFinal($id_pedido)
    {
        $objAccesoDatos = AccesoDatos::obtenerInstancia();
        $consulta = $objAccesoDatos->prepararConsulta("SELECT p.id_pedido,
        SUM(pr.precio * pp.cantidad) AS precio_final
        FROM pedidos p
        JOIN productopedidos pp ON p.id_pedido = pp.id_pedido
        JOIN productos pr ON pp.id_producto = pr.id_producto
        WHERE p.id_pedido = :id_pedido
        GROUP BY p.id_pedido");
        $consulta->bindValue(':id_pedido', $id_pedido, PDO::PARAM_INT);
        $consulta->execute();

        return $consulta->fetch(PDO::FETCH_OBJ)->precio_final;
    }

    public static function ObtenerDesglosePedido($id_pedido)
    {
        $objAccesoDatos = AccesoDatos::obtenerInstancia();
        $consulta = $objAccesoDatos->prepararConsulta("SELECT pr.descripcion, pr.precio, pp.cantidad, (pr.precio * pp.cantidad) AS total_producto
        FROM pedidos p
        JOIN productopedidos pp ON p.id_pedido = pp.id_pedido
        JOIN productos pr ON pp.id_producto = pr.id_producto
        WHERE p.id_pedido = :id_pedido");
        $consulta->bindValue(':id_pedido', $id_pedido, PDO::PARAM_INT);
        $consulta->execute();

        return $consulta->fetchAll(PDO::FETCH_OBJ);
    }
}
