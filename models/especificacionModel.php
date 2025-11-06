<?php
require_once __DIR__ . '/../config/Conexion.php';

class especificacionModel {
    private $table = 'especificaciones';
    private $conn;

    public function __construct($db) {
        $this->conn = $db;
    }

    public function create($id_servicio, $nombre, $tipo = 'opcional', $valor_precio = 0.00, $valor_tiempo = 0, $descripcion = null) {
        try {
            $query = "INSERT INTO {$this->table} (id_servicio, nombre, descripcion, tipo, valor_precio, valor_tiempo) VALUES (:id_servicio, :nombre, :descripcion, :tipo, :valor_precio, :valor_tiempo)";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':id_servicio', $id_servicio, PDO::PARAM_INT);
            $stmt->bindParam(':nombre', $nombre);
            $stmt->bindParam(':descripcion', $descripcion);
            $stmt->bindParam(':tipo', $tipo);
            $stmt->bindParam(':valor_precio', $valor_precio);
            $stmt->bindParam(':valor_tiempo', $valor_tiempo, PDO::PARAM_INT);
            $ok = $stmt->execute();
            if ($ok) return $this->conn->lastInsertId();
            return false;
        } catch (Exception $e) {
            error_log($e->getMessage());
            return false;
        }
    }

    public function getByService($id_servicio, $onlyActive = true) {
        try {
            $query = "SELECT id_especificacion, id_servicio, nombre, descripcion, tipo, valor_precio, valor_tiempo, estado FROM {$this->table} WHERE id_servicio = :id_servicio";
            if ($onlyActive) $query .= " AND estado = 'Activo'";
            $query .= " ORDER BY nombre ASC";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':id_servicio', $id_servicio, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            error_log($e->getMessage());
            return [];
        }
    }
}

?>
