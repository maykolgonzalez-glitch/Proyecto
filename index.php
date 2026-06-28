<?php
// 1. Cargamos dependencias necesarias
require_once 'config/conexion.php';
require_once 'controllers/ProductoController.php';

// 2. Determinamos la página o acción
$page = $_GET['page'] ?? 'home';

// 3. Lógica de enrutamiento
switch ($page) {
    case 'home':
        // Instanciamos el controlador pasando la conexión $pdo (definida en conexion.php)
        $controller = new ProductoController($pdo);
        
        // Llamamos a gestionar. Como el controlador hace el 'require' de la vista,
        // no necesitamos capturar el retorno aquí.
        $controller->gestionar(); 
        break;

    case 'form':
        // Si el formulario también necesita el controlador, haz lo mismo:
        $controller = new ProductoController($pdo);
        $controller->gestionar();
        break;

    default:
        header("HTTP/1.0 404 Not Found");
        echo "404 - Página no encontrada";
        break;
}