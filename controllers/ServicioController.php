<?php
header('Content-Type: application/json');
require_once __DIR__ . '/../config/Conexion.php';
require_once __DIR__ . '/../models/servicioModel.php';

$con = new Conexion();
$db = $con->conectar();
$model = new servicioModel($db);

$action = $_POST['action'] ?? $_GET['action'] ?? '';

switch ($action) {
    case 'create':
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
        echo json_encode(['success' => true, 'data' => $data]);
        break;

    default:
        echo json_encode(['success' => false, 'message' => 'Acción inválida']);
        break;
}

?>
