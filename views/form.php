<?php 
ob_start(); 
$isEdit = isset($producto);
?>

<h2><?= $isEdit ? 'Editar Producto' : 'Nuevo Producto' ?></h2>

<form method="POST" action="index.php?action=guardar" enctype="multipart/form-data">
    
    <?php if ($isEdit): ?>
        <input type="hidden" name="id" value="<?= $producto['id_producto'] ?>">
    <?php endif; ?>

    <label>Nombre:</label>
    <input type="text" name="nombre" value="<?= $isEdit ? htmlspecialchars($producto['nombre']) : '' ?>" required>

    <label>Categoría (Escribe una nueva o elige una sugerida):</label>
    <input type="text" list="categorias-list" name="categoria_manual" placeholder="Escribe la categoría aquí" 
           value="<?= $isEdit ? htmlspecialchars($producto['nombre_categoria'] ?? '') : '' ?>">
    
    <datalist id="categorias-list">
        <?php 
        $todasLasCategorias = $this->model->obtenerCategorias(); 
        foreach ($todasLasCategorias as $cat): ?>
            <option value="<?= htmlspecialchars($cat['nombre_categoria']) ?>">
        <?php endforeach; ?>
    </datalist>

    <label>Precio:</label>
    <input type="number" step="0.01" name="precio" value="<?= $isEdit ? $producto['precio'] : '' ?>" required>

    <label>Stock:</label>
    <input type="number" name="stock" value="<?= $isEdit ? $producto['stock_inventario'] : '' ?>" required>

    <?php if ($isEdit && !empty($imagenes)): ?>
        <label>Imágenes Actuales:</label>
        <div id="existing-container" style="display: flex; gap: 10px; flex-wrap: wrap; margin-bottom: 15px;">
            <?php foreach ($imagenes as $img): ?>
                <div style="position: relative; display: inline-block;">
                    <img src="<?= $img['ruta_imagen'] ?>" style="width: 100px; height: 100px; object-fit: cover; border: 2px solid #ddd; border-radius: 5px;">
                    <button type="button" onclick="eliminarDeBD(<?= $img['id_imagen'] ?>)" style="position: absolute; top: -8px; right: -8px; background: #ff4d4d; color: white; border: none; border-radius: 50%; width: 22px; height: 22px; cursor: pointer;">X</button>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

    <label>Añadir nuevas imágenes:</label>
    <input type="file" name="imagenes[]" id="input-imagenes" multiple accept="image/*">
    <div id="preview-container" style="display: flex; gap: 10px; margin-top: 10px; flex-wrap: wrap;"></div>

    <br><br>
    <button type="submit">Guardar Producto</button>
    <a href="index.php">Cancelar</a>
</form>

<script>
// --- Lógica para nuevas imágenes ---
let dataTransfer = new DataTransfer();
const input = document.getElementById('input-imagenes');
const previewContainer = document.getElementById('preview-container');

input.addEventListener('change', function() {
    for (let file of this.files) {
        dataTransfer.items.add(file);
    }
    input.files = dataTransfer.files;
    renderPreviews();
});

function renderPreviews() {
    previewContainer.innerHTML = '';
    Array.from(input.files).forEach((file, index) => {
        const reader = new FileReader();
        reader.onload = function(e) {
            const div = document.createElement('div');
            div.style.position = 'relative';
            div.style.display = 'inline-block';
            div.innerHTML = `
                <img src="${e.target.result}" style="width: 100px; height: 100px; object-fit: cover; border: 2px solid #ddd; border-radius: 5px;">
                <button type="button" onclick="eliminarImagenNueva(${index})" style="position: absolute; top: -8px; right: -8px; background: #ff4d4d; color: white; border: none; border-radius: 50%; width: 22px; height: 22px; cursor: pointer;">X</button>
            `;
            previewContainer.appendChild(div);
        }
        reader.readAsDataURL(file);
    });
}

function eliminarImagenNueva(index) {
    let newDataTransfer = new DataTransfer();
    let files = input.files;
    for (let i = 0; i < files.length; i++) {
        if (i !== index) newDataTransfer.items.add(files[i]);
    }
    input.files = newDataTransfer.files;
    dataTransfer = newDataTransfer;
    renderPreviews();
}

// --- Lógica para eliminar existentes de la BD ---
function eliminarDeBD(id_imagen) {
    if(confirm("¿Seguro que quieres borrar esta imagen permanentemente de la base de datos?")) {
        fetch('index.php?action=eliminarImagen&id=' + id_imagen)
        .then(() => location.reload());
    }
}
</script>

<?php 
$contenido = ob_get_clean();
require 'layout.php'; 
?>