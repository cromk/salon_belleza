<?php
require_once __DIR__ . '/../config/Conexion.php';

class estilistaModel {
    private $table = 'estilistas';
    private $conn;

    public function __construct($db) {
        $this->conn = $db;
    }

    public function create($id_usuario, $especialidad = null, $experiencia = 0, $disponible = 'Sí') {
        try {
            $query = "INSERT INTO {$this->table} (id_usuario, especialidad, experiencia_anios, disponible) VALUES (:id_usuario, :especialidad, :experiencia, :disponible)";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':id_usuario', $id_usuario, PDO::PARAM_INT);
            $stmt->bindParam(':especialidad', $especialidad);
            $stmt->bindParam(':experiencia', $experiencia, PDO::PARAM_INT);
            $stmt->bindParam(':disponible', $disponible);
            $ok = $stmt->execute();
            if ($ok) return $this->conn->lastInsertId();
            return false;
        } catch (Exception $e) {
            error_log($e->getMessage());
            return false;
        }
    }

    public function getAll() {
        try {
            $query = "SELECT e.id_estilista, e.id_usuario, u.nombre, u.apellido, u.correo, e.especialidad, e.experiencia_anios, e.disponible
                      FROM {$this->table} e
                      LEFT JOIN usuarios u ON u.id_usuario = e.id_usuario
                      ORDER BY u.nombre ASC";
            $stmt = $this->conn->prepare($query);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            error_log($e->getMessage());
            return [];
        }
    }

    public function getById($id) {
        try {
            $query = "SELECT * FROM {$this->table} WHERE id_estilista = :id LIMIT 1";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            error_log($e->getMessage());
            return null;
        }
    }

    public function assignServices($id_estilista, $serviceIds = []) {
        try {
            // eliminar associations previas
            $del = $this->conn->prepare("DELETE FROM estilista_servicio WHERE id_estilista = :id_estilista");
            $del->bindParam(':id_estilista', $id_estilista, PDO::PARAM_INT);
            $del->execute();

            if (empty($serviceIds)) return true;
            $ins = $this->conn->prepare("INSERT INTO estilista_servicio (id_estilista, id_servicio) VALUES (:id_estilista, :id_servicio)");
            foreach ($serviceIds as $sid) {
                // usar execute con parámetros (bindValue/execute) para evitar problemas de referencia
                $ins->execute([':id_estilista' => $id_estilista, ':id_servicio' => $sid]);
            }
            return true;
        } catch (Exception $e) {
            error_log($e->getMessage());
            return false;
        }
    }

    public function assignHorarios($id_estilista, $horarios = []) {
        try {
            // eliminar horarios previos
            $del = $this->conn->prepare("DELETE FROM horarios WHERE id_estilista = :id_estilista");
            $del->execute([':id_estilista' => $id_estilista]);

            if (empty($horarios) || !is_array($horarios)) return true;
            $ins = $this->conn->prepare("INSERT INTO horarios (id_estilista, dia_semana, hora_inicio, hora_fin) VALUES (:id_estilista, :dia_semana, :hora_inicio, :hora_fin)");
            foreach ($horarios as $h) {
                // esperar estructura: ['dia_semana'=>'Lunes','hora_inicio'=>'09:00','hora_fin'=>'17:00']
                $dia = $h['dia_semana'] ?? null;
                $hi = $h['hora_inicio'] ?? null;
                $hf = $h['hora_fin'] ?? null;
                if (!$dia || !$hi || !$hf) continue;
                $ins->execute([':id_estilista' => $id_estilista, ':dia_semana' => $dia, ':hora_inicio' => $hi, ':hora_fin' => $hf]);
            }
            return true;
        } catch (Exception $e) {
            error_log('assignHorarios error: ' . $e->getMessage());
            return false;
        }
    }

    public function getServices($id_estilista) {
        try {
            $query = "SELECT s.id_servicio, s.nombre FROM estilista_servicio es JOIN servicios s ON s.id_servicio = es.id_servicio WHERE es.id_estilista = :id";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':id', $id_estilista, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            error_log($e->getMessage());
            return [];
        }
    }
}

?>