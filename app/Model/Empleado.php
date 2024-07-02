<?php
require_once("./utilities/CrearPDF.php");
class Empleado
{
    public $id_empleado;
    public $nombre;
    public $usuario;
    public $clave;
    public $id_rol;
    public $estado;
    public $fecha_baja;

    public function NuevoEmpleado()
    {
        $objAccesoDatos = AccesoDatos::obtenerInstancia();
        $consulta = $objAccesoDatos->prepararConsulta("INSERT INTO empleados (nombre, usuario, clave, id_rol) VALUES (:nombre, :usuario, :clave, :id_rol)");
        $claveHash = password_hash($this->clave, PASSWORD_DEFAULT);
        $consulta->bindValue(':nombre', $this->nombre, PDO::PARAM_STR);
        $consulta->bindValue(':usuario', $this->usuario, PDO::PARAM_STR);
        $consulta->bindValue(':clave', $claveHash);
        $consulta->bindValue(':id_rol', $this->id_rol, PDO::PARAM_STR);
        $consulta->execute();

        return $objAccesoDatos->obtenerUltimoId();
    }

    public static function TraerEmpleados()
    {
        try {
            $objAccesoDatos = AccesoDatos::obtenerInstancia();
            $consulta = $objAccesoDatos->prepararConsulta("SELECT e.id_empleado, e.nombre, e.usuario, e.clave, e.id_rol, r.nombre AS rol, e.estado, e.fecha_baja
            FROM empleados e
            INNER JOIN roles r ON e.id_rol = r.id_rol");
            $consulta->execute();
            $empleados = $consulta->fetchAll(PDO::FETCH_OBJ);
            return $empleados;
        } catch (Exception $e) {
            throw new Exception("Error al traer empleados: " . $e->getMessage());
        }
    }

    public static function TraerEmpleadoPorUsuario($usuario)
    {
        $objAccesoDatos = AccesoDatos::obtenerInstancia();
        $consulta = $objAccesoDatos->prepararConsulta("SELECT e.id_empleado, e.nombre, e.usuario, e.clave, e.id_rol, r.nombre AS rol, e.estado, e.fecha_baja
            FROM empleados e
            INNER JOIN roles r ON e.id_rol = r.id_rol
            WHERE e.usuario = :usuario");
        $consulta->bindValue(':usuario', $usuario, PDO::PARAM_STR);
        $consulta->execute();

        return $consulta->fetch(PDO::FETCH_ASSOC);
    }

    public static function EmpleadoPorID($id_empleado)
    {
        $objAccesoDatos = AccesoDatos::obtenerInstancia();
        $consulta = $objAccesoDatos->prepararConsulta("SELECT * FROM empleados WHERE id_empleado = :id_empleado");
        $consulta->bindValue(':id_empleado', $id_empleado, PDO::PARAM_INT);
        $consulta->execute();

        return $consulta->fetchObject('Empleado');
    }

    public static function ModificarEmpleado($empleado)
    {
        $objAccesoDatos = AccesoDatos::obtenerInstancia();
        $consulta = $objAccesoDatos->prepararConsulta("UPDATE empleados SET nombre = :nombre, usuario = :usuario, clave = :clave, id_rol = :id_rol WHERE id_empleado = :id_empleado");
        $claveHash = password_hash($empleado->clave, PASSWORD_DEFAULT);
        $consulta->bindValue(':id_empleado', $empleado->id_empleado, PDO::PARAM_INT);
        $consulta->bindValue(':nombre', $empleado->nombre, PDO::PARAM_STR);
        $consulta->bindValue(':usuario', $empleado->usuario, PDO::PARAM_STR);
        $consulta->bindValue(':clave', $claveHash, PDO::PARAM_STR);
        $consulta->bindValue(':id_rol', $empleado->id_rol, PDO::PARAM_STR);

        $consulta->execute();
    }

    public static function BorrarEmpleado($id_empleado)
    {
        $objAccesoDato = AccesoDatos::obtenerInstancia();
        $consulta = $objAccesoDato->prepararConsulta("UPDATE empleados SET estado = '0', fecha_baja = :fecha_baja WHERE id_empleado = :id_empleado");
        $fecha = new DateTime(date("d-m-Y"));
        $consulta->bindValue(':id_empleado', $id_empleado, PDO::PARAM_INT);
        $consulta->bindValue(':fecha_baja', date_format($fecha, 'Y-m-d H:i:s'));
        $consulta->execute();
    }

    public static function UsuarioLogin($usuario, $clave)
    {
        $objetoAccesoDato = AccesoDatos::obtenerInstancia();
        $consulta = $objetoAccesoDato->prepararConsulta("SELECT * FROM empleados WHERE usuario = :usuario AND estado = 1");
        $consulta->bindValue(1, $usuario, PDO::PARAM_STR);
        $consulta->execute();
        $empleado = $consulta->fetchObject();
        if ($empleado && password_verify($clave, $empleado->clave)) {
            return $empleado;
        }

        return null;
    }

    public static function EsMozo($id_empleadoMozo)
    {
        $objAccesoDatos = AccesoDatos::obtenerInstancia();
        $consulta = $objAccesoDatos->prepararConsulta("SELECT * FROM empleados WHERE id_empleado = :id_empleado AND id_rol = 1");
        $consulta->bindValue(':id_empleado', $id_empleadoMozo, PDO::PARAM_INT);
        $consulta->execute();

        return $consulta->fetchObject('Empleado');
    }

    public static function ExportarPDF($path = "./empleados.pdf")
    {
        $pdf = new PDF();
        $pdf->AddPage();

        $empleados = Empleado::TraerEmpleados();

        // Agregar objetos al PDF
        foreach ($empleados as $empleado) {
            $pdf->ChapterTitle($empleado->id_empleado);
            $pdf->ChapterBody("Nombre: " . $empleado->nombre);
            $pdf->ChapterBody("Rol: " . $empleado->rol);
            $pdf->Ln();
        }

        $pdf->Output($path, 'F');
    }
}
