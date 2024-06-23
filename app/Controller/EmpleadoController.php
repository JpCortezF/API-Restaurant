<?php
require_once './Model/Empleado.php';
require_once './Interfaces/IApiUsable.php';

class EmpleadoController implements IApiUsable
{
    public function TraerUno($request, $response, $args)
    {
        try {
            // Buscamos empleado por usuario
            $usuario = $args['usuario'];
            $empleado = Empleado::TraerEmpleadoPorUsuario($usuario);
            $payload = json_encode($empleado);

            $response->getBody()->write($payload);
            return $response->withHeader('Content-Type', 'application/json');
        } catch (Exception $e) {
            $payload = json_encode(['mensaje' => 'Error interno del servidor']);
            $response->getBody()->write($payload);
            return $response->withStatus(500)->withHeader('Content-Type', 'application/json');
        }
    }

    public function TraerTodos($request, $response, $args)
    {
        $lista = Empleado::TraerEmpleados();
        $payload = json_encode(array("listaEmpleados" => $lista));

        $response->getBody()->write($payload);
        return $response->withHeader('Content-Type', 'application/json');
    }

    public function GuardarUno($request, $response, $args)
    {
        $parametros = $request->getParsedBody();

        $nombre = $parametros['nombre'];
        $usuario = $parametros['usuario'];
        $clave = $parametros['clave'];
        $id_rol = $parametros['id_rol'];
        $empleadoExistente = Empleado::TraerEmpleadoPorUsuario($usuario);

        if ($empleadoExistente) {
            $payload = json_encode(array("mensaje" => "El usuario ya existe"));
        } else {
            $empleado = new Empleado();
            $empleado->nombre = $nombre;
            $empleado->usuario = $usuario;
            $empleado->clave = $clave;
            $empleado->id_rol = $id_rol;
            $empleado->NuevoEmpleado();

            $payload = json_encode(array("mensaje" => "Empleado creado con exito"));
        }

        $response->getBody()->write($payload);
        return $response->withHeader('Content-Type', 'application/json');
    }

    public function BorrarUno($request, $response, $args)
    {
        $parametros = $request->getParsedBody();
        if (!isset($parametros['id_empleado'])) {
            $payload = json_encode(array("error" => "id_empleado no proporcionado"));
            $response->getBody()->write($payload);
            return $response->withHeader('Content-Type', 'application/json')->withStatus(400);
        }
        $id_empleado = $parametros['id_empleado'];
        Empleado::BorrarEmpleado($id_empleado);

        $payload = json_encode(array("mensaje" => "Empleado borrado con exito"));

        $response->getBody()->write($payload);
        return $response->withHeader('Content-Type', 'application/json');
    }

    public function ModificarUno($request, $response, $args)
    {
        $parametros = $request->getParsedBody();

        if (!isset($parametros['id_empleado'])) {
            $payload = json_encode(array("error" => "id_empleado no proporcionado"));
            $response->getBody()->write($payload);
            return $response->withHeader('Content-Type', 'application/json')->withStatus(400);
        }

        $empleado = Empleado::EmpleadoPorID($parametros['id_empleado']);
        if (isset($parametros['nombre'])) {
            $empleado->nombre = $parametros['nombre'];
        }
        if (isset($parametros['id_rol'])) {
            $empleado->id_rol = $parametros['id_rol'];
        }
        if (isset($parametros['usuario'])) {
            $empleado->usuario = $parametros['usuario'];
        }
        if (isset($parametros['clave'])) {
            $empleado->clave = $parametros['clave'];
        }

        Empleado::ModificarEmpleado($empleado);

        $payload = json_encode(array("mensaje" => "Empleado modificado con exito"));

        $response->getBody()->write($payload);
        return $response->withHeader('Content-Type', 'application/json');
    }

    public static function GuardarEmpleados($request, $response, $args)
    {
        $path = "empleados.csv";
        $arrayEmpleados = array();
        $empleados = Empleado::TraerEmpleados();

        foreach ($empleados as $e) {
            $empleado = array($e->id_empleado, $e->nombre, $e->usuario, $e->clave, $e->id_rol, $e->estado, $e->fecha_baja);
            $arrayEmpleados[] = $empleado;
        }

        $archivo = fopen($path, "w");
        if ($archivo === false) {
            $retorno = json_encode(array("mensaje" => "Error al abrir el archivo para escribir"));
            $response->getBody()->write($retorno);
            return $response->withStatus(500);
        }

        if (!empty($arrayEmpleados)) {
            $encabezado = array("id_empleado", "nombre", "usuario", "clave", "id_rol", "estado", "fecha_baja");
            fputcsv($archivo, $encabezado);
            foreach ($arrayEmpleados as $fila) {
                fputcsv($archivo, $fila);
            }
        }

        fclose($archivo);
        $retorno = json_encode(array("mensaje" => "Empleados guardados en CSV con éxito"));

        $response->getBody()->write($retorno);
        return $response;
    }

    public static function CargarEmpleados($request, $response, $args)
    {
        $path = "empleados.csv";
        $archivo = fopen($path, "r");

        if ($archivo === false) {
            $retorno = json_encode(array("mensaje" => "Error al abrir el archivo para leer"));
            $response->getBody()->write($retorno);
            return $response->withStatus(500);
        }

        $encabezado = fgets($archivo); // Leer la primera línea (encabezado)

        while (!feof($archivo)) {
            $linea = fgets($archivo);
            if ($linea === false || trim($linea) == "") {
                continue; // Saltear líneas vacías
            }

            $datos = str_getcsv($linea);
            if (count($datos) < 7) {
                continue; // Saltear líneas incompletas
            }

            $empleado = new Empleado();
            $empleado->id_empleado = $datos[0];
            $empleado->nombre = $datos[1];
            $empleado->usuario = $datos[2];
            $empleado->clave = $datos[3];
            $empleado->id_rol = $datos[4];
            $empleado->estado = $datos[5];
            $empleado->fecha_baja = $datos[6];
            $empleado->NuevoEmpleado();
        }

        fclose($archivo);

        $retorno = json_encode(array("mensaje" => "Empleados guardados en la base de datos con exito"));
        $response->getBody()->write($retorno);
        return $response;
    }
}
