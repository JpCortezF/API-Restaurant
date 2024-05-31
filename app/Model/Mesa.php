<?php
class Mesa
{
    public $id;
    public $codigo;
    public $estado;

    public function NuevaMesa()
    {
        $objAccesoDatos = AccesoDatos::obtenerInstancia();
        $consulta = $objAccesoDatos->prepararConsulta("INSERT INTO mesas (codigo)
         VALUES (:codigo)");

        $consulta->bindValue(':codigo', $this->codigo, PDO::PARAM_STR);
        $consulta->execute();

        return $objAccesoDatos->obtenerUltimoId();
    }

    public static function TraerMesas()
    {
        try {
            $objAccesoDatos = AccesoDatos::obtenerInstancia();
            $consulta = $objAccesoDatos->prepararConsulta("SELECT * FROM mesas");
            $consulta->execute();
            $productos = $consulta->fetchAll(PDO::FETCH_OBJ);
            return $productos;
        } catch (Exception $e) {
            throw new Exception("Error al traer las mesas: " . $e->getMessage());
        }
    }

    public static function TraerMesa($id_mesa)
    {
        try {
            $objAccesoDatos = AccesoDatos::obtenerInstancia();
            $consulta = $objAccesoDatos->prepararConsulta("SELECT * from mesas WHERE id_mesa = :id_mesa");
            $consulta->bindValue(':id_mesa', $id_mesa, PDO::PARAM_INT);
            $consulta->execute();
            $empleado = $consulta->fetch(PDO::FETCH_ASSOC);
            return $empleado;
        } catch (Exception $e) {
            throw new Exception("Error al traer la mesa: " . $e->getMessage());
        }
    }
}
