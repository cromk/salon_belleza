<?php
header('Content-Type: application/json');
require_once __DIR__ . '/../config/Conexion.php';
require_once __DIR__ . '/../models/clienteModel.php';

if (session_status() == PHP_SESSION_NONE) session_start();

$con = new Conexion();
$db = $con->conectar();
$model = new clienteModel($db);

$action = $_POST['action'] ?? $_GET['action'] ?? '';

function requireRoles(array $roles)
{
    if (!isset($_SESSION['usuario'])) {
        echo json_encode(['success' => false, 'message' => 'Sesión no válida']);
        exit;
    }
    $role = (int)($_SESSION['usuario']['id_rol'] ?? 0);
    if (!in_array($role, $roles, true)) {
        echo json_encode(['success' => false, 'message' => 'No autorizado']);
        exit;
    }
}

switch ($action) {
    case 'create':
        requireRoles([1, 2]);
        $nombre = trim($_POST['nombre'] ?? '');
        $apellido = trim($_POST['apellido'] ?? '');
        $telefono = trim($_POST['telefono'] ?? '');
        $correo = trim($_POST['correo'] ?? '');

        if ($nombre === '') {
            echo json_encode(['success' => false, 'message' => 'El nombre es obligatorio']);
            exit;
        }
        if ($correo !== '' && !filter_var($correo, FILTER_VALIDATE_EMAIL)) {
            echo json_encode(['success' => false, 'message' => 'Correo inválido']);
            exit;
        }
        if ($correo !== '') {
            $exist = $model->findByCorreo($correo);
            if ($exist) {
                echo json_encode(['success' => false, 'message' => 'Ya existe un cliente registrado con ese correo']);
                exit;
            }
        }

        $res = $model->create($nombre, $apellido !== '' ? $apellido : null, $telefono !== '' ? $telefono : null, $correo !== '' ? $correo : null);
        if (is_int($res)) {
            echo json_encode(['success' => true, 'id' => $res]);
        } else {
            echo json_encode(['success' => false, 'message' => 'No se pudo registrar el cliente']);
        }
        break;

    case 'read':
        requireRoles([1, 2]);
        $includeInactive = isset($_GET['all']) && $_GET['all'] === '1';
        $data = $model->getAll($includeInactive);
        echo json_encode(['success' => true, 'data' => $data]);
        break;

    case 'update':
        requireRoles([1, 2]);
        $id = isset($_POST['id_cliente']) ? intval($_POST['id_cliente']) : 0;
        $nombre = trim($_POST['nombre'] ?? '');
        $apellido = trim($_POST['apellido'] ?? '');
        $telefono = trim($_POST['telefono'] ?? '');
        $correo = trim($_POST['correo'] ?? '');

        if ($id <= 0 || $nombre === '') {
            echo json_encode(['success' => false, 'message' => 'Datos inválidos']);
            exit;
        }
        if ($correo !== '' && !filter_var($correo, FILTER_VALIDATE_EMAIL)) {
            echo json_encode(['success' => false, 'message' => 'Correo inválido']);
            exit;
        }
        if ($correo !== '') {
            $exist = $model->findByCorreo($correo, $id);
            if ($exist) {
                echo json_encode(['success' => false, 'message' => 'El correo ya está asignado a otro cliente']);
                exit;
            }
        }

        $ok = $model->update($id, $nombre, $apellido !== '' ? $apellido : null, $telefono !== '' ? $telefono : null, $correo !== '' ? $correo : null);
        echo json_encode(['success' => $ok, 'message' => $ok ? 'Cliente actualizado' : 'No se pudo actualizar el cliente']);
        break;

    case 'setEstado':
        requireRoles([1, 2]);
        $id = isset($_POST['id_cliente']) ? intval($_POST['id_cliente']) : 0;
        $estado = $_POST['estado'] ?? 'Inactivo';
        if ($id <= 0) {
            echo json_encode(['success' => false, 'message' => 'Cliente inválido']);
            exit;
        }
        $ok = $model->setEstado($id, $estado === 'Activo' ? 'Activo' : 'Inactivo');
        echo json_encode(['success' => $ok, 'message' => $ok ? 'Estado actualizado' : 'No se pudo actualizar el estado']);
        break;

    case 'delete':
        requireRoles([1, 2]);
        $id = isset($_POST['id_cliente']) ? intval($_POST['id_cliente']) : 0;
        if ($id <= 0) {
            echo json_encode(['success' => false, 'message' => 'Cliente inválido']);
            exit;
        }
        if ($model->hasHistorial($id)) {
            echo json_encode([
                'success' => false,
                'message' => 'El cliente tiene historial de citas, desactívelo en lugar de eliminarlo.',
                'hasHistory' => true
            ]);
            exit;
        }
        $ok = $model->delete($id);
        echo json_encode(['success' => $ok, 'message' => $ok ? 'Cliente eliminado' : 'No se pudo eliminar el cliente']);
        break;

    default:
        echo json_encode(['success' => false, 'message' => 'Acción inválida']);
        break;
}