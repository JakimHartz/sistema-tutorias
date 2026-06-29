<?php
// backend/controllers/AuthController.php

ini_set('display_errors', 1);
error_reporting(E_ALL);

header("Access-Control-Allow-Origin: http://localhost:5173");
header("Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With");
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Content-Type: application/json; charset=UTF-8");

// El navegador envía una petición OPTIONS antes de cada POST (preflight CORS)
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

require_once '../config/Database.php';
require_once '../models/Usuario.php';
require_once '../models/Profesor.php';

$database = new Database();
$db       = $database->getConnection();

$action = $_GET['action'] ?? '';

// =========================================================
// RUTA 1: REGISTRO DE PROFESOR  (?action=registrar_profesor)
// =========================================================
if ($action === 'registrar_profesor') {

    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        http_response_code(405);
        echo json_encode(["status" => "error", "message" => "Método no permitido."]);
        exit();
    }

    $data = json_decode(file_get_contents("php://input"));

    if (empty($data->num_empleado) || empty($data->nombre) || empty($data->password)) {
        http_response_code(400);
        echo json_encode(["status" => "error", "message" => "Por favor, rellene todos los campos."]);
        exit();
    }

    $profesor = new Profesor($db);
    $profesor->num_empleado = $data->num_empleado;
    $profesor->nombre       = $data->nombre;
    $profesor->password     = $data->password;

    if ($profesor->existeNumEmpleado()) {
        http_response_code(400);
        echo json_encode(["status" => "error", "message" => "El número de empleado ya está registrado."]);
        exit();
    }

    if ($profesor->crear()) {
        http_response_code(200);
        echo json_encode(["status" => "success", "message" => "Profesor registrado exitosamente."]);
    } else {
        http_response_code(500);
        echo json_encode(["status" => "error", "message" => "No se pudo guardar en la base de datos."]);
    }
    exit();
}

// =========================================================
// RUTA 2: LOGIN (sin parámetro ?action)
// =========================================================
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(["message" => "Método no permitido. Usa POST."]);
    exit();
}

$data = json_decode(file_get_contents("php://input"));

if (empty($data->num_empleado) || empty($data->password)) {
    http_response_code(400);
    echo json_encode(["status" => "error", "message" => "Datos incompletos. Ingrese usuario y contraseña."]);
    exit();
}

$usuario = new Usuario($db);
$usuario->num_empleado = $data->num_empleado;

if (!$usuario->buscarPorEmpleado()) {
    http_response_code(404);
    echo json_encode(["status" => "error", "message" => "El usuario no se encuentra registrado."]);
    exit();
}

// Comparación directa de contraseña (prototipo escolar)
// En producción real usar: password_verify($data->password, $usuario->password)
if ($data->password !== $usuario->password) {
    http_response_code(401);
    echo json_encode(["status" => "error", "message" => "Contraseña incorrecta."]);
    exit();
}

http_response_code(200);
echo json_encode([
    "status"  => "success",
    "message" => "Acceso concedido.",
    "user"    => [
        "id"     => $usuario->id,
        "nombre" => $usuario->nombre,
        "rol"    => $usuario->rol,
    ]
]);
