<?php
header('Content-Type: application/json');
require_once __DIR__ . '/../config/Conexion.php';
require_once __DIR__ . '/../models/servicioModel.php';
require_once __DIR__ . '/../models/especificacionModel.php';

// Iniciar sesión para comprobaciones de rol
if (session_status() == PHP_SESSION_NONE) session_start();

$con = new Conexion();
$db = $con->conectar();
$model = new servicioModel($db);
$espModel = new especificacionModel($db);

$action = $_POST['action'] ?? $_GET['action'] ?? '';

switch ($action) {
    case 'create':
        // Verificar rol: solo administradores (id_rol == 1)
        if (!isset($_SESSION['usuario']) || (int)($_SESSION['usuario']['id_rol'] ?? 0) !== 1) {
            echo json_encode(['success' => false, 'message' => 'No autorizado']); exit;
        }
        $nombre = trim($_POST['nombre'] ?? '');
        $precio = $_POST['precio'] ?? null;
        $duracion = $_POST['duracion'] ?? null;
        $descripcion = trim($_POST['descripcion'] ?? '');

        // Validaciones simples
        if ($nombre === '') {
            echo json_encode(['success' => false, 'message' => 'El nombre es obligatorio']); exit;
        }
        if (!is_numeric($precio) || floatval($precio) < 0) {
            echo json_encode(['success' => false, 'message' => 'Precio inválido']); exit;
        }
        if (!is_numeric($duracion) || intval($duracion) <= 0) {
            echo json_encode(['success' => false, 'message' => 'Duración inválida']); exit;
        }

        $res = $model->create($nombre, number_format((float)$precio, 2, '.', ''), intval($duracion), $descripcion);
        if ($res) {
            echo json_encode(['success' => true, 'id' => $res]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Error al crear el servicio']);
        }
        break;

    case 'read':
        $onlyActive = true;
        if (isset($_GET['all']) && $_GET['all'] == '1') $onlyActive = false;
        $data = $model->getAll($onlyActive);
        // Adjuntar especificaciones (variaciones) a cada servicio
        foreach ($data as &$s) {
            $s['especificaciones'] = $espModel->getByService($s['id_servicio'], $onlyActive);
        }
        echo json_encode(['success' => true, 'data' => $data]);
        break;

    case 'addSpecification':
        // Verificar rol: solo administradores
        if (!isset($_SESSION['usuario']) || (int)($_SESSION['usuario']['id_rol'] ?? 0) !== 1) {
            echo json_encode(['success' => false, 'message' => 'No autorizado']); exit;
        }
        // Crear especificación: id_servicio, nombre, tipo, valor_precio, valor_tiempo, descripcion
        $id_servicio = isset($_POST['servicio_id']) ? intval($_POST['servicio_id']) : 0;
        $nombre = trim($_POST['nombre'] ?? '');
        $tipo = trim($_POST['tipo'] ?? 'opcional');
        $valor_precio = isset($_POST['valor_precio']) ? $_POST['valor_precio'] : 0.00;
        $valor_tiempo = isset($_POST['valor_tiempo']) ? intval($_POST['valor_tiempo']) : 0;
        $descripcion = trim($_POST['descripcion'] ?? '');

        if ($id_servicio <= 0 || $nombre === '') { echo json_encode(['success' => false, 'message' => 'Servicio y nombre obligatorios']); exit; }
        if (!is_numeric($valor_precio)) $valor_precio = 0.00;
        $res = $espModel->create($id_servicio, $nombre, $tipo, number_format((float)$valor_precio, 2, '.', ''), intval($valor_tiempo), $descripcion);
        if ($res) echo json_encode(['success' => true, 'id' => $res]); else echo json_encode(['success' => false, 'message' => 'Error creando especificación']);
        break;

    case 'getSpecifications':
        $id_servicio = isset($_GET['servicio_id']) ? intval($_GET['servicio_id']) : 0;
        if ($id_servicio <= 0) { echo json_encode(['success' => false, 'message' => 'Servicio inválido']); exit; }
        $vars = $espModel->getByService($id_servicio, true);
        echo json_encode(['success' => true, 'data' => $vars]);
        break;

    default:
        echo json_encode(['success' => false, 'message' => 'Acción inválida']);
        break;
}

?>
