<?php
// 1. Configuración de conexión a la base de datos
$host = 'localhost';
$dbname = 'proyecto';
$user = 'Eadmin';
$pass = '12345'; // Ajusta esto según tu configuración de XAMPP/WAMP/MAMP

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Error de conexión: " . $e->getMessage());
}

// 2. Cargar el controlador
require_once 'controllers/ProductoController.php';

// 3. Inicializar el controlador y ejecutar la gestión
$controller = new ProductoController($pdo);
$controller->gestionar();
?>