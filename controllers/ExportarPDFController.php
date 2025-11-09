<?php
require_once __DIR__ . '/../config/Conexion.php';
require_once __DIR__ . '/../models/reporteModel.php';
require_once __DIR__ . '/../vendor/autoload.php';

use Dompdf\Dompdf;
use Dompdf\Options;

if (session_status() == PHP_SESSION_NONE) session_start();

$tipo = $_GET['tipo'] ?? 'ventas';
$inicio = $_GET['inicio'] ?? null;
$fin = $_GET['fin'] ?? null;

$conexion = new Conexion();
$db = $conexion->conectar();
$model = new reporteModel($db);

// Obtener datos según tipo
switch ($tipo) {
  case 'servicios':
    $datos = $model->serviciosMasSolicitados($inicio, $fin);
    $titulo = 'Reporte de Servicios más Solicitados';
    break;
  case 'ocupacion':
    $datos = $model->ocupacionEstilistas($inicio, $fin);
    $titulo = 'Reporte de Ocupación de Estilistas';
    break;
  case 'clientes':
    $datos = $model->clientesFrecuentes();
    $titulo = 'Reporte de Clientes Frecuentes';
    break;
  default:
    $datos = $model->obtenerVentas($inicio, $fin);
    $titulo = 'Reporte de Ventas';
}

$html = '
<html>
<head>
  <style>
    body { font-family: DejaVu Sans, sans-serif; color: #4b286d; }
    .header { text-align: center; border-bottom: 2px solid #8a2be2; margin-bottom: 15px; }
    .header h2 { margin: 5px; color: #8a2be2; }
    .fecha { text-align: right; font-size: 12px; color: #666; }
    table { width: 100%; border-collapse: collapse; margin-top: 15px; }
    th, td { border: 1px solid #ccc; padding: 6px; text-align: center; font-size: 13px; }
    th { background-color: #8a2be2; color: white; }
  </style>
</head>
<body>
  <div class="header">
    <h1><i class="bi bi-scissors"></i>Salón de Belleza Beauty Flow</h1>
    <h2>'.$titulo.'</h2>
    <p class="fecha">Generado: '.date('d/m/Y H:i').'</p>
  </div>
  <table>
    <thead><tr>';

if (count($datos)) {
  foreach (array_keys($datos[0]) as $col) {
    $html .= "<th>".ucwords(str_replace('_', ' ', $col))."</th>";
  }
  $html .= '</tr></thead><tbody>';
  foreach ($datos as $fila) {
    $html .= '<tr>';
    foreach ($fila as $valor) {
      $html .= '<td>'.$valor.'</td>';
    }
    $html .= '</tr>';
  }
  $html .= '</tbody>';
} else {
  $html .= '<th>No hay datos disponibles</th></tr></thead>';
}

$html .= '</table></body></html>';

// Configurar Dompdf
$options = new Options();
$options->set('isRemoteEnabled', true);
$dompdf = new Dompdf($options);
$dompdf->loadHtml($html);
$dompdf->setPaper('A4', 'portrait');
$dompdf->render();
// Generar nombre dinámico
$fechaActual = date("Ymd"); // Ejemplo: 20251109
$nombreReporte = ucfirst($tipo); // Ventas, Servicios, etc.
$nombreArchivo = "BeautyFlow_Reporte{$nombreReporte}_{$fechaActual}.pdf";

$dompdf->stream($nombreArchivo, ["Attachment" => true]);

exit;
?>
