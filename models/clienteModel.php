<?php
require_once __DIR__ . '/../config/Conexion.php';

class clienteModel {
    private $table = 'clientes';
    private $conn;

    public function __construct($db) {
        $this->conn = $db;
    }

    public function create($nombre, $apellido = null, $telefono = null, $correo = null) {
        try {
            $query = "INSERT INTO {$this->table} (nombre, apellido, telefono, correo, estado) VALUES (:nombre, :apellido, :telefono, :correo, 'Activo')";
            $stmt = $this->conn->prepare($query);
            $stmt->bindValue(':nombre', $nombre);
            $this->bindNullable($stmt, ':apellido', $apellido);
            $this->bindNullable($stmt, ':telefono', $telefono);
            $this->bindNullable($stmt, ':correo', $correo);
            $ok = $stmt->execute();
            if ($ok) {
                return (int) $this->conn->lastInsertId();
            }
            return false;
        } catch (Exception $e) {
            error_log('clienteModel::create -> ' . $e->getMessage());
            return false;
        }
    }

    public function getAll($includeInactive = false) {
        try {
            $query = "SELECT c.id_cliente, c.nombre, c.apellido, c.telefono, c.correo, c.estado, c.fecha_registro,"
                . "       COALESCE(SUM(CASE WHEN ci.id_cita IS NOT NULL AND (ci.observaciones IS NULL OR ci.observaciones NOT LIKE 'BLOQUEO:%') THEN 1 ELSE 0 END), 0) AS total_citas"
                . " FROM {$this->table} c"
                . " LEFT JOIN citas ci ON ci.id_cliente = c.id_cliente"
                . ($includeInactive ? '' : " WHERE c.estado = 'Activo'")
                . " GROUP BY c.id_cliente, c.nombre, c.apellido, c.telefono, c.correo, c.estado, c.fecha_registro"
                . " ORDER BY c.nombre ASC";
            $stmt = $this->conn->prepare($query);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            error_log('clienteModel::getAll -> ' . $e->getMessage());
            return [];
        }
    }

    public function findByCorreo($correo, $excludeId = null) {
        if ($correo === null || $correo === '') return null;
        try {
            $query = "SELECT id_cliente, nombre, apellido, telefono, correo, estado FROM {$this->table} WHERE correo = :correo";
            if ($excludeId) {
                $query .= " AND id_cliente != :exclude";
            }
            $query .= " LIMIT 1";
            $stmt = $this->conn->prepare($query);
            $stmt->bindValue(':correo', $correo);
            if ($excludeId) {
                $stmt->bindValue(':exclude', $excludeId, PDO::PARAM_INT);
            }
            $stmt->execute();
            return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
        } catch (Exception $e) {
            error_log('clienteModel::findByCorreo -> ' . $e->getMessage());
            return null;
        }
    }

    public function update($id, $nombre, $apellido = null, $telefono = null, $correo = null) {
        try {
            $query = "UPDATE {$this->table} SET nombre = :nombre, apellido = :apellido, telefono = :telefono, correo = :correo WHERE id_cliente = :id";
            $stmt = $this->conn->prepare($query);
            $stmt->bindValue(':nombre', $nombre);
            $this->bindNullable($stmt, ':apellido', $apellido);
            $this->bindNullable($stmt, ':telefono', $telefono);
            $this->bindNullable($stmt, ':correo', $correo);
            $stmt->bindValue(':id', $id, PDO::PARAM_INT);
            return $stmt->execute();
        } catch (Exception $e) {
            error_log('clienteModel::update -> ' . $e->getMessage());
            return false;
        }
    }

    public function setEstado($id, $estado = 'Inactivo') {
        try {
            $query = "UPDATE {$this->table} SET estado = :estado WHERE id_cliente = :id";
            $stmt = $this->conn->prepare($query);
            $stmt->bindValue(':estado', $estado);
            $stmt->bindValue(':id', $id, PDO::PARAM_INT);
            return $stmt->execute();
        } catch (Exception $e) {
            error_log('clienteModel::setEstado -> ' . $e->getMessage());
            return false;
        }
    }

    public function delete($id) {
        try {
            $query = "DELETE FROM {$this->table} WHERE id_cliente = :id";
            $stmt = $this->conn->prepare($query);
            $stmt->bindValue(':id', $id, PDO::PARAM_INT);
            return $stmt->execute();
        } catch (Exception $e) {
            error_log('clienteModel::delete -> ' . $e->getMessage());
            return false;
        }
    }

    public function hasHistorial($id) {
        try {
            $query = "SELECT COUNT(*) as cnt FROM citas WHERE id_cliente = :id AND (observaciones IS NULL OR observaciones NOT LIKE 'BLOQUEO:%')";
            $stmt = $this->conn->prepare($query);
            $stmt->bindValue(':id', $id, PDO::PARAM_INT);
            $stmt->execute();
            $count = (int) $stmt->fetchColumn();
            return $count > 0;
        } catch (Exception $e) {
            error_log('clienteModel::hasHistorial -> ' . $e->getMessage());
            return false;
        }
    }

    private function bindNullable($stmt, $param, $value) {
        if ($value === null || $value === '') {
            $stmt->bindValue($param, null, PDO::PARAM_NULL);
        } else {
            $stmt->bindValue($param, $value);
        }
    }
}

?>