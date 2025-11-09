<?php
// Importar la conexión a la base de datos para reutilizarla en el modelo
require_once __DIR__ . '/../config/Conexion.php';

/**
 * Clase modelo para administrar las promociones y combos del sistema.
 * Encapsula todas las operaciones CRUD sobre la tabla `promociones`.
 */
class promocionModel {
    /** @var string Nombre de la tabla a manipular */
    private $table = 'promociones';

    /** @var PDO Conexión activa a la base de datos */
    private $conn;

    /**
     * Constructor del modelo.
     *
     * @param PDO $db Instancia de conexión recibida desde el controlador.
     */
    public function __construct($db) {
        // Guardar la referencia de la conexión para reutilizarla en los métodos del modelo
        $this->conn = $db;
    }

    /**
     * Crear un nuevo registro de promoción/combo.
     *
     * @param string $nombre Nombre comercial de la promoción.
     * @param string $tipo Tipo de registro (Promoción o Combo).
     * @param string|null $descripcion Descripción detallada.
     * @param float $precio Precio final ofertado.
     * @return int|false Retorna el ID insertado o false en caso de error.
     */
    public function create($nombre, $tipo, $descripcion, $precio) {
        try {
            // Preparar la sentencia parametrizada para evitar inyecciones SQL
            $query = "INSERT INTO {$this->table} (nombre, tipo, descripcion, precio, estado) VALUES (:nombre, :tipo, :descripcion, :precio, 'Activo')";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':nombre', $nombre);
            $stmt->bindParam(':tipo', $tipo);
            $stmt->bindParam(':descripcion', $descripcion);
            $stmt->bindParam(':precio', $precio);
            $ok = $stmt->execute();
            if ($ok) {
                // Devolver el ID generado por la base de datos al crear el registro
                return (int) $this->conn->lastInsertId();
            }
            return false;
        } catch (Exception $e) {
            // Registrar el error para depuración y devolver false
            error_log($e->getMessage());
            return false;
        }
    }

    /**
     * Obtener todas las promociones/combos registrados.
     *
     * @param bool $onlyActive Indica si solo se consultan registros activos.
     * @return array Listado de promociones encontradas.
     */
    public function getAll($onlyActive = true) {
        try {
            // Construir la consulta base ordenada alfabéticamente
            $query = "SELECT id_promocion, nombre, tipo, descripcion, precio, estado FROM {$this->table}";
            if ($onlyActive) {
                // Filtrar por estado activo cuando se solicita únicamente lo visible al público
                $query .= " WHERE estado = 'Activo'";
            }
            $query .= " ORDER BY nombre ASC";
            $stmt = $this->conn->prepare($query);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            error_log($e->getMessage());
            return [];
        }
    }

    /**
     * Obtener una promoción específica por ID.
     *
     * @param int $id Identificador único.
     * @return array|null Devuelve los datos o null si no existe.
     */
    public function getById($id) {
        try {
            $query = "SELECT id_promocion, nombre, tipo, descripcion, precio, estado FROM {$this->table} WHERE id_promocion = :id LIMIT 1";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
        } catch (Exception $e) {
            error_log($e->getMessage());
            return null;
        }
    }

    /**
     * Actualizar los datos principales de una promoción o combo.
     *
     * @param int $id Identificador del registro.
     * @param string $nombre Nombre de la promoción.
     * @param string $tipo Tipo seleccionado.
     * @param string|null $descripcion Detalle informativo.
     * @param float $precio Precio actualizado.
     * @return bool True si se modificó correctamente.
     */
    public function update($id, $nombre, $tipo, $descripcion, $precio) {
        try {
            $query = "UPDATE {$this->table} SET nombre = :nombre, tipo = :tipo, descripcion = :descripcion, precio = :precio WHERE id_promocion = :id";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':nombre', $nombre);
            $stmt->bindParam(':tipo', $tipo);
            $stmt->bindParam(':descripcion', $descripcion);
            $stmt->bindParam(':precio', $precio);
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);
            return $stmt->execute();
        } catch (Exception $e) {
            error_log($e->getMessage());
            return false;
        }
    }

    /**
     * Cambiar el estado (Activo/Inactivo) de una promoción determinada.
     *
     * @param int $id Identificador del registro.
     * @param string $estado Nuevo estado deseado.
     * @return bool True si la actualización fue exitosa.
     */
    public function setEstado($id, $estado = 'Inactivo') {
        try {
            $query = "UPDATE {$this->table} SET estado = :estado WHERE id_promocion = :id";
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