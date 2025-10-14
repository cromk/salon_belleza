<?php
require_once "../models/usuarioModel.php";
require_once "../config/Conexion.php";

header('Content-Type: application/json');

$con = new Conexion();
$cn = $con->conectar();
$modelo = new usuarioModel($cn);

$action = $_POST['action'] ?? '';

switch($action){
    case 'login':
        $usuario = trim($_POST['usuario']);
        $password = trim($_POST['password']);

        $res = $modelo->verificarUsuario($usuario, $password);

        if ($res) {
            session_start();
            $_SESSION['usuario'] = $res;
            echo json_encode(["success" => true]);
        } else
            echo json_encode(["success" => false, "message" => "Usuario o contraseña incorrectos"]);
        break;

    case 'create':
        $nombre = $_POST['nombre'] ?? null;
        $apellido = $_POST['apellido'] ?? null;
        $correo = $_POST['correo'] ?? null;
        $telefono = $_POST['telefono'] ?? null;
        $usuario = $_POST['usuario'] ?? null;
        $clave = $_POST['clave'] ?? null;
        $rol = $_POST['rol'] ?? null;
        $ok = $model->create($nombre,$apellido,$correo,$telefono,$usuario,$clave,$rol);
        echo json_encode(["success"=>$ok===true, "msg"=>$ok===true?"Política creada":"Error al crear"]);
        break;

    default:
        echo json_encode(["success" => false, "message" => "Funcion no valida"]);
        break
}
?>