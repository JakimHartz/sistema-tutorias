<?php
// backend/controllers/AuthController.php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Cabeceras obligatorias para una API REST segura y accesible desde el frontend
header("Access-Control-Allow-Origin: http://localhost:5173");
header("Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With");
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Content-Type: application/json; charset=UTF-8");

// Manejo del preflight request de CORS (peticiones tipo OPTIONS que hace el navegador)
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

// Asegurar que solo procesamos peticiones POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(["message" => "Método no permitido. Debe ser POST."]);
    exit();
}

// Incluir archivos de configuración y modelos
require_once '../config/Database.php';
require_once '../models/Usuario.php';

// Obtener la conexión a la base de datos
$database = new Database();
$db = $database->getConnection();

// Inicializar el modelo de Usuario
$usuario = new Usuario($db);

// Leer los datos JSON que envía Vue.js a través del cuerpo de la petición (raw body)
$data = json_decode(file_get_contents("php://input"));

// Validar que los campos no estén vacíos
if (!empty($data->num_empleado) && !empty($data->password)) {
    
    $usuario->num_empleado = $data->num_empleado;
    
    // Buscar si el número de empleado existe
    if ($usuario->buscarPorEmpleado()) {
        
        // Verificar la contraseña 
        // NOTA: Para producción escolar rápida usamos comparación directa. 
        // Si usaras contraseñas encriptadas aquí usarías: if(password_verify($data->password, $usuario->password))
        if ($data->password === $usuario->password) {
            
            // Credenciales correctas: Respondemos código 200 y los datos del perfil
            http_response_code(200);
            echo json_encode([
                "status" => "success",
                "message" => "Acceso concedido.",
                "user" => [
                    "id" => $usuario->id,
                    "nombre" => $usuario->nombre,
                    "rol" => $usuario->rol
                ]
            ]);
            
        } else {
            // Contraseña incorrecta
            http_response_code(401);
            echo json_encode(["status" => "error", "message" => "Contraseña incorrecta."]);
        }
    } else {
        // El número de empleado no existe
        http_response_code(404);
        echo json_encode(["status" => "error", "message" => "El usuario no se encuentra registrado."]);
    }
} else {
    // Datos incompletos en la petición
    http_response_code(400);
    echo json_encode(["status" => "error", "message" => "Datos incompletos. Ingrese usuario y contraseña."]);
}