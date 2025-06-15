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

<div class="d-flex justify-content-between align-items-center mb-3">
    <h2 class="mb-0">Productos</h2>
    <div class="d-flex align-items-center">
        <form method="get" class="mb-0 me-2">
            <div class="row g-2 align-items-center">
                <div class="col-auto">
                    <label for="categoria_id" class="col-form-label">Filtrar por categoría:</label>
                </div>
                <div class="col-auto">
                    <select name="categoria_id" id="categoria_id" class="form-select" onchange="this.form.submit()">
                        <option value="0">Todas</option>
                        <?php foreach ($categorias as $cat): ?>
                            <option value="<?php echo $cat['id']; ?>" <?php if ($cat['id'] == $categoria_id) echo 'selected'; ?>>
                                <?php echo htmlspecialchars($cat['nombre']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>
        </form>
        <a href="create.php" class="btn btn-primary">Nuevo Producto</a>
    </div>
</div>

<?php if (count($productos) > 0): ?>
    <div class="table-responsive">
        <table class="table table-hover table-bordered align-middle">
            <thead class="table-dark">
                <tr>
                    <th>Nombre</th>
                    <th>Categoría</th>
                    <th>Precio ($)</th>
                    <th>Imagen</th>
                    <th>Fecha Creación</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($productos as $prod): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($prod['nombre']); ?></td>
                        <td><?php echo htmlspecialchars($prod['categoria']); ?></td>
                        <td><?php echo number_format((float)$prod['precio'], 2); ?></td>
                        <td>
                            <?php if ($prod['imagen']): ?>
                                <img
                                    src="<?php echo htmlspecialchars($prod['imagen']); ?>"
                                    alt="Thumb"
                                    style="width:50px; height:50px; object-fit:cover;"
                                >
                            <?php else: ?>
                                —
                            <?php endif; ?>
                        </td>
                        <td><?php echo htmlspecialchars($prod['fecha_creacion']); ?></td>
                        <td>
                            <a href="edit.php?id=<?php echo $prod['id']; ?>"
                               class="btn btn-sm btn-warning me-1">Editar</a>
                            <a href="delete.php?id=<?php echo $prod['id']; ?>"
                               class="btn btn-sm btn-danger"
                               data-confirm="¿Eliminar producto <?php echo addslashes($prod['nombre']); ?>?"
                            >Eliminar</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
<?php else: ?>
    <div class="alert alert-info">No hay productos registrados.</div>
<?php endif; ?>

<?php require '../includes/footer.php'; ?>
