<?php
require_once __DIR__ . '/../config/Conexion.php';

class servicioModel {
    private $table = 'servicios';
    private $conn;

    public function __construct($db) {
        $this->conn = $db;
    }

    public function create($nombre, $precio_base, $duracion_base, $descripcion = null) {
        try {
            $query = "INSERT INTO {$this->table} (nombre, descripcion, precio_base, duracion_base) VALUES (:nombre, :descripcion, :precio_base, :duracion_base)";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':nombre', $nombre);
            $stmt->bindParam(':descripcion', $descripcion);
            $stmt->bindParam(':precio_base', $precio_base);
            $stmt->bindParam(':duracion_base', $duracion_base, PDO::PARAM_INT);
            $ok = $stmt->execute();
            if ($ok) return $this->conn->lastInsertId();
            return false;
        } catch (Exception $e) {
            error_log($e->getMessage());
            return false;
        }
    }

    public function getAll($onlyActive = true) {
        try {
            $query = "SELECT id_servicio, nombre, descripcion, precio_base, duracion_base, estado FROM {$this->table}";
            if ($onlyActive) $query .= " WHERE estado = 'Activo'";
            $query .= " ORDER BY nombre ASC";
            $stmt = $this->conn->prepare($query);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            error_log($e->getMessage());
            return [];
        }
    }

    public function countActive() {
        try {
            $query = "SELECT COUNT(*) as cnt FROM {$this->table} WHERE estado = 'Activo'";
            $stmt = $this->conn->prepare($query);
            $stmt->execute();
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            return isset($row['cnt']) ? (int) $row['cnt'] : 0;
        } catch (Exception $e) {
            error_log($e->getMessage());
            return 0;
        }
    }

    public function getById($id) {
        try {
            $query = "SELECT id_servicio, nombre, descripcion, precio_base, duracion_base, estado FROM {$this->table} WHERE id_servicio = :id LIMIT 1";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            error_log($e->getMessage());
            return null;
        }
    }

    public function update($id, $nombre, $precio_base, $duracion_base, $descripcion = null) {
        try {
            $query = "UPDATE {$this->table} SET nombre = :nombre, descripcion = :descripcion, precio_base = :precio_base, duracion_base = :duracion_base WHERE id_servicio = :id";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':nombre', $nombre);
            $stmt->bindParam(':descripcion', $descripcion);
            $stmt->bindParam(':precio_base', $precio_base);
            $stmt->bindParam(':duracion_base', $duracion_base, PDO::PARAM_INT);
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);
            return $stmt->execute();
        } catch (Exception $e) {
            error_log($e->getMessage());
            return false;
        }
    }

    public function setEstado($id, $estado = 'Inactivo') {
        try {
            $query = "UPDATE {$this->table} SET estado = :estado WHERE id_servicio = :id";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':estado', $estado);
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);
            return $stmt->execute();
        } catch (Exception $e) {
            error_log($e->getMessage());
            return false;
        }
    }
}

?>
