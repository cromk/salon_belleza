<?php
require_once __DIR__ . '/../config/Conexion.php';
require_once __DIR__ . '/../models/pagoModel.php';
require_once __DIR__ . '/../vendor/autoload.php';

use Dompdf\Dompdf;
use Dompdf\Options;

if (session_status() == PHP_SESSION_NONE) session_start();

class FacturaController {

  private $pagoModel;

  public function __construct() {
    $conexion = new Conexion();
    $db = $conexion->conectar();
    $this->pagoModel = new PagoModel($db);
  }

  public function generarFacturaPDF($idPago) {
    $factura = $this->pagoModel->obtenerDatosFactura($idPago);

    if (!$factura) {
      die("Factura no encontrada");
    }

    // Estructura HTML con el mismo formato de tus reportes
    $html = '
    <html>
    <head>
      <meta charset="UTF-8">
      <style>
        body { font-family: DejaVu Sans, sans-serif; color: #4b286d; font-size: 13px; }
        .header { text-align: center; border-bottom: 2px solid #8a2be2; margin-bottom: 15px; }
        .header h1 { margin: 5px; color: #8a2be2; }
        .header h2 { margin: 0; color: #4b286d; font-size: 18px; }
        .fecha { text-align: right; font-size: 12px; color: #666; }
        table { width: 100%; border-collapse: collapse; margin-top: 15px; }
        th, td { border: 1px solid #ccc; padding: 8px; text-align: center; }
        th { background-color: #8a2be2; color: white; }
        .total { font-weight: bold; text-align: right; margin-top: 10px; }
        .footer { text-align: center; font-size: 11px; color: #777; margin-top: 25px; }
      </style>
    </head>
    <body>
      <div class="header">
        <h1><i class="bi bi-scissors"></i> Salón de Belleza Beauty Flow</h1>
        <h2>Factura de Pago</h2>
        <p class="fecha">Generado: '.date('d/m/Y H:i').'</p>
      </div>

      <p>
        <strong>Cliente:</strong> '.htmlspecialchars($factura['cliente']).'<br>
        <strong>Estilista:</strong> '.htmlspecialchars($factura['estilista']).'<br>
        <strong>Método de Pago:</strong> '.htmlspecialchars($factura['metodo']).'<br>
        <strong>Fecha de Pago:</strong> '.htmlspecialchars($factura['fecha_pago']).'<br>
        <strong>Referencia:</strong> '.htmlspecialchars($factura['referencia']).'
      </p>

      <table>
        <thead>
          <tr>
            <th>Servicio</th>
            <th>Precio</th>
            <th>Monto Pagado</th>
          </tr>
        </thead>
        <tbody>
          <tr>
            <td>'.htmlspecialchars($factura['servicio']).'</td>
            <td>$'.number_format($factura['monto'], 2).'</td>
            <td>$'.number_format($factura['monto'], 2).'</td>
          </tr>
        </tbody>
      </table>

      <p class="total">Total: $'.number_format($factura['monto'], 2).'</p>

      <div class="footer">
        ¡Gracias por su preferencia!<br>
        Salón de Belleza Beauty Flow
      </div>
    </body>
    </html>';

    // Configurar Dompdf
    $options = new Options();
    $options->set('isRemoteEnabled', true);
    $dompdf = new Dompdf($options);
    $dompdf->loadHtml($html);
    $dompdf->setPaper('A4', 'portrait');
    $dompdf->render();

    $fecha = date("Ymd_His");
    $archivo = "Factura_BeautyFlow_{$factura['id_pago']}_{$fecha}.pdf";

    // Descargar el archivo
    $dompdf->stream($archivo, ["Attachment" => true]);
    exit;
  }
}

// Permite ejecución directa con ?id_pago=#
if (isset($_GET['id_pago'])) {
  $controller = new FacturaController();
  $controller->generarFacturaPDF($_GET['id_pago']);
}
?>
