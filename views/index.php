<?php 
// Iniciamos el buffer para capturar el contenido y enviarlo al layout
ob_start(); 
?>

<h2>Listado de Productos</h2>

<form method="GET" action="index.php" class="search-form">
    <input type="text" name="nombre" placeholder="Buscar por nombre..." 
           value="<?= htmlspecialchars($_GET['nombre'] ?? '') ?>">

    <select name="cat">
        <option value="">Todas las categorías</option>
        <?php foreach ($categorias as $c): ?>
            <option value="<?= $c['id_categoria'] ?>" 
                <?= (isset($_GET['cat']) && $_GET['cat'] == $c['id_categoria']) ? 'selected' : '' ?>>
                <?= htmlspecialchars($c['nombre_categoria']) ?>
            </option>
        <?php endforeach; ?>
    </select>

    <button type="submit">Buscar</button>
    <a href="index.php" class="btn-limpiar">Limpiar</a>
</form>

<br>

<table>
    <thead>
        <tr>
            <th>Nombre</th>
            <th>Precio</th>
            <th>Stock</th>
            <th>Imágenes</th>
            <th>Acciones</th>
        </tr>
    </thead>
    <tbody>
        <?php if (!empty($productos)): ?>
            <?php foreach ($productos as $p): ?>
            <tr>
                <td><?= htmlspecialchars($p['nombre']) ?></td>
                <td>$<?= number_format($p['precio'], 2) ?></td>
                
                <td>
                    <?php 
                    $cantidad = $p['stock_inventario'];
                    if ($cantidad > 8) { $clase = "stock-verde"; $texto = $cantidad . " unidades"; }
                    elseif ($cantidad >= 3) { $clase = "stock-amarillo"; $texto = $cantidad . " unidades"; }
                    elseif ($cantidad > 0) { $clase = "stock-rojo"; $texto = $cantidad . " unidades"; }
                    else { $clase = "stock-gris"; $texto = "Agotado"; }
                    ?>
                    <span class="<?= $clase ?>"><?= $texto ?></span>
                </td>

                <td><?= $this->model->contarImagenes($p['id_producto']) ?> img</td>

                <td>
                    <a href="index.php?action=editar&id=<?= $p['id_producto'] ?>">Editar</a> | 
                    <a href="index.php?action=eliminar&id=<?= $p['id_producto'] ?>" 
                       onclick="return confirm('¿Estás seguro de eliminar este producto?')">Eliminar</a>
                </td>
            </tr>
            <?php endforeach; ?>
        <?php else: ?>
            <tr>
                <td colspan="5" style="text-align:center;">No se encontraron productos.</td>
            </tr>
        <?php endif; ?>
    </tbody>
</table>

<?php 
// Finalizamos la captura del buffer
$contenido = ob_get_clean();
// Requerimos el layout principal
require 'layout.php'; 
?>