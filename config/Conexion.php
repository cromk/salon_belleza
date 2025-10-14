<?php
class Conexion {
    private $host = "localhost";
    private $db_name = "salon_belleza_db";
    private $username = "root";
    private $password = "";
    public $conn;

    public function conectar(){
        $this->conn = null;
        try {
            $this->conn = new PDO(
                "mysql:host=" . $this->host . ";dbname=" . $this->db_name,
                $this->username,
                $this->password
            );

            // Configuraciones recomendadas para PDO
            $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION); // Excepciones en errores
            $this->conn->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC); // Fetch asociativo por defecto
            $this->conn->exec("SET NAMES utf8"); // Codificación UTF-8

        } catch(PDOException $exception){
            // Lanzar excepción para que se maneje donde se use la clase
            throw new Exception("Error de conexión: " . $exception->getMessage());
        }
        return $this->conn;
    }
}
?>