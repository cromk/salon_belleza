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

    public function create($a, $b, $c, $d, $e, $f, $g)
    {
        try {
            $query = "INSERT INTO {$this->table} (nombre,apellido,correo,telefono,usuario,clave,id_rol) VALUES(:a, :b, :c, :d, :e, :f, :g)"; //Consulta a ejecutar
            $stmt = $this->conn->prepare($query); //Preparando la consulta
            $stmt->bindParam(":a", $a, PDO::PARAM_STR); //Enviando parametro con su tipo de dato
            $stmt->bindParam(":b", $b, PDO::PARAM_STR); //Enviando parametro con su tipo de dato
            $stmt->bindParam(":c", $c, PDO::PARAM_STR); //Enviando parametro con su tipo de dato
            $stmt->bindParam(":d", $d, PDO::PARAM_STR); //Enviando parametro con su tipo de dato
            $stmt->bindParam(":e", $e, PDO::PARAM_STR); //Enviando parametro con su tipo de dato
            $stmt->bindParam(":f", $f, PDO::PARAM_STR); //Enviando parametro con su tipo de dato
            $stmt->bindParam(":g", $g, PDO::PARAM_INT); //Enviando parametro con su tipo de dato
            return $stmt->execute(); //Ejecutando consulta
        } catch (Exception $e) { //Manejo de errores
            error_log($e->getMessage()); //Imprimimos el error en consola
            return null; //Retornamos valor vacio
        }
    }
}
?>