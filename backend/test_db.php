<?php
// backend/test_db.php
ini_set('display_errors', 1);
ini_set('display_log_errors', 1);
error_reporting(E_ALL);

require_once 'config/Database.php';

echo "Intentando conectar...<br>";
$database = new Database();
$db = $database->getConnection();

if ($db) {
    echo "¡Conexión exitosa a MySQL local!";
} else {
    echo "Error desconocido al conectar.";
}