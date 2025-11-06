<?php
header('Content-Type: application/json');
require_once __DIR__ . '/../config/Conexion.php';
require_once __DIR__ . '/../models/estilistaModel.php';

if (session_status() == PHP_SESSION_NONE) session_start();

$con = new Conexion();
$db = $con->conectar();
$model = new estilistaModel($db);

$action = $_POST['action'] ?? $_GET['action'] ?? '';

switch ($action) {
    case 'createEstilista':
        // Solo admin
        if (!isset($_SESSION['usuario']) || (int)($_SESSION['usuario']['id_rol'] ?? 0) !== 1) {
            echo json_encode(['success' => false, 'message' => 'No autorizado']); exit;
        }
        $id_usuario = isset($_POST['id_usuario']) ? intval($_POST['id_usuario']) : 0;
        $especialidad = trim($_POST['especialidad'] ?? '');
        $experiencia = isset($_POST['experiencia']) ? intval($_POST['experiencia']) : 0;
        $disponible = trim($_POST['disponible'] ?? 'Sí');
        if ($id_usuario <= 0) { echo json_encode(['success' => false, 'message' => 'Usuario inválido']); exit; }
        $res = $model->create($id_usuario, $especialidad, $experiencia, $disponible);
        if ($res) {
            // Si se enviaron servicios, asociarlos
            $services = $_POST['services'] ?? null;
            if ($services) {
                if (!is_array($services)) {
                    $services = json_decode($services, true) ?? [];
                }
                if (is_array($services) && !empty($services)) {
                    $model->assignServices($res, $services);
                }
            }
            echo json_encode(['success' => true, 'id' => (int)$res]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Error creando estilista']);
        }
        break;

    case 'readEstilistas':
        $data = $model->getAll();
        // Adjuntar servicios por estilista
        foreach ($data as &$e) {
            $e['servicios'] = $model->getServices($e['id_estilista']);
        }
        echo json_encode(['success' => true, 'data' => $data]);
        break;

    case 'assignServices':
        // Solo admin
        if (!isset($_SESSION['usuario']) || (int)($_SESSION['usuario']['id_rol'] ?? 0) !== 1) {
            echo json_encode(['success' => false, 'message' => 'No autorizado']); exit;
        }
        $id_estilista = isset($_POST['id_estilista']) ? intval($_POST['id_estilista']) : 0;
        $services = isset($_POST['services']) ? $_POST['services'] : [];
        if (!is_array($services)) $services = json_decode($services, true) ?? [];
        $ok = $model->assignServices($id_estilista, $services);
        echo json_encode(['success' => (bool)$ok]);
        break;

    case 'getUsuariosByRole':
        // Devuelve usuarios por rol (por defecto estilista id=3)
        $role = isset($_GET['role']) ? intval($_GET['role']) : 3;
        try {
            $stmt = $db->prepare("SELECT id_usuario, nombre, apellido FROM usuarios WHERE id_rol = :r AND estado = 'Activo' ORDER BY nombre");
            $stmt->bindParam(':r', $role, PDO::PARAM_INT);
            $stmt->execute();
            $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
            echo json_encode(['success' => true, 'data' => $rows]);
        } catch (Exception $e) {
            echo json_encode(['success' => false, 'message' => 'Error leyendo usuarios']);
        }
        break;

    case 'getRoles':
        try {
            $stmt = $db->prepare("SELECT id_rol, nombre FROM roles ORDER BY id_rol");
            $stmt->execute();
            $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
            echo json_encode(['success' => true, 'data' => $rows]);
        } catch (Exception $e) {
            echo json_encode(['success' => false, 'message' => 'Error leyendo roles']);
        }
        break;

    case 'getServices':
        try {
            $stmt = $db->prepare("SELECT id_servicio, nombre FROM servicios WHERE estado = 'Activo' ORDER BY nombre");
            $stmt->execute();
            $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
            echo json_encode(['success' => true, 'data' => $rows]);
        } catch (Exception $e) {
            echo json_encode(['success' => false, 'message' => 'Error leyendo servicios']);
        }
        break;

    case 'readPersonal':
        try {
            $stmt = $db->prepare("SELECT u.id_usuario, u.nombre, u.apellido, u.correo, u.telefono, u.usuario, r.nombre as rol FROM usuarios u LEFT JOIN roles r ON r.id_rol = u.id_rol ORDER BY u.nombre");
            $stmt->execute();
            $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
            echo json_encode(['success' => true, 'data' => $rows]);
        } catch (Exception $e) {
            echo json_encode(['success' => false, 'message' => 'Error leyendo personal']);
        }
        break;

    default:
        echo json_encode(['success' => false, 'message' => 'Acción inválida']);
        break;
}

?>