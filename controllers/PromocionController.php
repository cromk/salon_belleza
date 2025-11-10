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

/**
 * Interpretar el identificador compuesto recibido desde el cliente.
 *
 * @param string $raw Identificador con formato "fuente:id".
 * @return array{0:?string,1:?int} Par [fuente, id] o [null, null] si no es válido.
 */
function parseIdentifier($raw) {
    if (!is_string($raw) || strpos($raw, ':') === false) {
        return [null, null];
    }
    [$source, $id] = explode(':', $raw, 2);
    $source = strtolower(trim($source));
    $id = (int) $id;
    if (!in_array($source, ['combo', 'oferta'], true) || $id <= 0) {
        return [null, null];
    }
    return [$source, $id];
}

/**
 * Obtener el tipo legible (Promoción/Combo) a partir de la fuente almacenada.
 */
function typeFromSource($source)
{
    return $source === 'combo' ? 'Combo' : 'Promoción';
}

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

/**
 * Interpretar el identificador compuesto recibido desde el cliente.
 *
 * @param string $raw Identificador con formato "fuente:id".
 * @return array{0:?string,1:?int} Par [fuente, id] o [null, null] si no es válido.
 */
function parseIdentifier($raw) {
    if (!is_string($raw) || strpos($raw, ':') === false) {
        return [null, null];
    }
    [$source, $id] = explode(':', $raw, 2);
    $source = strtolower(trim($source));
    $id = (int) $id;
    if (!in_array($source, ['combo', 'oferta'], true) || $id <= 0) {
        return [null, null];
    }
    return [$source, $id];
}

/**
 * Obtener el tipo legible (Promoción/Combo) a partir de la fuente almacenada.
 */
function typeFromSource($source)
{
    return $source === 'combo' ? 'Combo' : 'Promoción';
}

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
        if (!is_numeric($precio)) {
            echo json_encode(['success' => false, 'message' => 'Valor numérico inválido']);
            exit;
        }

        $precio = (float) $precio;
        if ($tipo === 'Promoción') {
            if ($precio < 0 || $precio > 100) {
                echo json_encode(['success' => false, 'message' => 'El descuento debe estar entre 0 y 100%.']);
                exit;
            }
        } else {
            if ($precio < 0) {
                echo json_encode(['success' => false, 'message' => 'El precio no puede ser negativo.']);
                exit;
            }
        }

        // Invocar al modelo para crear el registro
        $created = $model->create($nombre, $tipo, $descripcion, $precio);
        if ($created) {
            // Registrar el evento en el log para trazabilidad
            $userId = $_SESSION['usuario']['id_usuario'] ?? 0;
            log_change($userId, 'create_promo', ['id' => $created['identifier'], 'nombre' => $nombre, 'tipo' => $tipo]);
            echo json_encode(['success' => true, 'id' => $created['identifier']]);
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

        [$source, $id] = parseIdentifier($_POST['id_promocion'] ?? '');
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
        $tipoEsperado = typeFromSource($source);
        if ($tipo !== $tipoEsperado) {
            echo json_encode(['success' => false, 'message' => 'No se puede cambiar el tipo del registro.']);
            exit;
        }

        if (!is_numeric($precio)) {
            echo json_encode(['success' => false, 'message' => 'Valor numérico inválido']);
            exit;
        }

        $precio = (float) $precio;
        if ($tipo === 'Promoción') {
            if ($precio < 0 || $precio > 100) {
                echo json_encode(['success' => false, 'message' => 'El descuento debe estar entre 0 y 100%.']);
                exit;
            }
        } else {
            if ($precio < 0) {
                echo json_encode(['success' => false, 'message' => 'El precio no puede ser negativo.']);
                exit;
            }
        }

        $old = $model->getById($source, $id);
        $ok = $model->update($source, $id, $nombre, $descripcion, $precio);
        if ($ok) {
            $userId = $_SESSION['usuario']['id_usuario'] ?? 0;
            log_change($userId, 'update_promo', [
                'id' => sprintf('%s:%d', $source, $id),
                'old' => $old,
                'new' => ['nombre' => $nombre, 'tipo' => $tipo, 'precio' => $precio, 'descripcion' => $descripcion],
            ]);
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

        [$source, $id] = parseIdentifier($_POST['id_promocion'] ?? '');
        $estado = $_POST['estado'] ?? 'Inactivo';
        if (!$source || $id <= 0 || !in_array($estado, ['Activo', 'Inactivo'], true)) {
            echo json_encode(['success' => false, 'message' => 'Parámetros inválidos']);
            exit;
        }
        $old = $model->getById($source, $id);
        $ok = $model->setEstado($source, $id, $estado);
        if ($ok) {
            $userId = $_SESSION['usuario']['id_usuario'] ?? 0;
            log_change($userId, 'status_promo', ['id' => sprintf('%s:%d', $source, $id), 'old_estado' => $old['estado'] ?? null, 'nuevo_estado' => $estado]);
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
        $old = $model->getById($source, $id);
        $ok = $model->setEstado($source, $id, 'Inactivo');
        if ($ok) {
            $userId = $_SESSION['usuario']['id_usuario'] ?? 0;
            log_change($userId, 'delete_promo', ['id' => sprintf('%s:%d', $source, $id), 'old' => $old]);
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