<?php
// C:\xampp\htdocs\PROYECTO2DS6\categorias\index.php

require '../config/db.php';
require '../includes/header.php';

// Solo Admin (rol 01) puede acceder a este módulo
if ($_SESSION['usuario']['rol'] !== '01') {
    // Si quieren, redirigir al catálogo público
    header('Location: ../catalogo/index.php');
    exit;
}

// Obtener todas las categorías
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
    <a href="create.php" class="btn btn-primary">Nueva Categoría</a>
</div>

<?php if (count($cats) > 0): ?>
    <div class="table-responsive">
        <table class="table table-striped table-bordered align-middle">
            <thead class="table-dark">
                <tr>
                    <th scope="col">ID</th>
                    <th scope="col">Nombre</th>
                    <th scope="col">Imagen</th>
                    <th scope="col">Fecha Creación</th>
                    <th scope="col">Acciones</th>
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
                                    loading="lazy"
                                >
                            <?php else: ?>
                                —
                            <?php endif; ?>
                        </td>
                        <td><?php echo htmlspecialchars($cat['fecha_creacion']); ?></td>
                        <td>
                            <a
                                href="edit.php?id=<?php echo $cat['id']; ?>"
                                class="btn btn-sm btn-warning me-1"
                                title="Editar categoría"
                            >
                                Editar
                            </a>
                            <a
                                href="delete.php?id=<?php echo $cat['id']; ?>"
                                class="btn btn-sm btn-danger"
                                onclick="return confirm('¿Eliminar categoría <?php echo addslashes($cat['nombre']); ?>?');"
                                title="Eliminar categoría"
                            >
                                Eliminar
                            </a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
<?php else: ?>
    <div class="alert alert-info">No hay categorías registradas.</div>
<?php endif; ?>

<?php require '../includes/footer.php'; ?>
