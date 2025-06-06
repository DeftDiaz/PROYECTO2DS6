<?php
// C:\xampp\htdocs\PROYECTO2DS6\productos\index.php

require '../config/db.php';
require '../includes/header.php';

$sql = "SELECT p.id, p.nombre, p.precio, p.imagen, p.fecha_creacion, c.nombre AS categoria
        FROM productos p
        LEFT JOIN categorias c ON p.categoria_id = c.id
        ORDER BY p.fecha_creacion DESC";
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
    <h2>Productos</h2>
    <a href="create.php" class="btn btn-primary">Nuevo Producto</a>
</div>

<?php if (count($productos) > 0): ?>
    <div class="table-responsive">
        <table class="table table-hover table-bordered align-middle">
            <thead class="table-dark">
                <tr>
                    <th>ID</th>
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
                        <td><?php echo htmlspecialchars($prod['id']); ?></td>
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
                               onclick="return confirm('¿Eliminar producto <?php echo addslashes($prod['nombre']); ?>?');"
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
