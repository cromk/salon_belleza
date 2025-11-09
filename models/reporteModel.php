<?php
require_once __DIR__ . '/../config/Conexion.php';

class reporteModel {

  private $conn;

  public function __construct($conexion) {
    $this->conn = $conexion;
  }

  // Ventas totales y por fecha
  public function obtenerVentas($fechaInicio = null, $fechaFin = null, $idEstilista = null) {
    $sql = "SELECT c.fecha_cita, e.id_estilista, u.nombre AS estilista, SUM(c.total) AS total_ventas
            FROM citas c
            INNER JOIN estilistas e ON c.id_estilista = e.id_estilista
            INNER JOIN usuarios u ON e.id_usuario = u.id_usuario
            WHERE c.estado = 'Completada'";

    $params = [];
    if ($fechaInicio && $fechaFin) {
      $sql .= " AND c.fecha_cita BETWEEN ? AND ?";
      $params[] = $fechaInicio;
      $params[] = $fechaFin;
    }
    if ($idEstilista) {
      $sql .= " AND c.id_estilista = ?";
      $params[] = $idEstilista;
    }

    $sql .= " GROUP BY c.fecha_cita, e.id_estilista ORDER BY c.fecha_cita";

    $stmt = $this->conn->prepare($sql);
    $stmt->execute($params);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
  }

  // Servicios más solicitados
  public function serviciosMasSolicitados($fechaInicio = null, $fechaFin = null) {
    $sql = "SELECT s.nombre, COUNT(c.id_servicio) AS total
            FROM citas c
            INNER JOIN servicios s ON c.id_servicio = s.id_servicio
            WHERE c.estado IN ('Completada', 'Confirmada')";
    $params = [];
    if ($fechaInicio && $fechaFin) {
      $sql .= " AND c.fecha_cita BETWEEN ? AND ?";
      $params[] = $fechaInicio;
      $params[] = $fechaFin;
    }
    $sql .= " GROUP BY s.id_servicio ORDER BY total DESC LIMIT 5";
    $stmt = $this->conn->prepare($sql);
    $stmt->execute($params);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
  }

  // Ocupación de estilistas (HU-20)
  public function ocupacionEstilistas($fechaInicio = null, $fechaFin = null) {
    $sql = "SELECT u.nombre AS estilista, COUNT(c.id_cita) AS citas
            FROM citas c
            INNER JOIN estilistas e ON c.id_estilista = e.id_estilista
            INNER JOIN usuarios u ON e.id_usuario = u.id_usuario
            WHERE c.estado IN ('Completada','Confirmada')";
    $params = [];
    if ($fechaInicio && $fechaFin) {
      $sql .= " AND c.fecha_cita BETWEEN ? AND ?";
      $params[] = $fechaInicio;
      $params[] = $fechaFin;
    }
    $sql .= " GROUP BY e.id_estilista ORDER BY citas DESC";
    $stmt = $this->conn->prepare($sql);
    $stmt->execute($params);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
  }

  // Clientes frecuentes
  public function clientesFrecuentes($limite = 5) {
    $sql = "SELECT cl.nombre, cl.apellido, COUNT(c.id_cita) AS visitas
            FROM citas c
            INNER JOIN clientes cl ON c.id_cliente = cl.id_cliente
            WHERE c.estado IN ('Completada','Confirmada')
            GROUP BY cl.id_cliente ORDER BY visitas DESC LIMIT ?";
    $stmt = $this->conn->prepare($sql);
    $stmt->bindValue(1, (int)$limite, PDO::PARAM_INT);
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
  }
}
?>
