<?php
// C:\xampp\htdocs\PROYECTO2DS6\catalogo\index.php

require '../config/db.php';
require 'header_catalogo.php';

// 1) Leer parámetros GET: categoría y página
$catParam  = isset($_GET['cat'])   ? (int)$_GET['cat']   : 0;
$pageParam = isset($_GET['page'])  ? (int)$_GET['page']  : 1;
if ($pageParam < 1) {
    $pageParam = 1;
}

// 2) Cargar todas las categorías para mostrar el grid superior
$sqlCats = "SELECT id, nombre, imagen FROM categorias ORDER BY nombre";
$resCats = $mysqli->query($sqlCats);
if (!$resCats) {
    die("Error al obtener categorías: " . $mysqli->error);
}
$cats = [];
while ($fila = $resCats->fetch_assoc()) {
    $cats[] = $fila; // ['id'], ['nombre'], ['imagen']
}
$resCats->free();

// 3) Preparar paginación para productos
$porPagina = 9; // mostrar 9 productos por página
$offset    = ($pageParam - 1) * $porPagina;

// 4) Contar total de productos (aplica filtro de categoría si $catParam > 0)
if ($catParam > 0) {
    $sqlCount = "SELECT COUNT(*) AS total 
                  FROM productos 
                  WHERE categoria_id = ?";
    $stmtCount = $mysqli->prepare($sqlCount);
    $stmtCount->bind_param('i', $catParam);
    $stmtCount->execute();
    $resCount = $stmtCount->get_result();
    $total    = $resCount->fetch_assoc()['total'];
    $stmtCount->close();
} else {
    $sqlCount = "SELECT COUNT(*) AS total FROM productos";
    $resCount = $mysqli->query($sqlCount);
    $total    = $resCount->fetch_assoc()['total'];
    $resCount->free();
}

$totalPaginas = (int) ceil($total / $porPagina);
if ($totalPaginas < 1) {
    $totalPaginas = 1;
}

// 5) Obtener productos de la página actual (con JOIN para traer nombre de categoría)
if ($catParam > 0) {
    $sqlProds = "SELECT p.id,
                        p.nombre   AS prod_nombre,
                        p.precio,
                        p.imagen   AS prod_imagen,
                        c.nombre   AS cat_nombre
                 FROM productos p
                 INNER JOIN categorias c ON p.categoria_id = c.id
                 WHERE p.categoria_id = ?
                 ORDER BY p.nombre
                 LIMIT ? OFFSET ?";
    $stmt = $mysqli->prepare($sqlProds);
    $stmt->bind_param('iii', $catParam, $porPagina, $offset);
    $stmt->execute();
    $resProds = $stmt->get_result();
} else {
    $sqlProds = "SELECT p.id,
                        p.nombre   AS prod_nombre,
                        p.precio,
                        p.imagen   AS prod_imagen,
                        c.nombre   AS cat_nombre
                 FROM productos p
                 LEFT JOIN categorias c ON p.categoria_id = c.id
                 ORDER BY p.nombre
                 LIMIT ? OFFSET ?";
    $stmt = $mysqli->prepare($sqlProds);
    $stmt->bind_param('ii', $porPagina, $offset);
    $stmt->execute();
    $resProds = $stmt->get_result();
}

// Si no hay producto en esta página y el usuario pidió una página > 1, forzar a la última
if ($resProds->num_rows === 0 && $pageParam > 1) {
    header("Location: ?cat={$catParam}&page=" . $totalPaginas);
    exit;
}
?>

<!-- 6) Grid de categorías -->
<h3 class="mb-4">Categorías</h3>
<?php if (count($cats) > 0): ?>
    <div class="row row-cols-1 row-cols-md-4 g-4 mb-5">
        <?php foreach ($cats as $cat): ?>
            <div class="col">
                <div class="card h-100 shadow-sm">
                    <?php if ($cat['imagen']): ?>
                        <img
                            src="<?php echo htmlspecialchars($cat['imagen']); ?>"
                            class="card-img-top"
                            alt="<?php echo htmlspecialchars($cat['nombre']); ?>"
                            style="height:180px; object-fit:cover;"
                            loading="lazy"
                        >
                    <?php else: ?>
                        <div class="d-flex justify-content-center align-items-center bg-light" style="height:180px;">
                            <span class="text-muted">Sin imagen</span>
                        </div>
                    <?php endif; ?>
                    <div class="card-body text-center">
                        <a
                            href="?cat=<?php echo (int)$cat['id']; ?>"
                            class="stretched-link text-decoration-none text-dark"
                        >
                            <h5 class="card-title"><?php echo htmlspecialchars($cat['nombre']); ?></h5>
                        </a>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
