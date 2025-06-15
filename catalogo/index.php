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
<h3 class="mb-4">Categorías</h3>
<?php if ($cats): ?>
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
<h3 class="mb-4"><?php echo $titulo; ?></h3>

<!-- Filtro de categorías -->
<div class="d-flex justify-content-end align-items-center mb-3">
    <form method="get" class="mb-0">
        <div class="row g-2 align-items-center">
            <div class="col-auto">
                <label for="cat" class="col-form-label">Filtrar por categoría:</label>
            </div>
            <div class="col-auto">
                <select name="cat" id="cat" class="form-select" onchange="this.form.submit()">
                    <option value="0">Todas</option>
                    <?php foreach ($cats as $cat): ?>
                        <option value="<?php echo $cat['id']; ?>" <?php if ($cat['id'] == $catParam) echo 'selected'; ?>>
                            <?php echo htmlspecialchars($cat['nombre']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>
    </form>
</div>

<!-- Grid de Productos -->
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

<!-- Paginación -->
<?php if ($totalPaginas > 1): ?>
    <nav aria-label="Paginación" class="mt-4">
        <ul class="pagination justify-content-center">
            <?php
            $prev = max(1, $pageParam - 1);
            $next = min($totalPaginas, $pageParam + 1);
            ?>
            <li class="page-item <?php echo $pageParam === 1 ? 'disabled' : ''; ?>">
                <a class="page-link" href="?cat=<?php echo $catParam ?>&page=<?php echo $prev ?>">
                    &laquo;
                </a>
            </li>
            <?php for ($i = 1; $i <= $totalPaginas; $i++): ?>
                <li class="page-item <?php echo $i === $pageParam ? 'active' : ''; ?>">
                    <a class="page-link" href="?cat=<?php echo $catParam ?>&page=<?php echo $i ?>">
                        <?php echo $i ?>
                    </a>
                </li>
            <?php endfor; ?>
            <li class="page-item <?php echo $pageParam === $totalPaginas ? 'disabled' : ''; ?>">
                <a class="page-link" href="?cat=<?php echo $catParam ?>&page=<?php echo $next ?>">
                    &raquo;
                </a>
            </li>
        </ul>
    </nav>
<?php endif; ?>

<?php
$stmt->close();
require '../includes/footer.php';
?>

<script src="/PROYECTO2DS6/js/script.js"></script>
</body>
</html>
