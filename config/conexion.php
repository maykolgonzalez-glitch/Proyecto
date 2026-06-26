<?php
$host = "localhost";
$dbname = "proyecto";
$user = "Eadmin"; // Cambia según tu configuración
$pass = "12345";     // Cambia según tu configuración

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Error de conexión: " . $e->getMessage());
}
?>