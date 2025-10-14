<?php
class usuarioModel {
    private $table = "usuarios";
    private $conn;

    public function __construct(PDO $db) {
        $this->conn = $db;
    }

    public function verificarUsuario($usuario, $password) {
        try {
            $query = "SELECT * FROM {$this->table} WHERE clave = :password AND usuario = :usuario"; //Consulta a ejecutar
            $stmt = $this->conn->prepare($query); //Preparando la consulta
            $stmt->bindParam(":usuario", $usuario, PDO::PARAM_STR); //Enviando parametro con su tipo de dato
            $stmt->bindParam(":password", $password, PDO::PARAM_STR); //Enviando parametro con su tipo de dato
            $stmt->execute(); //Ejecutando consulta
            return $stmt->fetch(PDO::FETCH_ASSOC); //Ejecutando consulta y retornamos el resultado obtenido
        } catch (Exception $e) { //Manejo de errores
            error_log($e->getMessage()); //Imprimimos el error en consola
            return null; //Retornamos valor vacio
        }
    }
}
?>