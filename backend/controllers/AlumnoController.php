<?php
// backend/controllers/AlumnoController.php
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
require_once '../models/Alumno.php';

$database = new Database();
$db = $database->getConnection();
$alumno = new Alumno($db);

$action = $_GET['action'] ?? '';

// ========================================================
// REQUERIMIENTO 1: ALTA MANUAL (JSON POST)
// ========================================================
if ($action === 'crear_manual' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = json_decode(file_get_contents("php://input"));

    if (!empty($data->matricula) && !empty($data->nombre)) {
        $alumno->matricula = $data->matricula;
        $alumno->nombre = $data->nombre;
        $alumno->profesor_id = $data->profesor_id ?? null;

        if ($alumno->existeMatricula()) {
            http_response_code(400);
            echo json_encode(["status" => "error", "message" => "La matrícula ya está registrada."]);
            exit();
        }

        if ($alumno->crear()) {
            http_response_code(201);
            echo json_encode(["status" => "success", "message" => "Alumno registrado exitosamente."]);
        } else {
            http_response_code(500);
            echo json_encode(["status" => "error", "message" => "No se pudo registrar al alumno."]);
        }
    } else {
        http_response_code(400);
        echo json_encode(["status" => "error", "message" => "Datos incompletos."]);
    }
}

// ========================================================
// REQUERIMIENTO 2: CARGA MASIVA MEDIANTE HOJA DE CÁLCULO (CSV)
// ========================================================
elseif ($action === 'carga_masiva' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_FILES['archivo']) && $_FILES['archivo']['error'] === UPLOAD_ERR_OK) {
        $fileTmpPath = $_FILES['archivo']['tmp_name'];
        
        // Abrir el archivo CSV local en modo lectura
        if (($handle = fopen($fileTmpPath, "r")) !== FALSE) {
            $insertados = 0;
            $duplicados = 0;
            
            // Opcional: Si tu archivo Excel/CSV incluye títulos en la primera línea, 
            // descomenta la siguiente línea para brincarla:
            // fgetcsv($handle, 1000, ",");

            // Leer renglón por renglón el archivo separado por comas
            while (($row = fgetcsv($handle, 1000, ",")) !== FALSE) {
                // Suponiendo que la Estructura del CSV es: Matrícula, Nombre Completo
                if (count($row) >= 2) {
                    $alumno->matricula = trim($row[0]);
                    $alumno->nombre = trim($row[1]);
                    $alumno->profesor_id = null; // Inicialmente sin profesor asignado

                    if (!$alumno->existeMatricula()) {
                        if ($alumno->crear()) {
                            $insertados++;
                        }
                    } else {
                        $duplicados++;
                    }
                }
            }
            fclose($handle);

            http_response_code(200);
            echo json_encode([
                "status" => "success",
                "message" => "Procesamiento completado.",
                "detalles" => [
                    "nuevos_alumnos" => $insertados,
                    "duplicados_omitidos" => $duplicados
                ]
            ]);
        } else {
            http_response_code(500);
            echo json_encode(["status" => "error", "message" => "No se pudo leer el archivo cargado."]);
        }
    } else {
        http_response_code(400);
        echo json_encode(["status" => "error", "message" => "No se recibió un archivo válido."]);
    }
} else {
    http_response_code(404);
    echo json_encode(["status" => "error", "message" => "Acción no encontrada."]);
}