<?php else: ?>
    <div class="alert alert-info">No hay categorías disponibles.</div>
<?php endif; ?>

<!-- 7) Título de “Catálogo” o nombre de categoría seleccionada -->
<?php
if ($catParam > 0):
    // Buscar el nombre de la categoría actual
    $catActual = null;
    foreach ($cats as $c) {
        if ((int)$c['id'] === $catParam) {
            $catActual = $c;
            break;
        }
    }
    $titulo = $catActual
        ? "Productos en “" . htmlspecialchars($catActual['nombre']) . "”"
        : "Categoría no encontrada";
else:
    $titulo = "Todos los Productos";
endif;
?>
<h3 class="mb-4"><?php echo $titulo; ?></h3>

<!-- 8) Grid de productos -->
<?php if ($resProds->num_rows > 0): ?>
    <div class="row row-cols-1 row-cols-md-3 g-4">
        <?php while ($prod = $resProds->fetch_assoc()): ?>
            <div class="col">
                <div class="card h-100 shadow-sm">
                    <?php if ($prod['prod_imagen']): ?>
                        <img
                            src="<?php echo htmlspecialchars($prod['prod_imagen']); ?>"
                            class="card-img-top"
                            alt="<?php echo htmlspecialchars($prod['prod_nombre']); ?>"
                            style="height:200px; object-fit:cover;"
                            loading="lazy"
                        >
                    <?php else: ?>
                        <div class="d-flex justify-content-center align-items-center bg-light" style="height:200px;">
                            <span class="text-muted">Sin imagen</span>
                        </div>
                    <?php endif; ?>
                    <div class="card-body d-flex flex-column">
                        <h5 class="card-title"><?php echo htmlspecialchars($prod['prod_nombre']); ?></h5>
                        <p class="card-text text-truncate"><?php echo htmlspecialchars($prod['cat_nombre']); ?></p>
                        <p class="card-text fw-bold">$ <?php echo number_format((float)$prod['precio'], 2); ?></p>
                    </div>
                </div>
            </div>
        <?php endwhile; ?>
    </div>
<?php else: ?>
    <div class="alert alert-info">No hay productos para mostrar.</div>
<?php endif; ?>

<!-- 9) Paginación Bootstrap -->
<?php if ($totalPaginas > 1): ?>
    <nav aria-label="Paginación" class="mt-4">
        <ul class="pagination justify-content-center">
            <?php
            // “Anterior”
            $prevPage = max(1, $pageParam - 1);
            $disabledPrev = ($pageParam === 1) ? ' disabled' : '';
            ?>
            <li class="page-item<?php echo $disabledPrev; ?>">
                <a
                    class="page-link"
                    href="?cat=<?php echo $catParam; ?>&page=<?php echo $prevPage; ?>"
                    aria-label="Anterior"
                >
                    <span aria-hidden="true">&laquo;</span>
                </a>
            </li>

            <?php
            // Mostrar un rango de páginas (por ejemplo, de 1 a $totalPaginas)
            for ($i = 1; $i <= $totalPaginas; $i++):
                $active = ($i === $pageParam) ? ' active' : '';
            ?>
                <li class="page-item<?php echo $active; ?>">
                    <a class="page-link" href="?cat=<?php echo $catParam; ?>&page=<?php echo $i; ?>">
                        <?php echo $i; ?>
                    </a>
                </li>
            <?php endfor; ?>

            <?php
            // “Siguiente”
            $nextPage = min($totalPaginas, $pageParam + 1);
            $disabledNext = ($pageParam === $totalPaginas) ? ' disabled' : '';
            ?>
            <li class="page-item<?php echo $disabledNext; ?>">
                <a
                    class="page-link"
                    href="?cat=<?php echo $catParam; ?>&page=<?php echo $nextPage; ?>"
                    aria-label="Siguiente"
                >
                    <span aria-hidden="true">&raquo;</span>
                </a>
            </li>
        </ul>
    </nav>
<?php endif; ?>

<?php
// 10) Cerrar conexión y requerir footer
$stmt->close();
require '../includes/footer.php';
