<?php
require_once './Model/Empleado.php';
require_once './Utilities/AutentificadorJWT.php';

class LoginController
{

    public function Login($request, $response, $args)
    {
        $parametros = $request->getParsedBody();

        $usuario = $parametros['usuario'];
        $clave = $parametros['clave'];
        $empleado = Empleado::UsuarioLogin($usuario, $clave);
        if ($empleado) {
            $datos = array('id_empleado' => $empleado->id_empleado, 'nombre' => $empleado->nombre, 'id_rol' => $empleado->id_rol);
            $token = AutentificadorJWT::CrearToken($datos);
            $payload = json_encode(array('jwt' => $token));
        } else {
            $payload = json_encode(array('error' => 'Usuario o contraseña incorrectos'));
        }

        $response->getBody()->write($payload);
        return $response->withHeader('Content-Type', 'application/json');
    }
}
