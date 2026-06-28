<?php
// backend/models/Sesion.php

class Sesion {
    private $conn;
    private $table_name = "sesiones";

    public $id;
    public $alumno_id;
    public $profesor_id;
    public $asistencia;
    public $observaciones;
    public $fecha_sesion;

    public function __construct($db) {
        $this->conn = $db;
    }

    // Crear registro de tutoría (Bitácora)
    public function registrar() {
        $query = "INSERT INTO " . $this->table_name . " (alumno_id, profesor_id, asistencia, observaciones) 
                  VALUES (:alumno_id, :profesor_id, :asistencia, :observaciones)";
        
        $stmt = $this->conn->prepare($query);

        // Limpieza de datos contra inyecciones XSS / SQL
        $this->alumno_id = (int)$this->alumno_id;
        $this->profesor_id = (int)$this->profesor_id;
        $this->asistencia = htmlspecialchars(strip_tags($this->asistencia));
        $this->observaciones = htmlspecialchars(strip_tags($this->observaciones));

        // Vincular parámetros
        $stmt->bindParam(":alumno_id", $this->alumno_id);
        $stmt->bindParam(":profesor_id", $this->profesor_id);
        $stmt->bindParam(":asistencia", $this->asistencia);
        $stmt->bindParam(":observaciones", $this->observaciones);

        if($stmt->execute()) {
            return true;
        }
        return false;
    }

    // Obtener las tutorías de un profesor (Para los reportes)
    public function obtenerPorProfesor($profesor_id) {
        $query = "SELECT s.id, a.matricula, a.nombre AS alumno_nombre, s.asistencia, s.observaciones, s.fecha_sesion 
                  FROM " . $this->table_name . " s
                  JOIN alumnos a ON s.alumno_id = a.id
                  WHERE s.profesor_id = :profesor_id
                  ORDER BY s.fecha_sesion DESC";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":profesor_id", $profesor_id, \PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }
}