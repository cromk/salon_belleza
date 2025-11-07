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

    case 'getAgenda':
        // Devuelve horario base y citas (incluye bloqueos) para una fecha y estilista
        $id_estilista = isset($_GET['id_estilista']) ? intval($_GET['id_estilista']) : 0;
        $fecha = isset($_GET['fecha']) ? $_GET['fecha'] : null;
        if ($id_estilista <= 0 || !$fecha) { echo json_encode(['success'=>false,'message'=>'Parámetros inválidos']); exit; }
        try {
            // obtener día en español a partir de la fecha
            $ts = strtotime($fecha);
            $dias = ['Monday'=>'Lunes','Tuesday'=>'Martes','Wednesday'=>'Miércoles','Thursday'=>'Jueves','Friday'=>'Viernes','Saturday'=>'Sábado','Sunday'=>'Domingo'];
            $dia_nombre = $dias[date('l', $ts)];

            $stmt = $db->prepare("SELECT id_horario, dia_semana, hora_inicio, hora_fin FROM horarios WHERE id_estilista = :e AND dia_semana = :d");
            $stmt->bindParam(':e', $id_estilista, PDO::PARAM_INT);
            $stmt->bindParam(':d', $dia_nombre);
            $stmt->execute();
            $horarios = $stmt->fetchAll(PDO::FETCH_ASSOC);

            $stmt2 = $db->prepare("SELECT id_cita, id_cliente, id_servicio, hora_inicio, hora_fin, estado, observaciones FROM citas WHERE id_estilista = :e AND fecha_cita = :f");
            $stmt2->bindParam(':e', $id_estilista, PDO::PARAM_INT);
            $stmt2->bindParam(':f', $fecha);
            $stmt2->execute();
            $citas = $stmt2->fetchAll(PDO::FETCH_ASSOC);

            echo json_encode(['success'=>true,'data'=>['horarios'=>$horarios,'citas'=>$citas]]);
        } catch (Exception $e) {
            error_log('getAgenda error: ' . $e->getMessage());
            echo json_encode(['success'=>false,'message'=>'Error obteniendo agenda', 'detail' => $e->getMessage()]);
        }
        break;

    case 'blockSlot':
        // Solo admin
        if (!isset($_SESSION['usuario']) || (int)($_SESSION['usuario']['id_rol'] ?? 0) !== 1) {
            echo json_encode(['success' => false, 'message' => 'No autorizado']); exit;
        }
        $id_estilista = isset($_POST['id_estilista']) ? intval($_POST['id_estilista']) : 0;
        $fecha = isset($_POST['fecha']) ? $_POST['fecha'] : null;
        $hora_inicio = isset($_POST['hora_inicio']) ? $_POST['hora_inicio'] : null;
        $hora_fin = isset($_POST['hora_fin']) ? $_POST['hora_fin'] : null;
        $motivo = trim($_POST['motivo'] ?? 'Ausencia');
        if ($id_estilista <= 0 || !$fecha || !$hora_inicio || !$hora_fin) { echo json_encode(['success'=>false,'message'=>'Parámetros inválidos']); exit; }
        try {
            // comprobar conflictos con citas existentes (no contar bloqueos)
            $chk = $db->prepare("SELECT id_cita, hora_inicio, hora_fin, observaciones FROM citas WHERE id_estilista = :e AND fecha_cita = :f AND NOT (hora_fin <= :hi OR hora_inicio >= :hf)");
            $chk->execute([':e'=>$id_estilista, ':f'=>$fecha, ':hi'=>$hora_inicio, ':hf'=>$hora_fin]);
            $conf = $chk->fetchAll(PDO::FETCH_ASSOC);
            // si existe alguna cita que no sea bloqueos (identificamos bloqueos por observaciones que comienzan con 'BLOQUEO:')
            $realConf = array_filter($conf, function($r){ return stripos($r['observaciones'] ?? '', 'BLOQUEO:') !== 0; });
            if (count($realConf) > 0) {
                echo json_encode(['success'=>false,'message'=>'Conflicto con citas existentes','conflicts'=>$realConf]); exit;
            }

            // crear/obtener cliente marcador para bloqueos
            $blkCorreo = 'bloqueo@salonbelleza.local';
            $stmt = $db->prepare("SELECT id_cliente FROM clientes WHERE correo = :c LIMIT 1");
            $stmt->execute([':c'=>$blkCorreo]);
            $cli = $stmt->fetch(PDO::FETCH_ASSOC);
            if ($cli) $id_cliente_blk = $cli['id_cliente'];
            else {
                $insc = $db->prepare("INSERT INTO clientes (nombre, apellido, telefono, correo) VALUES (:n,:a,:t,:c)");
                $insc->execute([':n'=>'Bloqueo',':a'=>'Sistema',':t'=>'',':c'=>$blkCorreo]);
                $id_cliente_blk = $db->lastInsertId();
            }

            // crear/obtener servicio marcador para bloqueos
            $svcName = 'BLOQUEO - NO ASIGNABLE';
            $sstmt = $db->prepare("SELECT id_servicio FROM servicios WHERE nombre = :n LIMIT 1");
            $sstmt->execute([':n'=>$svcName]);
            $svc = $sstmt->fetch(PDO::FETCH_ASSOC);
            if ($svc) $id_servicio_blk = $svc['id_servicio'];
            else {
                $ins = $db->prepare("INSERT INTO servicios (nombre, descripcion, precio_base, duracion_base, estado) VALUES (:n,:d,0,0,'Inactivo')");
                $ins->execute([':n'=>$svcName, ':d'=>'Servicio marcador para bloqueos de agenda']);
                $id_servicio_blk = $db->lastInsertId();
            }

            // insertar cita como bloqueo
            $insb = $db->prepare("INSERT INTO citas (id_cliente, id_estilista, id_servicio, fecha_cita, hora_inicio, hora_fin, total, estado, observaciones) VALUES (:ic, :ie, :is, :f, :hi, :hf, 0, 'Confirmada', :obs)");
            $obsText = 'BLOQUEO: ' . $motivo;
            $insb->execute([':ic'=>$id_cliente_blk, ':ie'=>$id_estilista, ':is'=>$id_servicio_blk, ':f'=>$fecha, ':hi'=>$hora_inicio, ':hf'=>$hora_fin, ':obs'=>$obsText]);
            $newId = $db->lastInsertId();
            echo json_encode(['success'=>true,'id_cita'=> (int)$newId]);
        } catch (Exception $e) {
            error_log('blockSlot error: ' . $e->getMessage());
            echo json_encode(['success'=>false,'message'=>'Error creando bloqueo', 'detail' => $e->getMessage()]);
        }
        break;

    case 'unblockSlot':
        // Solo admin
        if (!isset($_SESSION['usuario']) || (int)($_SESSION['usuario']['id_rol'] ?? 0) !== 1) {
            echo json_encode(['success' => false, 'message' => 'No autorizado']); exit;
        }
        $id_cita = isset($_POST['id_cita']) ? intval($_POST['id_cita']) : 0;
        if ($id_cita <= 0) { echo json_encode(['success'=>false,'message'=>'id inválido']); exit; }
        try {
            // solo permitir eliminar si es un bloqueo (observaciones empiezan con BLOQUEO:)
            $chk = $db->prepare("SELECT observaciones FROM citas WHERE id_cita = :id LIMIT 1");
            $chk->execute([':id'=>$id_cita]);
            $row = $chk->fetch(PDO::FETCH_ASSOC);
            if (!$row) { echo json_encode(['success'=>false,'message'=>'No existe cita']); exit; }
            if (stripos($row['observaciones'] ?? '', 'BLOQUEO:') !== 0) { echo json_encode(['success'=>false,'message'=>'No es un bloqueo']); exit; }
            $del = $db->prepare("DELETE FROM citas WHERE id_cita = :id");
            $del->execute([':id'=>$id_cita]);
            echo json_encode(['success'=>true]);
        } catch (Exception $e) {
            error_log('unblockSlot error: ' . $e->getMessage());
            echo json_encode(['success'=>false,'message'=>'Error eliminando bloqueo', 'detail' => $e->getMessage()]);
        }
        break;

    default:
        echo json_encode(['success' => false, 'message' => 'Acción inválida']);
        break;
}

?>