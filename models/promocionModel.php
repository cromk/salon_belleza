<?php
// Importar la conexión a la base de datos para reutilizarla en el modelo
require_once __DIR__ . '/../config/Conexion.php';

/**
 * Clase modelo para administrar las promociones y combos del sistema.
 * Trabaja sobre las tablas `combos` y `ofertas` para centralizar la lógica.
 */
class promocionModel {

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
     * Crear un nuevo registro de promoción o combo.
     *
     * @param string $nombre Nombre comercial del registro.
     * @param string $tipo Tipo solicitado (Promoción o Combo).
     * @param string|null $descripcion Texto descriptivo opcional.
     * @param float $precio Valor asociado (precio final o descuento porcentual).
     * @return array{identifier:string,source:string,id:int}|false Información del nuevo registro o false.
     */
    public function create($nombre, $tipo, $descripcion, $precio) {
        try {
            $descripcion = $descripcion !== '' ? $descripcion : null;
            if ($tipo === 'Combo') {
                $query = "INSERT INTO combos (nombre, descripcion, precio_total, descuento, estado, fecha_inicio, fecha_fin) VALUES (:nombre, :descripcion, :precio, 0.00, 'Activo', NULL, NULL)";
                $stmt = $this->conn->prepare($query);
                $stmt->bindValue(':nombre', $nombre, PDO::PARAM_STR);
                if ($descripcion === null) {
                    $stmt->bindValue(':descripcion', null, PDO::PARAM_NULL);
                } else {
                    $stmt->bindValue(':descripcion', $descripcion, PDO::PARAM_STR);
                }
                $stmt->bindValue(':precio', number_format((float) $precio, 2, '.', ''), PDO::PARAM_STR);
                if ($stmt->execute()) {
                    $id = (int) $this->conn->lastInsertId();
                    return [
                        'identifier' => $this->composeIdentifier('combo', $id),
                        'source' => 'combo',
                        'id' => $id,
                    ];
                }
            } else {
                $query = "INSERT INTO ofertas (tipo, id_servicio, id_combo, nombre, descripcion, porcentaje_descuento, fecha_inicio, fecha_fin, estado) VALUES ('General', NULL, NULL, :nombre, :descripcion, :descuento, NULL, NULL, 'Activa')";
                $stmt = $this->conn->prepare($query);
                $stmt->bindValue(':nombre', $nombre, PDO::PARAM_STR);
                if ($descripcion === null) {
                    $stmt->bindValue(':descripcion', null, PDO::PARAM_NULL);
                } else {
                    $stmt->bindValue(':descripcion', $descripcion, PDO::PARAM_STR);
                }
                $stmt->bindValue(':descuento', number_format((float) $precio, 2, '.', ''), PDO::PARAM_STR);
                if ($stmt->execute()) {
                    $id = (int) $this->conn->lastInsertId();
                    return [
                        'identifier' => $this->composeIdentifier('oferta', $id),
                        'source' => 'oferta',
                        'id' => $id,
                    ];
                }
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
     * @return array Listado de promociones normalizadas.
     */
    public function getAll($onlyActive = true) {
        try {
            $result = [];

            // Consultar combos disponibles
            $comboQuery = "SELECT id_combo, nombre, descripcion, precio_total, estado FROM combos";
            if ($onlyActive) {
                $comboQuery .= " WHERE estado = 'Activo'";
            }
            $comboQuery .= " ORDER BY nombre ASC";
            $stmt = $this->conn->prepare($comboQuery);
            $stmt->execute();
            $combos = $stmt->fetchAll(PDO::FETCH_ASSOC);
            foreach ($combos as $combo) {
                $id = (int) ($combo['id_combo'] ?? 0);
                $result[] = [
                    'id_promocion' => $this->composeIdentifier('combo', $id),
                    'source' => 'combo',
                    'id' => $id,
                    'nombre' => $combo['nombre'] ?? '',
                    'tipo' => 'Combo',
                    'descripcion' => $combo['descripcion'] ?? null,
                    'precio' => isset($combo['precio_total']) ? (float) $combo['precio_total'] : 0.0,
                    'estado' => $combo['estado'] ?? 'Inactivo',
                ];
            }

            // Consultar ofertas disponibles
            $ofertaQuery = "SELECT id_oferta, nombre, descripcion, porcentaje_descuento, estado FROM ofertas";
            if ($onlyActive) {
                $ofertaQuery .= " WHERE estado = 'Activa'";
            }
            $ofertaQuery .= " ORDER BY nombre ASC";
            $stmt = $this->conn->prepare($ofertaQuery);
            $stmt->execute();
            $ofertas = $stmt->fetchAll(PDO::FETCH_ASSOC);
            foreach ($ofertas as $oferta) {
                $id = (int) ($oferta['id_oferta'] ?? 0);
                $estado = $oferta['estado'] ?? 'Inactiva';
                $result[] = [
                    'id_promocion' => $this->composeIdentifier('oferta', $id),
                    'source' => 'oferta',
                    'id' => $id,
                    'nombre' => $oferta['nombre'] ?? '',
                    'tipo' => 'Promoción',
                    'descripcion' => $oferta['descripcion'] ?? null,
                    'precio' => isset($oferta['porcentaje_descuento']) ? (float) $oferta['porcentaje_descuento'] : 0.0,
                    'estado' => ($estado === 'Activa') ? 'Activo' : 'Inactivo',
                ];
            }

            // Ordenar alfabéticamente para una vista unificada
            usort($result, function ($a, $b) {
                return strcasecmp($a['nombre'] ?? '', $b['nombre'] ?? '');
            });

            return $result;
        } catch (Exception $e) {
            error_log($e->getMessage());
            return [];
        }
    }

    /**
     * Obtener una promoción específica por ID.
     *
     * @param string $source Fuente del registro (combo|oferta).
     * @param int $id Identificador único dentro de la tabla correspondiente.
     * @return array|null Registro normalizado o null si no existe.
     */
    public function getById($source, $id) {
        try {
            <?php
// Importar la conexión a la base de datos para reutilizarla en el modelo
require_once __DIR__ . '/../config/Conexion.php';

/**
 * Clase modelo para administrar las promociones y combos del sistema.
 * Encapsula todas las operaciones CRUD sobre la tabla `promociones`.
 * Trabaja sobre las tablas `combos` y `ofertas` para centralizar la lógica.
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
     * Crear un nuevo registro de promoción o combo.
     *
     * @param string $nombre Nombre comercial de la promoción.
     * @param string $tipo Tipo de registro (Promoción o Combo).
     * @param string|null $descripcion Descripción detallada.
     * @param float $precio Precio final ofertado.
     * @return int|false Retorna el ID insertado o false en caso de error.
     * @param string $nombre Nombre comercial del registro.
     * @param string $tipo Tipo solicitado (Promoción o Combo).
     * @param string|null $descripcion Texto descriptivo opcional.
     * @param float $precio Valor asociado (precio final o descuento porcentual).
     * @return array{identifier:string,source:string,id:int}|false Información del nuevo registro o false.
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
            $descripcion = $descripcion !== '' ? $descripcion : null;
            if ($tipo === 'Combo') {
                $query = "INSERT INTO combos (nombre, descripcion, precio_total, descuento, estado, fecha_inicio, fecha_fin) VALUES (:nombre, :descripcion, :precio, 0.00, 'Activo', NULL, NULL)";
                $stmt = $this->conn->prepare($query);
                $stmt->bindValue(':nombre', $nombre, PDO::PARAM_STR);
                if ($descripcion === null) {
                    $stmt->bindValue(':descripcion', null, PDO::PARAM_NULL);
                } else {
                    $stmt->bindValue(':descripcion', $descripcion, PDO::PARAM_STR);
                }
                $stmt->bindValue(':precio', number_format((float) $precio, 2, '.', ''), PDO::PARAM_STR);
                if ($stmt->execute()) {
                    $id = (int) $this->conn->lastInsertId();
                    return [
                        'identifier' => $this->composeIdentifier('combo', $id),
                        'source' => 'combo',
                        'id' => $id,
                    ];
                }
            } else {
                $query = "INSERT INTO ofertas (tipo, id_servicio, id_combo, nombre, descripcion, porcentaje_descuento, fecha_inicio, fecha_fin, estado) VALUES ('General', NULL, NULL, :nombre, :descripcion, :descuento, NULL, NULL, 'Activa')";
                $stmt = $this->conn->prepare($query);
                $stmt->bindValue(':nombre', $nombre, PDO::PARAM_STR);
                if ($descripcion === null) {
                    $stmt->bindValue(':descripcion', null, PDO::PARAM_NULL);
                } else {
                    $stmt->bindValue(':descripcion', $descripcion, PDO::PARAM_STR);
                }
                $stmt->bindValue(':descuento', number_format((float) $precio, 2, '.', ''), PDO::PARAM_STR);
                if ($stmt->execute()) {
                    $id = (int) $this->conn->lastInsertId();
                    return [
                        'identifier' => $this->composeIdentifier('oferta', $id),
                        'source' => 'oferta',
                        'id' => $id,
                    ];
                }
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
     * @return array Listado de promociones normalizadas.
     */
    public function getAll($onlyActive = true) {
        try {
            // Construir la consulta base ordenada alfabéticamente
            $query = "SELECT id_promocion, nombre, tipo, descripcion, precio, estado FROM {$this->table}";
            $result = [];

            // Consultar combos disponibles
            $comboQuery = "SELECT id_combo, nombre, descripcion, precio_total, estado FROM combos";
            if ($onlyActive) {
                // Filtrar por estado activo cuando se solicita únicamente lo visible al público
                $query .= " WHERE estado = 'Activo'";
                $comboQuery .= " WHERE estado = 'Activo'";
            }
            $query .= " ORDER BY nombre ASC";
            $stmt = $this->conn->prepare($query);
            $comboQuery .= " ORDER BY nombre ASC";
            $stmt = $this->conn->prepare($comboQuery);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
            $combos = $stmt->fetchAll(PDO::FETCH_ASSOC);
            foreach ($combos as $combo) {
                $id = (int) ($combo['id_combo'] ?? 0);
                $result[] = [
                    'id_promocion' => $this->composeIdentifier('combo', $id),
                    'source' => 'combo',
                    'id' => $id,
                    'nombre' => $combo['nombre'] ?? '',
                    'tipo' => 'Combo',
                    'descripcion' => $combo['descripcion'] ?? null,
                    'precio' => isset($combo['precio_total']) ? (float) $combo['precio_total'] : 0.0,
                    'estado' => $combo['estado'] ?? 'Inactivo',
                ];
            }

            // Consultar ofertas disponibles
            $ofertaQuery = "SELECT id_oferta, nombre, descripcion, porcentaje_descuento, estado FROM ofertas";
            if ($onlyActive) {
                $ofertaQuery .= " WHERE estado = 'Activa'";
            }
            $ofertaQuery .= " ORDER BY nombre ASC";
            $stmt = $this->conn->prepare($ofertaQuery);
            $stmt->execute();
            $ofertas = $stmt->fetchAll(PDO::FETCH_ASSOC);
            foreach ($ofertas as $oferta) {
                $id = (int) ($oferta['id_oferta'] ?? 0);
                $estado = $oferta['estado'] ?? 'Inactiva';
                $result[] = [
                    'id_promocion' => $this->composeIdentifier('oferta', $id),
                    'source' => 'oferta',
                    'id' => $id,
                    'nombre' => $oferta['nombre'] ?? '',
                    'tipo' => 'Promoción',
                    'descripcion' => $oferta['descripcion'] ?? null,
                    'precio' => isset($oferta['porcentaje_descuento']) ? (float) $oferta['porcentaje_descuento'] : 0.0,
                    'estado' => ($estado === 'Activa') ? 'Activo' : 'Inactivo',
                ];
            }

            // Ordenar alfabéticamente para una vista unificada
            usort($result, function ($a, $b) {
                return strcasecmp($a['nombre'] ?? '', $b['nombre'] ?? '');
            });

            return $result;
        } catch (Exception $e) {
            error_log($e->getMessage());
            return [];
        }
    }

    /**
     * Obtener una promoción específica por ID.
     * Obtener una promoción específica por su fuente y ID.
     *
     * @param int $id Identificador único.
     * @return array|null Devuelve los datos o null si no existe.
     * @param string $source Fuente del registro (combo|oferta).
     * @param int $id Identificador único dentro de la tabla correspondiente.
     * @return array|null Registro normalizado o null si no existe.
     */
    public function getById($id) {
    public function getById($source, $id) {
        try {
            $query = "SELECT id_promocion, nombre, tipo, descripcion, precio, estado FROM {$this->table} WHERE id_promocion = :id LIMIT 1";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
            if ($source === 'combo') {
                $query = "SELECT id_combo, nombre, descripcion, precio_total, estado FROM combos WHERE id_combo = :id LIMIT 1";
                $stmt = $this->conn->prepare($query);
                $stmt->bindValue(':id', $id, PDO::PARAM_INT);
                $stmt->execute();
                $combo = $stmt->fetch(PDO::FETCH_ASSOC);
                if ($combo) {
                    return [
                        'id_promocion' => $this->composeIdentifier('combo', (int) $combo['id_combo']),
                        'source' => 'combo',
                        'id' => (int) $combo['id_combo'],
                        'nombre' => $combo['nombre'] ?? '',
                        'tipo' => 'Combo',
                        'descripcion' => $combo['descripcion'] ?? null,
                        'precio' => isset($combo['precio_total']) ? (float) $combo['precio_total'] : 0.0,
                        'estado' => $combo['estado'] ?? 'Inactivo',
                    ];
                }
            } elseif ($source === 'oferta') {
                $query = "SELECT id_oferta, nombre, descripcion, porcentaje_descuento, estado FROM ofertas WHERE id_oferta = :id LIMIT 1";
                $stmt = $this->conn->prepare($query);
                $stmt->bindValue(':id', $id, PDO::PARAM_INT);
                $stmt->execute();
                $oferta = $stmt->fetch(PDO::FETCH_ASSOC);
                if ($oferta) {
                    $estado = $oferta['estado'] ?? 'Inactiva';
                    return [
                        'id_promocion' => $this->composeIdentifier('oferta', (int) $oferta['id_oferta']),
                        'source' => 'oferta',
                        'id' => (int) $oferta['id_oferta'],
                        'nombre' => $oferta['nombre'] ?? '',
                        'tipo' => 'Promoción',
                        'descripcion' => $oferta['descripcion'] ?? null,
                        'precio' => isset($oferta['porcentaje_descuento']) ? (float) $oferta['porcentaje_descuento'] : 0.0,
                        'estado' => ($estado === 'Activa') ? 'Activo' : 'Inactivo',
                    ];
                }
            }
            return null;
        } catch (Exception $e) {
            error_log($e->getMessage());
            return null;
        }
    }

    /**
     * Actualizar los datos principales de una promoción o combo.
     *
     * @param string $source Fuente del registro (combo|oferta).
     * @param int $id Identificador del registro.
     * @param string $nombre Nombre actualizado.
     * @param string|null $descripcion Texto descriptivo.
     * @param float $precio Valor actualizado (precio o descuento).
     * @return bool True si la operación fue exitosa.
     */
    public function update($source, $id, $nombre, $descripcion, $precio) {
        try {
            $descripcion = $descripcion !== '' ? $descripcion : null;
            if ($source === 'combo') {
                $query = "UPDATE combos SET nombre = :nombre, descripcion = :descripcion, precio_total = :precio WHERE id_combo = :id";
                $stmt = $this->conn->prepare($query);
                $stmt->bindValue(':nombre', $nombre, PDO::PARAM_STR);
                if ($descripcion === null) {
                    $stmt->bindValue(':descripcion', null, PDO::PARAM_NULL);
                } else {
                    $stmt->bindValue(':descripcion', $descripcion, PDO::PARAM_STR);
                }
                $stmt->bindValue(':precio', number_format((float) $precio, 2, '.', ''), PDO::PARAM_STR);
                $stmt->bindValue(':id', $id, PDO::PARAM_INT);
                return $stmt->execute();
            } elseif ($source === 'oferta') {
                $query = "UPDATE ofertas SET nombre = :nombre, descripcion = :descripcion, porcentaje_descuento = :descuento WHERE id_oferta = :id";
                $stmt = $this->conn->prepare($query);
                $stmt->bindValue(':nombre', $nombre, PDO::PARAM_STR);
                if ($descripcion === null) {
                    $stmt->bindValue(':descripcion', null, PDO::PARAM_NULL);
                } else {
                    $stmt->bindValue(':descripcion', $descripcion, PDO::PARAM_STR);
                }
                $stmt->bindValue(':descuento', number_format((float) $precio, 2, '.', ''), PDO::PARAM_STR);
                $stmt->bindValue(':id', $id, PDO::PARAM_INT);
                return $stmt->execute();
            }
            return false;
        } catch (Exception $e) {
            error_log($e->getMessage());
            return false;
        }
    }

    /**
     * Cambiar el estado (Activo/Inactivo) de una promoción determinada.
     *
     * @param string $source Fuente del registro (combo|oferta).
     * @param int $id Identificador del registro.
     * @param string $estado Nuevo estado normalizado.
     * @return bool True si la actualización fue exitosa.
     */
    public function setEstado($source, $id, $estado = 'Inactivo') {
        try {
            if ($source === 'combo') {
                $query = "UPDATE combos SET estado = :estado WHERE id_combo = :id";
                $stmt = $this->conn->prepare($query);
                $stmt->bindValue(':estado', $estado, PDO::PARAM_STR);
                $stmt->bindValue(':id', $id, PDO::PARAM_INT);
                return $stmt->execute();
            } elseif ($source === 'oferta') {
                $estadoOferta = ($estado === 'Activo') ? 'Activa' : 'Inactiva';
                $query = "UPDATE ofertas SET estado = :estado WHERE id_oferta = :id";
                $stmt = $this->conn->prepare($query);
                $stmt->bindValue(':estado', $estadoOferta, PDO::PARAM_STR);
                $stmt->bindValue(':id', $id, PDO::PARAM_INT);
                return $stmt->execute();
            }
            return false;
        } catch (Exception $e) {
            error_log($e->getMessage());
            return false;
        }
    }

    /**
     * Construir un identificador compuesto fuente:id para el consumo del cliente.
     */
    private function composeIdentifier($source, $id) {
        return sprintf('%s:%d', $source, $id);
    }
}

?>