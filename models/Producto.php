<?php
class Producto {
    private $pdo;

    public function __construct($pdo) { 
        $this->pdo = $pdo; 
    }

    // --- Lógica de categorización ---

    private function detectarCategoria($nombre) {
        $nombre = strtolower($nombre);
        
        if (strpos($nombre, 'filtro') !== false) return 'Repuestos';
        if (strpos($nombre, 'turbo') !== false) return 'Sistema de Turbo';
        if (strpos($nombre, 'bujía') !== false || strpos($nombre, 'batería') !== false || 
            strpos($nombre, 'switch') !== false || strpos($nombre, 'bobina') !== false) return 'Sistema Eléctrico';
        if (strpos($nombre, 'faros') !== false || strpos($nombre, 'luces') !== false) return 'Iluminación';
        if (strpos($nombre, 'alternador') !== false || strpos($nombre, 'sensor') !== false) return 'Motor';
        if (strpos($nombre, 'aceite') !== false) return 'Lubricantes';
        
        $partes = explode(' ', trim($nombre));
        return ucfirst($partes[0]); 
    }

    public function obtenerOCrearCategoria($nombre_categoria) {
        $nombre_normalizado = trim($nombre_categoria);
        
        // Buscamos si existe ignorando mayúsculas/minúsculas
        $stmt = $this->pdo->prepare("SELECT id_categoria FROM Categorias WHERE LOWER(nombre_categoria) = LOWER(?)");
        $stmt->execute([$nombre_normalizado]);
        $cat = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($cat) {
            return $cat['id_categoria'];
        } else {
            $stmt = $this->pdo->prepare("INSERT INTO Categorias (nombre_categoria) VALUES (?)");
            $stmt->execute([$nombre_normalizado]);
            return $this->pdo->lastInsertId();
        }
    }

    public function limpiarCategoriasVacias() {
        $sql = "DELETE FROM Categorias WHERE id_categoria NOT IN (SELECT DISTINCT id_categoria FROM Productos WHERE id_categoria IS NOT NULL)";
        $this->pdo->exec($sql);
    }

    // --- Gestión de Productos ---

    public function guardar($datos) {
        $nombre = $datos['nombre'] ?? '';
        $precio = $datos['precio'] ?? 0;
        $stock = $datos['stock'] ?? 0;
        $id = $datos['id'] ?? null;
        
        // Si escribiste algo, $nombre_cat toma ese valor. 
        // Si no escribiste nada, el sistema lo detecta solo.
        if (!empty($datos['categoria_manual'])) {
            $nombre_cat = $datos['categoria_manual'];
        } else {
            $nombre_cat = $this->detectarCategoria($nombre);
        }

        // Esto busca el nombre en la BD. Si no existe, lo crea.
        $id_categoria = $this->obtenerOCrearCategoria($nombre_cat);

        if (!empty($id)) {
            $sql = "UPDATE Productos SET nombre = ?, precio = ?, stock_inventario = ?, id_categoria = ? WHERE id_producto = ?";
            $this->pdo->prepare($sql)->execute([$nombre, $precio, $stock, $id_categoria, $id]);
        } else {
            $sql = "INSERT INTO Productos (nombre, precio, stock_inventario, id_categoria) VALUES (?, ?, ?, ?)";
            $this->pdo->prepare($sql)->execute([$nombre, $precio, $stock, $id_categoria]);
        }
    }

    public function obtenerTodos($nombre = null, $id_categoria = null) {
        $sql = "SELECT p.*, c.nombre_categoria FROM Productos p 
                LEFT JOIN Categorias c ON p.id_categoria = c.id_categoria WHERE 1=1";
        $params = [];
        if (!empty($nombre)) {
            $sql .= " AND p.nombre LIKE :nombre";
            $params['nombre'] = '%' . $nombre . '%';
        }
        if (!empty($id_categoria)) {
            $sql .= " AND p.id_categoria = :cat";
            $params['cat'] = $id_categoria;
        }
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function obtenerPorId($id) {
        $stmt = $this->pdo->prepare("SELECT p.*, c.nombre_categoria FROM Productos p 
                                     LEFT JOIN Categorias c ON p.id_categoria = c.id_categoria 
                                     WHERE p.id_producto = ?");
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function obtenerCategorias() {
        return $this->pdo->query("SELECT * FROM Categorias")->fetchAll(PDO::FETCH_ASSOC);
    }

    public function eliminar($id) {
        $imagenes = $this->obtenerImagenesPorProducto($id);
        foreach ($imagenes as $img) {
            if (file_exists($img['ruta_imagen'])) unlink($img['ruta_imagen']);
        }
        $this->pdo->prepare("DELETE FROM Producto_Imagenes WHERE id_producto = ?")->execute([$id]);
        $this->pdo->prepare("DELETE FROM Productos WHERE id_producto = ?")->execute([$id]);
        $this->limpiarCategoriasVacias();
    }

    // --- Gestión de Imágenes ---

    public function obtenerImagenesPorProducto($id_producto) {
        $stmt = $this->pdo->prepare("SELECT * FROM Producto_Imagenes WHERE id_producto = ?");
        $stmt->execute([$id_producto]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Corregido: Se ha mantenido este método para evitar el error de "undefined method"
    public function contarImagenes($id_producto) {
        $stmt = $this->pdo->prepare("SELECT COUNT(*) FROM Producto_Imagenes WHERE id_producto = ?");
        $stmt->execute([$id_producto]);
        return (int)$stmt->fetchColumn();
    }

    public function guardarImagen($id_producto, $ruta) {
        $stmt = $this->pdo->prepare("INSERT INTO Producto_Imagenes (id_producto, ruta_imagen) VALUES (?, ?)");
        $stmt->execute([$id_producto, $ruta]);
    }

    public function eliminarImagenPorId($id_imagen) {
        $stmt = $this->pdo->prepare("SELECT ruta_imagen FROM Producto_Imagenes WHERE id_imagen = ?");
        $stmt->execute([$id_imagen]);
        $img = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($img && file_exists($img['ruta_imagen'])) {
            unlink($img['ruta_imagen']);
        }
        $this->pdo->prepare("DELETE FROM Producto_Imagenes WHERE id_imagen = ?")->execute([$id_imagen]);
    }
}