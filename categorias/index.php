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

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" href="/PROYECTO2DS6/css/admin.css">
    <title>Categorías</title>
</head>

<div class="page-header">
    <h2 class="page-title">Categorías</h2>
    <a href="create.php" class="btn btn-primary categoria-btn">+ Nueva Categoría</a>
</div>

<?php if (count($cats) > 0): ?>
    <div class="table-container">
        <div class="table-responsive">
            <table class="table">
                <thead>
                    <tr>
                        <th scope="col">Nombre</th>
                        <th scope="col">Imagen</th>
                        <th scope="col">Fecha Creación</th>
                        <th scope="col">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($cats as $cat): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($cat['nombre']); ?></td>
                            <td>
                                <?php if ($cat['imagen']): ?>
                                    <img
                                        src="<?php echo htmlspecialchars($cat['imagen']); ?>"
                                        alt="Imagen de categoría"
                                        class="category-image"
                                        loading="lazy"
                                    >
                                <?php else: ?>
                                    <span class="no-image">Sin imagen</span>
                                <?php endif; ?>
                            </td>
                            <td><?php echo htmlspecialchars($cat['fecha_creacion']); ?></td>
                            <td>
                                <div class="action-buttons">
                                    <a
                                        href="edit.php?id=<?php echo $cat['id']; ?>"
                                        class="btn btn-sm btn-warning"
                                        title="Editar categoría"
                                    >
                                        Editar
                                    </a>
                                    <a href="delete.php?id=<?php echo $cat['id']; ?>"
                                       class="btn btn-sm btn-danger"
                                       data-confirm="¿Estás seguro de eliminar la categoría '<?php echo addslashes($cat['nombre']); ?>'?"
                                       title="Eliminar categoría">
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
    <div class="alert alert-info">No hay categorías registradas.</div>
<?php endif; ?>

<?php require '../includes/footer.php'; ?>
