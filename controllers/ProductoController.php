<?php
require_once __DIR__ . '/../models/Producto.php';

class ProductoController {
    private $model;
    private $pdo;

    public function __construct($pdo) { 
        $this->pdo = $pdo;
        $this->model = new Producto($pdo); 
    }

    public function gestionar() {
        $action = $_GET['action'] ?? 'listar';

        switch ($action) {
            case 'crear':
                $producto = null; // Definido para evitar error en la vista
                require __DIR__ . '/../views/form.php';
                break;

            case 'editar':
                if (!isset($_GET['id'])) die("ID no especificado");
                $producto = $this->model->obtenerPorId($_GET['id']);
                require __DIR__ . '/../views/form.php';
                break;

            case 'guardar':
                // 1. Guardar los datos básicos y obtener el ID
                $id_producto = $this->model->guardar($_POST);

                // 2. Procesar la imagen si se envió una nueva
                if (!empty($_FILES['imagen']['name'])) {
                    if (!is_dir('uploads')) mkdir('uploads', 0777, true);
                    
                    $nombreArchivo = time() . '_' . basename($_FILES['imagen']['name']);
                    $ruta = 'uploads/' . $nombreArchivo;
                    
                    if (move_uploaded_file($_FILES['imagen']['tmp_name'], $ruta)) {
                        $this->model->guardarImagen($id_producto, $ruta);
                    }
                }
                header('Location: index.php');
                exit;

            case 'eliminar':
                if (isset($_GET['id'])) {
                    $this->model->eliminar($_GET['id']);
                }
                header('Location: index.php');
                exit;

            default:
                $nombre = $_GET['nombre'] ?? null;
                $categoria = $_GET['cat'] ?? null;
                $productos = $this->model->obtenerTodos($nombre, $categoria);
                $categorias = $this->model->obtenerCategorias();
                
                require __DIR__ . '/../views/home.php';
                break;
        }
    }
}