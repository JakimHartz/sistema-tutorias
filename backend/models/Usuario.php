<?php
// backend/models/Usuario.php

class Usuario {
    private $conn;
    private $table_name = "usuarios";

    // Propiedades del objeto
    public $id;
    public $num_empleado;
    public $nombre;
    public $password;
    public $rol;

    // Constructor que recibe la conexión a la BD
    public function __construct($db) {
        $this->conn = $db;
    }

    // Método para buscar un usuario por su número de empleado
    public function buscarPorEmpleado() {
        $query = "SELECT id, nombre, password, rol 
                  FROM " . $this->table_name . " 
                  WHERE num_empleado = :num_empleado 
                  LIMIT 0,1";

        $stmt = $this->conn->prepare($query);

        // Limpiar parámetros contra inyecciones SQL
        $this->num_empleado = htmlspecialchars(strip_tags($this->num_empleado));

        // Vincular parámetro
        $stmt->bindParam(":num_empleado", $this->num_empleado);

        $stmt->execute();

        // Si encuentra el registro, asigna los datos al objeto
        if ($stmt->rowCount() > 0) {
            // Quitamos PDO::ATTR_ASSOC y usamos FETCH_ASSOC directo como string o el fetch simple
            $row = $stmt->fetch(); 
            
            $this->id = $row['id'];
            $this->nombre = $row['nombre'];
            $this->password = $row['password'];
            $this->rol = $row['rol'];
            return true;
        }

        return false;
    }
}