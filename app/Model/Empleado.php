<?php
class Empleado
{
    public $id_empleado;
    public $nombre;
    public $clave;
    public $id_rol;
    public $estado;

    public function NuevoEmpleado()
    {
        $objAccesoDatos = AccesoDatos::obtenerInstancia();
        $consulta = $objAccesoDatos->prepararConsulta("INSERT INTO empleados (nombre, clave, id_rol, estado) VALUES (:nombre, :clave, :id_rol, :estado)");
        $claveHash = password_hash($this->clave, PASSWORD_DEFAULT);
        $consulta->bindValue(':nombre', $this->nombre, PDO::PARAM_STR);
        $consulta->bindValue(':clave', $claveHash);
        $consulta->bindValue(':id_rol', $this->id_rol, PDO::PARAM_STR);
        $consulta->bindValue(':estado', 'activo', PDO::PARAM_STR);
        $consulta->execute();

        return $objAccesoDatos->obtenerUltimoId();
    }

    public static function TraerEmpleados()
    {
        try {
            $objAccesoDatos = AccesoDatos::obtenerInstancia();
            $consulta = $objAccesoDatos->prepararConsulta("SELECT e.id_empleado, e.nombre, e.clave, r.nombre AS rol, e.estado, e.fecha_baja
                FROM empleados e
                INNER JOIN roles r ON e.id_rol = r.id_rol");
            $consulta->execute();
            $empleados = $consulta->fetchAll(PDO::FETCH_OBJ);
            return $empleados;
        } catch (Exception $e) {
            throw new Exception("Error al traer empleados: " . $e->getMessage());
        }
    }

    public static function TraerEmpleado($nombre)
    {
        try {
            $objAccesoDatos = AccesoDatos::obtenerInstancia();
            $consulta = $objAccesoDatos->prepararConsulta("SELECT e.id_empleado, e.nombre, e.clave, r.nombre AS rol, e.estado, e.fecha_baja
            FROM empleados e
            INNER JOIN roles r ON e.id_rol = r.id_rol
            WHERE e.nombre = :nombre");
            $consulta->bindValue(':nombre', $nombre, PDO::PARAM_STR);
            $consulta->execute();
            $empleado = $consulta->fetch(PDO::FETCH_ASSOC);
            return $empleado;
        } catch (Exception $e) {
            throw new Exception("Error al traer empleado: " . $e->getMessage());
        }
    }

    public static function EmpleadoPorID($id_empleado)
    {
        $objAccesoDatos = AccesoDatos::obtenerInstancia();
        $consulta = $objAccesoDatos->prepararConsulta("SELECT id_empleado, nombre, id_rol
        FROM empleados WHERE id_empleado = :id_empleado");
        $consulta->bindValue(':id_empleado', $id_empleado, PDO::PARAM_INT);
        $consulta->execute();

        return $consulta->fetchObject('Empleado');
    }

    public static function ModificarEmpleado($empleado)
    {
        $objAccesoDatos = AccesoDatos::obtenerInstancia();
        $consulta = $objAccesoDatos->prepararConsulta("UPDATE empleados SET nombre = :nombre, clave = :clave, id_rol = :id_rol WHERE id_empleado = :id_empleado");
        $consulta->bindValue(':id_empleado', $empleado->id_empleado, PDO::PARAM_INT);
        $consulta->bindValue(':nombre', $empleado->nombre, PDO::PARAM_STR);
        $consulta->bindValue(':clave', $empleado->clave, PDO::PARAM_STR);
        $consulta->bindValue(':id_rol', $empleado->id_rol, PDO::PARAM_STR);

        $consulta->execute();
    }

    public static function BorrarEmpleado($id_empleado)
    {
        $objAccesoDato = AccesoDatos::obtenerInstancia();
        $consulta = $objAccesoDato->prepararConsulta("UPDATE empleados SET estado = inactivo, fecha_baja = :fecha_baja WHERE id_empleado = :id_empleado");
        $fecha = new DateTime(date("d-m-Y"));
        $consulta->bindValue(':id_empleado', $id_empleado, PDO::PARAM_INT);
        $consulta->bindValue(':fecha_baja', date_format($fecha, 'Y-m-d H:i:s'));
        $consulta->execute();
    }
}
