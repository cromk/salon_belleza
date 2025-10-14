<?php
require_once "../models/usuarioModel.php";
require_once "../config/Conexion.php";

header('Content-Type: application/json');

$con = new Conexion();
$cn = $con->conectar();
$modelo = new usuarioModel($cn);

if ($_POST['action'] == 'login') {
    $usuario = trim($_POST['usuario']);
    $password = trim($_POST['password']);

    $res = $modelo->verificarUsuario($usuario, $password);

    if ($res) {
        session_start();
        $_SESSION['usuario'] = $res;
        echo json_encode(["success" => true]);
    } else {
        echo json_encode(["success" => false, "message" => "Usuario o contraseña incorrectos"]);
    }
}
?>