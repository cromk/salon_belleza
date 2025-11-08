<?php
require_once "../models/usuarioModel.php";
require_once "../config/Conexion.php";
require_once "../config/logger.php";

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
            // Si el usuario es Estilista (id_rol == 3), verificar que exista en tabla estilistas
            $isEstilista = isset($res['id_rol']) && intval($res['id_rol']) === 3;
            if ($isEstilista) {
                try {
                    $chk = $cn->prepare("SELECT id_estilista FROM estilistas WHERE id_usuario = :uid LIMIT 1");
                    $chk->bindParam(':uid', $res['id_usuario'], PDO::PARAM_INT);
                    $chk->execute();
                    $found = $chk->fetch(PDO::FETCH_ASSOC);
                    if (!$found) {
                        echo json_encode(["success" => false, "message" => "Acceso denegado: El usuario existe pero aun no tiene permisos para ingresar consulte con su administrador."]);
                        break;
                    }
                } catch (Exception $e) {
                    // en caso de error en la comprobación, negar acceso por seguridad
                    error_log('Login estilista check error: ' . $e->getMessage());
                    echo json_encode(["success" => false, "message" => "Error verificando permisos. Intente más tarde."]);
                    break;
                }
            }
            session_start();
            $_SESSION['usuario'] = $res;
            echo json_encode(["success" => true]);
        } else {
            echo json_encode(["success" => false, "message" => "Usuario o contraseña incorrectos"]);
        }
        break;

    case 'create':
    $nombre = $_POST['nombre'] ?? null;
    $apellido = $_POST['apellido'] ?? null;
    $correo = isset($_POST['correo']) ? trim($_POST['correo']) : null;
    // normalizar correo a minúsculas y eliminar espacios invisibles
    if ($correo) {
        $correo = mb_strtolower($correo);
        $correo = trim($correo);
        // Si el usuario envía solo la parte local (sin @), añadir el dominio por defecto
        if (strpos($correo, '@') === false) {
            $correo = $correo . '@salonbelleza.com';
        }
    }
    $telefono = $_POST['telefono'] ?? null;
    $usuario = isset($_POST['usuario']) ? trim($_POST['usuario']) : null;
    $clave = $_POST['clave'] ?? null;
        $rol = isset($_POST['id_rol']) ? intval($_POST['id_rol']) : ($_POST['rol'] ?? null);

        // Validaciones básicas
        if (!$nombre || !$correo || !$usuario || !$clave || !$rol) {
            echo json_encode(["success" => false, "message" => "Faltan datos obligatorios"]);
            break;
        }

        // Comprobar duplicados (correo o usuario) usando normalización en SQL para evitar diferencias de case/espacios
        try {
            $chk = $cn->prepare("SELECT usuario, correo FROM usuarios WHERE LOWER(TRIM(correo)) = :correo OR TRIM(usuario) = :usuario LIMIT 1");
            $correo_check = mb_strtolower(trim($correo));
            $usuario_check = trim($usuario);
            $chk->bindParam(':correo', $correo_check);
            $chk->bindParam(':usuario', $usuario_check);
            $chk->execute();
            $found = $chk->fetch(PDO::FETCH_ASSOC);
            if ($found) {
                // Comparaciones normalizadas
                if (!empty($found['correo']) && mb_strtolower(trim($found['correo'])) === $correo_check) {
                    echo json_encode(["success" => false, "message" => "El correo ya está registrado"]);
                    break;
                }
                if (!empty($found['usuario']) && trim($found['usuario']) === $usuario_check) {
                    echo json_encode(["success" => false, "message" => "El nombre de usuario ya está en uso"]);
                    break;
                }
                // genérico
                echo json_encode(["success" => false, "message" => "Ya existe un usuario con esos datos"]);
                break;
            }
        } catch (Exception $e) {
            // si falla la comprobación, continuar e intentar crear (se mostrará el error real)
        }

        $ok = $modelo->create($nombre, $apellido, $correo, $telefono, $usuario, $clave, $rol);
        if (is_numeric($ok)) {
            // Registrar intento exitoso
            log_change((int)$ok, 'create_usuario', [
                'input' => ['nombre'=>$nombre,'apellido'=>$apellido,'correo'=>$correo,'telefono'=>$telefono,'usuario'=>$usuario,'id_rol'=>$rol],
                'result' => 'created',
            ]);
            echo json_encode(["success" => true, "message" => "Usuario creado correctamente", "id_usuario" => (int)$ok]);
        } else if (is_array($ok) && isset($ok['error'])) {
            // Mapear errores de integridad (duplicate key) a mensajes amigables
            $err = $ok['error'];
            $errInfo = $ok['errorInfo'] ?? null;
            // Si tenemos errorInfo (PDO), comprobar el código MySQL
            if (is_array($errInfo) && isset($errInfo[1]) && intval($errInfo[1]) === 1062) {
                // Mensaje típico: "Duplicate entry 'value' for key 'index_name'"
                $msg = $errInfo[2] ?? $err;
                if (stripos($msg, 'correo') !== false) {
                    echo json_encode(["success" => false, "message" => "El correo ya está registrado"]);
                    break;
                }
                if (stripos($msg, 'usuario') !== false) {
                    echo json_encode(["success" => false, "message" => "El nombre de usuario ya está en uso"]);
                    break;
                }
                // si no sabemos qué índice, devolver mensaje genérico de duplicado
                echo json_encode(["success" => false, "message" => "Ya existe un registro con esos datos (duplicado)"]);
                break;
            }
            // Registrar intento fallido
            log_change(null, 'create_usuario_failed', [
                'input' => ['nombre'=>$nombre,'apellido'=>$apellido,'correo'=>$correo,'telefono'=>$telefono,'usuario'=>$usuario,'id_rol'=>$rol],
                'error' => $err,
                'errorInfo' => $errInfo,
            ]);
            // si no tenemos errorInfo o no es duplicado, devolver detalle (temporal para debug)
            echo json_encode(["success" => false, "message" => "Error al crear usuario: " . $err]);
        } else {
            log_change(null, 'create_usuario_failed', [
                'input' => ['nombre'=>$nombre,'apellido'=>$apellido,'correo'=>$correo,'telefono'=>$telefono,'usuario'=>$usuario,'id_rol'=>$rol],
                'error' => 'unknown',
            ]);
            echo json_encode(["success" => false, "message" => "Error al crear usuario"]);
        }
        break;

    default:
        echo json_encode(["success" => false, "message" => "Funcion no valida"]);
        break;
}
?>