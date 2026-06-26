<?php
require_once 'models/Producto.php';

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
                require 'views/form.php';
                break;

            case 'editar':
                if (!isset($_GET['id'])) die("ID no especificado");
                $producto = $this->model->obtenerPorId($_GET['id']);
                // Obtenemos las imágenes ya guardadas en la BD para mostrarlas
                $imagenes = $this->model->obtenerImagenesPorProducto($_GET['id']);
                require 'views/form.php';
                break;

            case 'guardar':
                $this->model->guardar($_POST);
                $id_producto = !empty($_POST['id']) ? $_POST['id'] : $this->pdo->lastInsertId();

                if (!empty($_FILES['imagenes']['name'][0])) {
                    if (!is_dir('uploads')) mkdir('uploads', 0777, true);

                    foreach ($_FILES['imagenes']['tmp_name'] as $index => $tmpName) {
                        if (!empty($tmpName)) {
                            $nombreArchivo = time() . '_' . $_FILES['imagenes']['name'][$index];
                            $ruta = 'uploads/' . $nombreArchivo;
                            if (move_uploaded_file($tmpName, $ruta)) {
                                $this->model->guardarImagen($id_producto, $ruta);
                            }
                        }
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

            // Nueva acción para eliminar una imagen específica sin borrar el producto
            case 'eliminarImagen':
                if (isset($_GET['id'])) {
                    $this->model->eliminarImagenPorId($_GET['id']);
                }
                exit; // Solo termina la petición, el JS maneja el feedback

            default:
                $nombre = $_GET['nombre'] ?? null;
                $categoria = $_GET['cat'] ?? null;
                $productos = $this->model->obtenerTodos($nombre, $categoria);
                $categorias = $this->model->obtenerCategorias();
                require 'views/index.php';
                break;
        }
    }
}