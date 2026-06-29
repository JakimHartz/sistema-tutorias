<?php
// backend/models/Profesor.php

class Profesor {
    private $conn;
    private $table_name = "usuarios";

    public $id;
    public $num_empleado;
    public $nombre;
    public $password;

    public function __construct($db) {
        $this->conn = $db;
    }

    // Obtener todos los profesores registrados
    public function listarTodos() {
        $query = "SELECT id, num_empleado, nombre 
                  FROM " . $this->table_name . " 
                  WHERE rol = 'profesor'
                  ORDER BY nombre ASC";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    // Verificar si el número de empleado ya existe (evitar duplicados)
    public function existeNumEmpleado() {
        $query = "SELECT id FROM " . $this->table_name . " WHERE num_empleado = :num_empleado LIMIT 1";
        $stmt = $this->conn->prepare($query);
        $this->num_empleado = htmlspecialchars(strip_tags($this->num_empleado));
        $stmt->bindParam(":num_empleado", $this->num_empleado);
        $stmt->execute();
        return $stmt->rowCount() > 0;
    }

    // Registrar un nuevo profesor
    public function crear() {
        $query = "INSERT INTO " . $this->table_name . " (num_empleado, nombre, password, rol)
                  VALUES (:num_empleado, :nombre, :password, 'profesor')";
        $stmt = $this->conn->prepare($query);

        $this->num_empleado = htmlspecialchars(strip_tags($this->num_empleado));
        $this->nombre       = htmlspecialchars(strip_tags($this->nombre));

        $stmt->bindParam(":num_empleado", $this->num_empleado);
        $stmt->bindParam(":nombre",       $this->nombre);
        $stmt->bindParam(":password",     $this->password);

        return $stmt->execute();
    }
}
