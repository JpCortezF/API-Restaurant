<?php
require './Model/Empleado.php';
require_once './Interfaces/IApiUsable.php';

class EmpleadoController implements IApiUsable
{
    public function TraerUno($request, $response, $args)
    {
        try {
            // Buscamos empleado por nombre
            $nombre = $args['nombre'];
            $empleado = Empleado::TraerEmpleado($nombre);
            $payload = json_encode($empleado);

            $response->getBody()->write($payload);
            return $response->withHeader('Content-Type', 'application/json');
        } catch (Exception $e) {
            return $response->withStatus(500)->write('Error interno del servidor');
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
        $id_rol = $parametros['id_rol'];

        // Creamos el Empleado
        $empleado = new Empleado();
        $empleado->nombre = $nombre;
        $empleado->id_rol = $id_rol;
        $empleado->NuevoEmpleado();

        $payload = json_encode(array("mensaje" => "Empleado creado con exito"));

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

        Empleado::ModificarEmpleado($empleado);

        $payload = json_encode(array("mensaje" => "Empleado modificado con exito"));

        $response->getBody()->write($payload);
        return $response->withHeader('Content-Type', 'application/json');
    }
}
