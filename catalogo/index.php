<?php
// C:\xampp\htdocs\PROYECTO2DS6\catalogo\index.php

require '../config/db.php';
require 'header_catalogo.php';

// Parámetros GET
$catParam  = isset($_GET['cat'])  ? (int)$_GET['cat']  : 0;
$pageParam = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;

// 1) Obtén todas las categorías
$sqlCats = "SELECT id, nombre, imagen FROM categorias ORDER BY nombre";
$resCats = $mysqli->query($sqlCats);
$cats = [];
while ($fila = $resCats->fetch_assoc()) {
    $cats[] = $fila;
}
$resCats->free();

// 2) Cuenta total de productos con filtro opcional
$porPagina = 9;
if ($catParam > 0) {
    $stmtCount = $mysqli->prepare(
        "SELECT COUNT(*) AS total FROM productos WHERE categoria_id = ?"
    );
    $stmtCount->bind_param('i', $catParam);
    $stmtCount->execute();
    $total = $stmtCount->get_result()->fetch_assoc()['total'];
    $stmtCount->close();
} else {
    $total = $mysqli->query("SELECT COUNT(*) AS total FROM productos")
                    ->fetch_assoc()['total'];
}
$totalPaginas = max(1, (int) ceil($total / $porPagina));
$offset = ($pageParam - 1) * $porPagina;

// 3) Obtener productos paginados
if ($catParam > 0) {
    $stmt = $mysqli->prepare(
        "SELECT p.id, p.nombre AS prod_nombre, p.precio, p.imagen AS prod_imagen, c.nombre AS cat_nombre
        FROM productos p
        JOIN categorias c ON p.categoria_id = c.id
        WHERE p.categoria_id = ?
        ORDER BY p.nombre
        LIMIT ? OFFSET ?"
    );
    $stmt->bind_param('iii', $catParam, $porPagina, $offset);
} else {
    $stmt = $mysqli->prepare(
        "SELECT p.id, p.nombre AS prod_nombre, p.precio, p.imagen AS prod_imagen, c.nombre AS cat_nombre
        FROM productos p
        LEFT JOIN categorias c ON p.categoria_id = c.id
        ORDER BY p.nombre
        LIMIT ? OFFSET ?"
    );
    $stmt->bind_param('ii', $porPagina, $offset);
}
$stmt->execute();
$resProds = $stmt->get_result();

// Redirigir si pedimos página sin resultados
if ($resProds->num_rows === 0 && $pageParam > 1) {
    header("Location: ?cat={$catParam}&page={$totalPaginas}");
    exit;
}
?>

<!-- Grid de Categorías -->
<?php if ($cats): ?>
    <div class="categories-grid">
        <h1 style="margin-bottom:2rem;grid-column:1/-1;">Categorías</h1>
        <?php foreach ($cats as $cat): ?>
            <a href="?cat=<?php echo (int)$cat['id']; ?>" class="category-card">
                <?php if ($cat['imagen']): ?>
                    <img src="<?php echo htmlspecialchars($cat['imagen']); ?>" class="category-image" alt="<?php echo htmlspecialchars($cat['nombre']); ?>" loading="lazy">
                <?php else: ?>
                    <div class="no-image">Sin imagen</div>
                <?php endif; ?>
                <div class="category-title"><?php echo htmlspecialchars($cat['nombre']); ?></div>
            </a>
        <?php endforeach; ?>
    </div>
<?php else: ?>
    <div style="background:#eef;padding:1rem;border-radius:8px;">No hay categorías disponibles.</div>
<?php endif; ?>

<!-- Título de sección -->
<?php
if ($catParam > 0) {
    $actual = array_filter($cats, fn($c) => (int)$c['id'] === $catParam);
    $actual = array_shift($actual);
    $titulo = $actual
        ? "Productos en “" . htmlspecialchars($actual['nombre']) . "”"
        : "Categoría no encontrada";
} else {
    $titulo = "Todos los Productos";
}
?>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" href="/PROYECTO2DS6/css/catalogo.css">
    <title>Catálogo</title>
</head>
<body>
<div class="catalogo-main-wrapper">
<h3 class="page-title"><?php echo $titulo; ?></h3>

<!-- Filtro de categorías -->
<div class="catalogo-filter-container">
    <div style="flex:1"></div>
    <form class="catalogo-filter-form">
        <label for="cat" class="filter-label">Filtrar por categoría:</label>
        <select name="cat" id="cat" class="form-select" onchange="this.form.submit()">
            <option value="0">Todas las categorías</option>
            <?php foreach ($cats as $cat): ?>
                <option value="<?php echo $cat['id']; ?>" <?php if ($cat['id'] == $catParam) echo 'selected'; ?>>
                    <?php echo htmlspecialchars($cat['nombre']); ?>
                </option>
            <?php endforeach; ?>
        </select>
    </form>
</div>

<!-- Grid de Productos -->
<?php if ($resProds->num_rows > 0): ?>
    <div class="products-grid">
        <?php while ($prod = $resProds->fetch_assoc()): ?>
            <div class="product-card">
                <?php if ($prod['prod_imagen']): ?>
                    <img
                        src="<?php echo htmlspecialchars($prod['prod_imagen']); ?>"
                        class="product-image"
                        alt="<?php echo htmlspecialchars($prod['prod_nombre']); ?>"
                        loading="lazy"
                    >
                <?php else: ?>
                    <div class="no-image-placeholder">
                        Sin imagen disponible
                    </div>
                <?php endif; ?>
                <div class="product-info">
                    <h5 class="product-name"><?php echo htmlspecialchars($prod['prod_nombre']); ?></h5>
                    <span class="product-category"><?php echo htmlspecialchars($prod['cat_nombre']); ?></span>
                    <div class="product-price"><?php echo number_format((float)$prod['precio'], 2); ?></div>
                </div>
            </div>
        <?php endwhile; ?>
    </div>
<?php else: ?>
    <div class="alert alert-info">No hay productos para mostrar en esta categoría.</div>
<?php endif; ?>

<!-- Paginación -->
<?php if ($totalPaginas > 1): ?>
    <nav class="pagination-container" aria-label="Paginación">
        <ul class="pagination">
            <?php
            $prev = max(1, $pageParam - 1);
            $next = min($totalPaginas, $pageParam + 1);
            ?>
            <li class="page-item <?php echo $pageParam === 1 ? 'disabled' : ''; ?>">
                <a class="page-link nav-arrow" href="?cat=<?php echo $catParam ?>&page=<?php echo $prev ?>" aria-label="Página anterior">
                    ‹
                </a>
            </li>
            <?php for ($i = 1; $i <= $totalPaginas; $i++): ?>
                <li class="page-item <?php echo $i === $pageParam ? 'active' : ''; ?>">
                    <a class="page-link" href="?cat=<?php echo $catParam ?>&page=<?php echo $i ?>" aria-label="Página <?php echo $i ?>">
                        <?php echo $i ?>
                    </a>
                </li>
            <?php endfor; ?>
            <li class="page-item <?php echo $pageParam === $totalPaginas ? 'disabled' : ''; ?>">
                <a class="page-link nav-arrow" href="?cat=<?php echo $catParam ?>&page=<?php echo $next ?>" aria-label="Página siguiente">
                    ›
                </a>
            </li>
        </ul>
    </nav>
<?php endif; ?>

<?php require '../includes/footer.php'; ?>
</div>
<script src="/PROYECTO2DS6/js/script.js"></script>
</body>
</html>
