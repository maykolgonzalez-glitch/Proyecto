<?php 
// Inicialización segura de variables
$producto = $producto ?? null;
$categorias = $categorias ?? []; 
$isEdit = !empty($producto);

ob_start(); 
?>

<style>
    .acciones-form { display: flex; gap: 10px; align-items: center; margin-top: 15px; }
    .btn { padding: 10px 20px; text-decoration: none; border-radius: 5px; font-weight: bold; }
    .btn-editar { background: #3498db; color: white; border: none; cursor: pointer; }
    .btn-eliminar { background: #e74c3c; color: white; }
    .img-preview { width: 120px; height: 120px; object-fit: cover; border: 2px solid #ddd; border-radius: 8px; }
</style>

<h2><?= $isEdit ? 'Editar Producto' : 'Nuevo Producto' ?></h2>

<form method="POST" action="index.php?action=guardar" enctype="multipart/form-data">
    
    <?php if ($isEdit): ?>
        <input type="hidden" name="id" value="<?= htmlspecialchars($producto['id_producto'] ?? $producto['id'] ?? '') ?>">
    <?php endif; ?>

    <label>Nombre:</label>
    <input type="text" name="nombre" value="<?= $isEdit ? htmlspecialchars($producto['nombre'] ?? '') : '' ?>" required>

    <label>Categoría:</label>
    <input type="text" list="categorias-list" name="categoria_manual" value="<?= $isEdit ? htmlspecialchars($producto['nombre_categoria'] ?? '') : '' ?>">
    
    <datalist id="categorias-list">
        <?php foreach ($categorias as $cat): ?>
            <option value="<?= htmlspecialchars($cat['nombre_categoria']) ?>">
        <?php endforeach; ?>
    </datalist>

    <label>Precio:</label>
    <input type="number" step="0.01" name="precio" value="<?= $isEdit ? ($producto['precio'] ?? '') : '' ?>" required>

    <label>Stock:</label>
    <input type="number" name="stock" value="<?= $isEdit ? ($producto['stock_inventario'] ?? '') : '' ?>" required>

    <label>Imagen del producto:</label>
    <?php if ($isEdit): ?>
        <div style="margin-bottom: 15px;">
            <p>Imagen actual:</p>
            <?php 
                // Buscamos el ID de forma segura para crear la ruta: assets/img/producto/ID.jpeg
                $id_archivo = $producto['id_producto'] ?? $producto['id'] ?? '0';
                $rutaImagen = "assets/img/producto/" . $id_archivo . ".jpeg";
            ?>
            <img src="<?= $rutaImagen ?>" class="img-preview" 
                 onerror="this.onerror=null; this.src='assets/img/default.jpg';">
            
    <?php endif; ?>
    
    <label>Subir nueva imagen:</label>
    <input type="file" name="imagen" id="input-imagen" accept="image/*">
    <div id="preview-container" style="margin-top: 10px;"></div>

    <div class="acciones-form">
        <button type="submit" class="btn btn-editar">Guardar Producto</button>
        <a href="index.php" class="btn btn-eliminar">Cancelar</a>
    </div>
</form>

<script>
const input = document.getElementById('input-imagen');
const previewContainer = document.getElementById('preview-container');

input.addEventListener('change', function() {
    previewContainer.innerHTML = ''; 
    const file = this.files[0];
    if (file) {
        const reader = new FileReader();
        reader.onload = function(e) {
            previewContainer.innerHTML = `<img src="${e.target.result}" class="img-preview">`;
        }
        reader.readAsDataURL(file);
    }
});
</script>

<?php 
$contenido = ob_get_clean();
require 'layout.php'; 
?>