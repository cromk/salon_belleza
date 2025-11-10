<?php
require_once __DIR__ . '/../models/pagoModel.php';
require_once __DIR__ . '/../config/Conexion.php';

if (session_status() == PHP_SESSION_NONE) session_start();

$con = new Conexion();
$db = $con->conectar();
$model = new PagoModel($db);

class PagosController {
  private $pago;

  public function __construct($model) {
    $this->pago = $model;
  }

  public function index() {
    $pagos = $this->pago->obtenerPagos();
    $citas = $this->pago->obtenerCitasPendientes();
    include __DIR__ . '/../views/pagos.php';
  }

  public function registrar() {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
      $idCita = $_POST['id_cita'];
      $metodo = $_POST['metodo'];
      $monto = $_POST['monto'];
      $referencia = uniqid("BF-");

      $this->pago->registrarPago($idCita, $metodo, $monto, $referencia);
      header('Location: /salon_belleza/controllers/PagosController.php');
      exit();
    }
  }

  // Endpoint AJAX para obtener info de una cita específica
  public function getCita() {
    if (isset($_GET['id_cita'])) {
      $data = $this->pago->obtenerDatosCita($_GET['id_cita']);
      header('Content-Type: application/json; charset=utf-8');
      echo json_encode($data);
      exit();
    }
  }
}

$controller = new PagosController($model);

if (isset($_GET['action'])) {
  switch ($_GET['action']) {
    case 'registrar':
      $controller->registrar();
      break;
    case 'getCita':
      $controller->getCita();
      break;
    default:
      $controller->index();
      break;
  }
} else {
  $controller->index();
}
?>
