<?php
class Encuesta
{
    public $id;
    public $id_mesa;
    public $id_pedido;
    public $nombre_cliente;
    public $puntuacion_mesa;
    public $puntuacion_mozo;
    public $puntuacion_cocinero;
    public $puntuacion_restaurant;
    public $comentario;

    public static function CrearEncuesta($encuesta)
    {
        $objAccesoDatos = AccesoDatos::obtenerInstancia();

        $consulta = $objAccesoDatos->prepararConsulta("INSERT INTO encuestas (id_mesa,id_pedido,nombre_cliente,puntuacion_mesa,puntuacion_mozo,puntuacion_cocinero,puntuacion_restaurante,comentario)
        VALUES(:id_mesa,:id_pedido,:nombre_cliente,:puntuacion_mesa,:puntuacion_mozo,:puntuacion_cocinero,:puntuacion_restaurante,:comentario)");
        $consulta->bindValue(':id_mesa', $encuesta->id_mesa);
        $consulta->bindValue(':id_pedido', $encuesta->id_pedido);
        $consulta->bindValue(':nombre_cliente', $encuesta->nombre_cliente);
        $consulta->bindValue(':puntuacion_mesa', $encuesta->puntuacion_mesa);
        $consulta->bindValue(':puntuacion_mozo', $encuesta->puntuacion_mozo);
        $consulta->bindValue(':puntuacion_cocinero', $encuesta->puntuacion_cocinero);
        $consulta->bindValue(':puntuacion_restaurante', $encuesta->puntuacion_restaurant);
        $consulta->bindValue(':comentario', $encuesta->comentario);
        $consulta->execute();
    }

    public static function TraerEncuestas()
    {
        $objAccesoDatos = AccesoDatos::obtenerInstancia();
        $consulta = $objAccesoDatos->prepararConsulta("SELECT * FROM encuestas");
        $consulta->execute();

        return $consulta->fetchAll(PDO::FETCH_OBJ);
    }

    public static function TraerPorId($id)
    {
        $objAccesoDatos = AccesoDatos::obtenerInstancia();
        $consulta = $objAccesoDatos->prepararConsulta("SELECT * FROM encuestas WHERE id = :id");
        $consulta->bindValue(':id', $id, PDO::PARAM_INT);
        $consulta->execute();

        return $consulta->fetchObject('Encuesta');
    }

    public static function TraerMejoresComentarios()
    {
        $objAccesoDatos = AccesoDatos::obtenerInstancia();
        $consulta = $objAccesoDatos->prepararConsulta("SELECT * FROM encuestas ORDER BY puntuacion_restaurant DESC");
        $consulta->execute();

        return $consulta->fetchAll(PDO::FETCH_CLASS, 'Encuesta');
    }
}
