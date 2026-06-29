-- ============================================================
-- Sistema de Control de Tutorías
-- Archivo: backend/schema.sql
-- Instrucciones: Importa este archivo en phpMyAdmin o ejecuta
--   en tu terminal:  mysql -u root -p < schema.sql
-- ============================================================

-- Crear la base de datos si no existe
CREATE DATABASE IF NOT EXISTS sistema_tutorias
    CHARACTER SET utf8mb4
    COLLATE utf8mb4_unicode_ci;

USE sistema_tutorias;

--  Tabla de usuarios (admin y profesores) 
CREATE TABLE IF NOT EXISTS usuarios (
    id           INT AUTO_INCREMENT PRIMARY KEY,
    num_empleado VARCHAR(20)  NOT NULL UNIQUE,
    nombre       VARCHAR(120) NOT NULL,
    password     VARCHAR(255) NOT NULL,
    rol          ENUM('admin', 'profesor') NOT NULL DEFAULT 'profesor',
    created_at   TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--  Tabla de alumnos 
CREATE TABLE IF NOT EXISTS alumnos (
    id           INT AUTO_INCREMENT PRIMARY KEY,
    matricula    CHAR(10)     NOT NULL UNIQUE,
    nombre       VARCHAR(120) NOT NULL,
    profesor_id  INT NULL,
    created_at   TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    -- Llave foránea: un alumno pertenece a un profesor (puede ser NULL)
    CONSTRAINT fk_alumno_profesor
        FOREIGN KEY (profesor_id) REFERENCES usuarios(id)
        ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--  Tabla de sesiones de tutoría (bitácora) 
CREATE TABLE IF NOT EXISTS sesiones (
    id            INT AUTO_INCREMENT PRIMARY KEY,
    alumno_id     INT         NOT NULL,
    profesor_id   INT         NOT NULL,
    asistencia    ENUM('asistio', 'falta') NOT NULL DEFAULT 'asistio',
    observaciones TEXT        NOT NULL,
    fecha_sesion  TIMESTAMP   DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_sesion_alumno
        FOREIGN KEY (alumno_id)   REFERENCES alumnos(id)  ON DELETE CASCADE,
    CONSTRAINT fk_sesion_profesor
        FOREIGN KEY (profesor_id) REFERENCES usuarios(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--  Datos iniciales de prueba 
-- Usuario administrador por defecto (contraseña: admin123)
INSERT IGNORE INTO usuarios (num_empleado, nombre, password, rol)
VALUES ('admin01', 'Administrador del Sistema', 'admin123', 'admin');

-- Profesor de ejemplo (contraseña: prof123)
INSERT IGNORE INTO usuarios (num_empleado, nombre, password, rol)
VALUES ('EMP-001', 'Dr. Carlos Ramírez López', 'prof123', 'profesor');

-- Alumnos de ejemplo asignados al profesor con id=2
INSERT IGNORE INTO alumnos (matricula, nombre, profesor_id) VALUES
    ('2024010001', 'Ana Lucía Torres Vega',   2),
    ('2024010002', 'Luis Fernando García',    2),
    ('2024010003', 'Karla Sofía Mendoza',     2);
