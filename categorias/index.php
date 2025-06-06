<?php
require '../config/db.php';
require '../includes/header.php';

// 1) Obtener todas las categorías
$sql = "SELECT * FROM categorias ORDER BY fecha_creacion DESC";
$result = $mysqli->query($sql);
if (!$result) {
    die("Error en la consulta de categorías: " . $mysqli->error);
}

$cats = [];
while ($fila = $result->fetch_assoc()) {
    $cats[] = $fila;
}
$result->free();
?>

<div class="d-flex justify-content-between align-items-center mb-3">
    <h2>Categorías</h2>
    <?php if ($_SESSION['usuario']['rol'] === '01'): ?>
        <!-- Solo Admin (rol 01) ve el botón para crear -->
        <a href="create.php" class="btn btn-primary">Nueva Categoría</a>
    <?php endif; ?>
</div>

<?php if (count($cats) > 0): ?>
    <div class="table-responsive">
        <table class="table table-striped table-bordered align-middle">
            <thead class="table-dark">
                <tr>
                    <th>ID</th>
                    <th>Nombre</th>
                    <th>Imagen</th>
                    <th>Fecha Creación</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($cats as $cat): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($cat['id']); ?></td>
                        <td><?php echo htmlspecialchars($cat['nombre']); ?></td>
                        <td>
                            <?php if ($cat['imagen']): ?>
                                <img
                                    src="<?php echo htmlspecialchars($cat['imagen']); ?>"
                                    alt="Thumb"
                                    style="width:50px; height:50px; object-fit:cover;"
                                >
                            <?php else: ?>
                                —
                            <?php endif; ?>
                        </td>
                        <td><?php echo htmlspecialchars($cat['fecha_creacion']); ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
<?php else: ?>
    <div class="alert alert-info">No hay categorías registradas.</div>
<?php endif; ?>

<?php require '../includes/footer.php'; ?>
