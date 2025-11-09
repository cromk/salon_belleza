<?php
require_once __DIR__ . '/../models/reporteModel.php';
require_once __DIR__ . '/../config/Conexion.php';
require_once __DIR__ . '/../config/logger.php';

// Iniciar sesión para comprobaciones de rol
if (session_status() == PHP_SESSION_NONE) session_start();

class ReportesController {

  private $reporte;

  public function index() {
    include __DIR__ . '/../views/reportes.php';
  }

  public function __construct() {
    $conexion = new Conexion();
    $db = $conexion->conectar();
    $this->reporte = new reporteModel($db);
  }

  // Para AJAX o exportar CSV/PDF
  public function obtenerDatos() {
    $tipo = $_GET['tipo'] ?? 'ventas';
    $fechaInicio = $_GET['inicio'] ?? null;
    $fechaFin = $_GET['fin'] ?? null;
    $idEstilista = $_GET['estilista'] ?? null;

    switch ($tipo) {
      case 'servicios':
        $datos = $this->reporte->serviciosMasSolicitados($fechaInicio, $fechaFin);
        break;
      case 'ocupacion':
        $datos = $this->reporte->ocupacionEstilistas($fechaInicio, $fechaFin);
        break;
      case 'clientes':
        $datos = $this->reporte->clientesFrecuentes();
        break;
      default:
        $datos = $this->reporte->obtenerVentas($fechaInicio, $fechaFin, $idEstilista);
    }

    header('Content-Type: application/json');
    echo json_encode($datos);
  }
}
if (isset($_GET['tipo'])) {
  $controller = new ReportesController();
  $controller->obtenerDatos();
} else {
  $controller = new ReportesController();
  $controller->index();
}
?>