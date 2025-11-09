<?php
// Devolver todas las respuestas en formato JSON para facilitar el consumo desde JavaScript
header('Content-Type: application/json');

// Importar dependencias necesarias
require_once __DIR__ . '/../config/Conexion.php';
require_once __DIR__ . '/../models/promocionModel.php';
require_once __DIR__ . '/../config/logger.php';

// Iniciar sesión si aún no está iniciada para validar el rol del usuario
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Instanciar la conexión y el modelo reutilizando las clases existentes
$con = new Conexion();
$db = $con->conectar();
$model = new promocionModel($db);

// Determinar la acción solicitada mediante POST o GET
$action = $_POST['action'] ?? $_GET['action'] ?? '';

switch ($action) {
    case 'create':
        // Solo los administradores (rol 1) pueden crear promociones o combos
        if (!isset($_SESSION['usuario']) || (int)($_SESSION['usuario']['id_rol'] ?? 0) !== 1) {
            echo json_encode(['success' => false, 'message' => 'No autorizado']);
            exit;
        }

        // Recopilar parámetros enviados desde el formulario
        $nombre = trim($_POST['nombre'] ?? '');
        $tipo = trim($_POST['tipo'] ?? '');
        $descripcion = trim($_POST['descripcion'] ?? '');
        $precio = $_POST['precio'] ?? null;

        // Validaciones básicas para asegurar datos coherentes
        if ($nombre === '') {
            echo json_encode(['success' => false, 'message' => 'El nombre es obligatorio']);
            exit;
        }
        $tipoPermitido = ['Promoción', 'Combo'];
        if (!in_array($tipo, $tipoPermitido, true)) {
            echo json_encode(['success' => false, 'message' => 'Tipo inválido']);
            exit;
        }
        if (!is_numeric($precio) || floatval($precio) < 0) {
            echo json_encode(['success' => false, 'message' => 'Precio inválido']);
            exit;
        }

        // Formatear valores numéricos antes de almacenarlos
        $precio = number_format((float) $precio, 2, '.', '');

        // Invocar al modelo para crear el registro
        $id = $model->create($nombre, $tipo, $descripcion, $precio);
        if ($id) {
            // Registrar el evento en el log para trazabilidad
            $userId = $_SESSION['usuario']['id_usuario'] ?? 0;
            log_change($userId, 'create_promo', ['id' => $id, 'nombre' => $nombre]);
            echo json_encode(['success' => true, 'id' => $id]);
        } else {
            echo json_encode(['success' => false, 'message' => 'No se pudo crear la promoción']);
        }
        break;

    case 'read':
        // Por defecto solo se devuelven activos; si se solicita "all" debe ser administrador
        $onlyActive = true;
        if (isset($_GET['all']) && $_GET['all'] === '1') {
            if (!isset($_SESSION['usuario']) || (int)($_SESSION['usuario']['id_rol'] ?? 0) !== 1) {
                echo json_encode(['success' => false, 'message' => 'No autorizado']);
                exit;
            }
            $onlyActive = false;
        }
        $data = $model->getAll($onlyActive);
        echo json_encode(['success' => true, 'data' => $data]);
        break;

    case 'update':
        // Actualizar promociones está limitado a administradores
        if (!isset($_SESSION['usuario']) || (int)($_SESSION['usuario']['id_rol'] ?? 0) !== 1) {
            echo json_encode(['success' => false, 'message' => 'No autorizado']);
            exit;
        }

        $id = isset($_POST['id_promocion']) ? intval($_POST['id_promocion']) : 0;
        $nombre = trim($_POST['nombre'] ?? '');
        $tipo = trim($_POST['tipo'] ?? '');
        $descripcion = trim($_POST['descripcion'] ?? '');
        $precio = $_POST['precio'] ?? null;

        if ($id <= 0 || $nombre === '') {
            echo json_encode(['success' => false, 'message' => 'Datos incompletos']);
            exit;
        }
        $tipoPermitido = ['Promoción', 'Combo'];
        if (!in_array($tipo, $tipoPermitido, true)) {
            echo json_encode(['success' => false, 'message' => 'Tipo inválido']);
            exit;
        }
        if (!is_numeric($precio) || floatval($precio) < 0) {
            echo json_encode(['success' => false, 'message' => 'Precio inválido']);
            exit;
        }

        $precio = number_format((float) $precio, 2, '.', '');
        $old = $model->getById($id);
        $ok = $model->update($id, $nombre, $tipo, $descripcion, $precio);
        if ($ok) {
            $userId = $_SESSION['usuario']['id_usuario'] ?? 0;
            log_change($userId, 'update_promo', ['id' => $id, 'old' => $old, 'new' => ['nombre' => $nombre, 'tipo' => $tipo, 'precio' => $precio]]);
            echo json_encode(['success' => true]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Error al actualizar la promoción']);
        }
        break;

    case 'changeStatus':
        // Permitir activar o desactivar registros según el estado recibido
        if (!isset($_SESSION['usuario']) || (int)($_SESSION['usuario']['id_rol'] ?? 0) !== 1) {
            echo json_encode(['success' => false, 'message' => 'No autorizado']);
            exit;
        }

        $id = isset($_POST['id_promocion']) ? intval($_POST['id_promocion']) : 0;
        $estado = $_POST['estado'] ?? 'Inactivo';
        if ($id <= 0 || !in_array($estado, ['Activo', 'Inactivo'], true)) {
            echo json_encode(['success' => false, 'message' => 'Parámetros inválidos']);
            exit;
        }
        $old = $model->getById($id);
        $ok = $model->setEstado($id, $estado);
        if ($ok) {
            $userId = $_SESSION['usuario']['id_usuario'] ?? 0;
            log_change($userId, 'status_promo', ['id' => $id, 'old_estado' => $old['estado'] ?? null, 'nuevo_estado' => $estado]);
            echo json_encode(['success' => true]);
        } else {
            echo json_encode(['success' => false, 'message' => 'No se pudo actualizar el estado']);
        }
        break;

    case 'delete':
        // El borrado será una desactivación lógica para preservar histórico
        if (!isset($_SESSION['usuario']) || (int)($_SESSION['usuario']['id_rol'] ?? 0) !== 1) {
            echo json_encode(['success' => false, 'message' => 'No autorizado']);
            exit;
        }

        $id = isset($_POST['id_promocion']) ? intval($_POST['id_promocion']) : 0;
        if ($id <= 0) {
            echo json_encode(['success' => false, 'message' => 'Identificador inválido']);
            exit;
        }
        $old = $model->getById($id);
        $ok = $model->setEstado($id, 'Inactivo');
        if ($ok) {
            $userId = $_SESSION['usuario']['id_usuario'] ?? 0;
            log_change($userId, 'delete_promo', ['id' => $id, 'old' => $old]);
            echo json_encode(['success' => true]);
        } else {
            echo json_encode(['success' => false, 'message' => 'No se pudo eliminar la promoción']);
        }
        break;

    default:
        // Acción desconocida: informar al cliente
        echo json_encode(['success' => false, 'message' => 'Acción no definida']);
        break;
}