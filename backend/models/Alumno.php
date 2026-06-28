<?php
// backend/models/Alumno.php

class Alumno {
    private $conn;
    private $table_name = "alumnos";

    public $id;
    public $matricula;
    public $nombre;
    public $profesor_id;

    public function __construct($db) {
        $this->conn = $db;
    }

    // Comprobar si la matrícula ya está registrada
    public function existeMatricula() {
        $query = "SELECT id FROM " . $this->table_name . " WHERE matricula = :matricula LIMIT 0,1";
        $stmt = $this->conn->prepare($query);
        $this->matricula = htmlspecialchars(strip_tags($this->matricula));
        $stmt->bindParam(":matricula", $this->matricula);
        $stmt->execute();
        return $stmt->rowCount() > 0;
    }

    // Crear un alumno (Manual o desde CSV)
    public function crear() {
        $query = "INSERT INTO " . $this->table_name . " (matricula, nombre, profesor_id) 
                  VALUES (:matricula, :nombre, :profesor_id)";
        
        $stmt = $this->conn->prepare($query);

        // Limpieza de datos
        $this->matricula = htmlspecialchars(strip_tags($this->matricula));
        $this->nombre = htmlspecialchars(strip_tags($this->nombre));
        
        // Manejar el valor nulo si no se le asigna un profesor inmediatamente
        $profesor = !empty($this->profesor_id) ? $this->profesor_id : null;

        $stmt->bindParam(":matricula", $this->matricula);
        $stmt->bindParam(":nombre", $this->nombre);
        $stmt->bindParam(":profesor_id", $profesor, $profesor ? PDO::PARAM_INT : PDO::PARAM_NULL);

        if($stmt->execute()) {
            return true;
        }
        return false;
    }
}