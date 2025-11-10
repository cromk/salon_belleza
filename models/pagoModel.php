<?php
require_once __DIR__ . '/../config/Conexion.php';

class PagoModel {
  private $conn;

  public function __construct($conexion) {
    $this->conn = $conexion;
  }

  public function registrarPago($idCita, $metodo, $monto, $referencia) {
    $sql = "INSERT INTO pagos (id_cita, metodo, monto, referencia, estado, fecha_pago)
            VALUES (?, ?, ?, ?, 'Completado', NOW())";
    $stmt = $this->conn->prepare($sql);
    $stmt->execute([$idCita, $metodo, $monto, $referencia]);

    // Actualiza la cita como pagada
    $sql2 = "UPDATE citas SET estado_pago = 'Completado' WHERE id_cita = ?";
    $stmt2 = $this->conn->prepare($sql2);
    $stmt2->execute([$idCita]);

    return true;
  }

  public function obtenerPagos() {
    $sql = "SELECT p.id_pago, c.fecha_cita, p.metodo, p.monto, p.referencia, p.estado, p.fecha_pago
            FROM pagos p
            INNER JOIN citas c ON p.id_cita = c.id_cita
            ORDER BY p.fecha_pago DESC";
    $stmt = $this->conn->query($sql);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
  }

  public function obtenerCitasPendientes() {
  $sql = "SELECT 
            c.id_cita,
            CONCAT(cl.nombre, ' ', cl.apellido) AS cliente,
            s.nombre AS servicio,
            c.total AS monto
          FROM citas c
          INNER JOIN clientes cl ON c.id_cliente = cl.id_cliente
          INNER JOIN servicios s ON c.id_servicio = s.id_servicio
          WHERE c.estado = 'Completada' 
          AND (c.estado_pago IS NULL OR c.estado_pago != 'Completado')
          ORDER BY c.fecha_cita ASC";
          
  $stmt = $this->conn->query($sql);
  return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

public function obtenerDatosCita($idCita) {
  $sql = "SELECT 
            c.id_cita,
            CONCAT(cl.nombre, ' ', cl.apellido) AS cliente,
            s.nombre AS servicio,
            c.total AS monto
          FROM citas c
          INNER JOIN clientes cl ON c.id_cliente = cl.id_cliente
          INNER JOIN servicios s ON c.id_servicio = s.id_servicio
          WHERE c.id_cita = ?";
  $stmt = $this->conn->prepare($sql);
  $stmt->execute([$idCita]);
  return $stmt->fetch(PDO::FETCH_ASSOC);
}

// Datos para generar la factura PDF
  public function obtenerDatosFactura($idPago) {
  $sql = "SELECT 
            p.id_pago,
            p.metodo,
            p.monto,
            p.referencia,
            p.fecha_pago,
            c.fecha_cita,
            CONCAT(cl.nombre, ' ', cl.apellido) AS cliente,
            s.nombre AS servicio,
            u.nombre AS estilista
          FROM pagos p
          INNER JOIN citas c ON p.id_cita = c.id_cita
          INNER JOIN clientes cl ON c.id_cliente = cl.id_cliente
          INNER JOIN servicios s ON c.id_servicio = s.id_servicio
          INNER JOIN estilistas e ON c.id_estilista = e.id_estilista
          INNER JOIN usuarios u ON e.id_usuario = u.id_usuario
          WHERE p.id_pago = ?";
  $stmt = $this->conn->prepare($sql);
  $stmt->execute([$idPago]);
  return $stmt->fetch(PDO::FETCH_ASSOC);
}
}
?>
