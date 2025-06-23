<?php
// C:\xampp\htdocs\PROYECTO2DS6\productos\index.php

require '../config/db.php';
require '../includes/header.php';

// 1. Obtener categorías
$cat_sql = "SELECT id, nombre FROM categorias ORDER BY nombre";
$cat_result = $mysqli->query($cat_sql);
$categorias = [];
while ($row = $cat_result->fetch_assoc()) {
    $categorias[] = $row;
}
$cat_result->free();

// 2. Leer categoría seleccionada
$categoria_id = isset($_GET['categoria_id']) ? intval($_GET['categoria_id']) : 0;

// 3. Consulta de productos (con filtro si aplica)
$sql = "SELECT p.id, p.nombre, p.precio, p.imagen, p.fecha_creacion, c.nombre AS categoria
        FROM productos p
        LEFT JOIN categorias c ON p.categoria_id = c.id";
if ($categoria_id > 0) {
    $sql .= " WHERE p.categoria_id = $categoria_id";
}
$sql .= " ORDER BY p.fecha_creacion DESC";
$result = $mysqli->query($sql);

if (!$result) {
    die("Error en la consulta de productos: " . $mysqli->error);
}

$productos = [];
while ($fila = $result->fetch_assoc()) {
    $productos[] = $fila;
}
$result->free();
?>

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" href="/PROYECTO2DS6/css/admin.css">
    <title>Productos</title>
</head>

<div class="page-header">
    <h2 class="page-title">Productos</h2>
    <div class="filter-container" style="display: flex; align-items: center; gap: 1.2rem; flex-wrap: wrap; justify-content: space-between;">
        <a href="create.php" class="btn btn-primary">+ Nuevo Producto</a>
        <form method="get" class="filter-form" style="margin:0; display: flex; align-items: center;">
            <select name="categoria_id" id="categoria_id" class="form-select" onchange="this.form.submit()">
                <option value="0">Todas las categorías</option>
                <?php foreach ($categorias as $cat): ?>
                    <option value="<?php echo $cat['id']; ?>" <?php if ($cat['id'] == $categoria_id) echo 'selected'; ?>>
                        <?php echo htmlspecialchars($cat['nombre']); ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </form>
    </div>
</div>

<?php if (count($productos) > 0): ?>
    <div class="table-container">
        <div class="table-responsive">
            <table class="table">
                <thead>
                    <tr>
                        <th>Nombre</th>
                        <th>Categoría</th>
                        <th>Precio</th>
                        <th>Imagen</th>
                        <th>Fecha Creación</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($productos as $prod): ?>
                        <tr>
                            <td><strong><?php echo htmlspecialchars($prod['nombre']); ?></strong></td>
                            <td>
                                <span class="category-badge">
                                    <?php echo htmlspecialchars($prod['categoria']); ?>
                                </span>
                            </td>
                            <td class="price-cell"><?php echo number_format((float)$prod['precio'], 2); ?></td>
                            <td>
                                <?php if ($prod['imagen']): ?>
                                    <img
                                        src="<?php echo htmlspecialchars($prod['imagen']); ?>"
                                        alt="Imagen del producto"
                                        class="product-image"
                                        loading="lazy"
                                    >
                                <?php else: ?>
                                    <span class="no-image">Sin imagen</span>
                                <?php endif; ?>
                            </td>
                            <td><?php echo htmlspecialchars($prod['fecha_creacion']); ?></td>
                            <td>
                                <div class="action-buttons">
                                    <a href="edit.php?id=<?php echo $prod['id']; ?>"
                                       class="btn btn-sm btn-warning"
                                       title="Editar producto">Editar</a>
                                    <a href="delete.php?id=<?php echo $prod['id']; ?>"
                                       class="btn btn-sm btn-danger"
                                       data-confirm="¿Eliminar producto <?php echo addslashes($prod['nombre']); ?>?"
                                       title="Eliminar producto">
                                       Eliminar
                                    </a>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
<?php else: ?>
    <div class="alert alert-info">No hay productos registrados para la categoría seleccionada.</div>
<?php endif; ?>

<?php require '../includes/footer.php'; ?>
