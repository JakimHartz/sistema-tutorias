<?php
// backend/controllers/SesionController.php
ini_set('display_errors', 1);
error_reporting(E_ALL);

header("Access-Control-Allow-Origin: http://localhost:5173");
header("Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With");
header("Access-Control-Allow-Methods: POST, GET, OPTIONS");
header("Content-Type: application/json; charset=UTF-8");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

require_once '../config/Database.php';
require_once '../models/Sesion.php';

$database = new Database();
$db = $database->getConnection();
$sesion = new Sesion($db);

$action = $_GET['action'] ?? '';

// ========================================================
// 1. OBTENER LISTA DE ALUMNOS ASIGNADOS A UN PROFESOR
// ========================================================
if ($action === 'alumnos_asignados' && $_SERVER['REQUEST_METHOD'] === 'GET') {
    $profesor_id = $_GET['profesor_id'] ?? '';

    if (!empty($profesor_id)) {
        $query = "SELECT id, matricula, nombre FROM alumnos WHERE profesor_id = :profesor_id";
        $stmt = $db->prepare($query);
        $stmt->bindParam(":profesor_id", $profesor_id, \PDO::PARAM_INT);
        $stmt->execute();
        $alumnos = $stmt->fetchAll();

        http_response_code(200);
        echo json_encode($alumnos);
    } else {
        http_response_code(400);
        echo json_encode(["status" => "error", "message" => "ID de profesor requerido."]);
    }
}

// ========================================================
// 2. GUARDAR BITÁCORA DE LA SESIÓN
// ========================================================
elseif ($action === 'guardar_bitacora' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = json_decode(file_get_contents("php://input"));

    if (!empty($data->alumno_id) && !empty($data->profesor_id) && !empty($data->asistencia) && !empty($data->observaciones)) {
        
        $sesion->alumno_id = $data->alumno_id;
        $sesion->profesor_id = $data->profesor_id;
        $sesion->asistencia = $data->asistencia; // 'asistio' o 'falta'
        $sesion->observaciones = $data->observaciones;

        if ($sesion->registrar()) {
            http_response_code(201);
            echo json_encode(["status" => "success", "message" => "Bitácora guardada con éxito."]);
        } else {
            http_response_code(500);
            echo json_encode(["status" => "error", "message" => "Error al guardar la sesión."]);
        }
    } else {
        http_response_code(400);
        echo json_encode(["status" => "error", "message" => "Datos de sesión incompletos."]);
    }
}

// ========================================================
// 3. EXPORTAR REPORTE (AÑADIENDO PSICOPEDAGÓGICO Y DESERCIÓN)
// ========================================================
elseif ($action === 'exportar_reporte' && $_SERVER['REQUEST_METHOD'] === 'GET') {
    $profesor_id = $_GET['profesor_id'] ?? '';
    
    if(empty($profesor_id)) {
        http_response_code(400);
        echo json_encode(["status" => "error", "message" => "Falta ID del profesor."]);
        exit();
    }

    $datos = $sesion->obtenerPorProfesor($profesor_id);

    // Cambiar las cabeceras para forzar la descarga de un archivo CSV local
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename=reporte_tutorias_psicopedagogico.csv');
    
    // Abrir el puntero de salida
    $output = fopen('php://output', 'w');
    
    // Escribir los encabezados del archivo final
    //fputcsv($output, ['Matricula', 'Alumno', 'Asistencia', 'Observaciones del Tutor', 'Fecha', 'Evaluacion Psicopedagogica', 'Alerta Desercion']);
    fputcsv($output, ['Matricula', 'Alumno', 'Asistencia', 'Observaciones del Tutor', 'Fecha', 'Nivel Academico', 'Alerta Desercion']);

    // Inyectar renglones agregando las cláusulas estáticas del cliente
    foreach ($datos as $row) {
        // Buscamos si en el texto de observaciones se guardaron las banderas de nivel y riesgo
        // O las procesamos limpiamente
        fputcsv($output, [
            $row['matricula'],
            $row['alumno_nombre'],
            $row['asistencia'],
            $row['observaciones'],
            $row['fecha_sesion'],
            // Se autogenera o lee dinámicamente desde el registro de la sesión
            $row['nivel_academico'] ?? 'Licenciatura (Regular)', 
            $row['alerta_desercion'] ?? 'Bajo Riesgo'
        ]);
    }
    
    fclose($output);
    exit();
} else {
    http_response_code(404);
    echo json_encode(["status" => "error", "message" => "Endpoint no válido."]);
}