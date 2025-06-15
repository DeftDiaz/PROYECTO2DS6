<?php
// C:\xampp\htdocs\PROYECTO2DS6\productos\edit.php

require '../config/db.php';
require '../includes/header.php';

$errors = [];
$nombre = '';
$descripcion = '';
$precio = '';
$categoria_id = '';
$rutaImagen = '';
$id = 0;

// 1) Verificar ID por GET
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    die("ID de producto no válido.");
}
$id = (int) $_GET['id'];

// 2) Obtener lista de categorías
$sqlCats = "SELECT id, nombre FROM categorias ORDER BY nombre";
$resCats = $mysqli->query($sqlCats);
if (!$resCats) {
    die("Error al obtener categorías: " . $mysqli->error);
}
$cats = [];
while ($fila = $resCats->fetch_assoc()) {
    $cats[] = $fila;
}
$resCats->free();

// 3) Si no es POST, precargar datos del producto
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    $sql = "SELECT * FROM productos WHERE id = ?";
    $stmt = $mysqli->prepare($sql);
    $stmt->bind_param('i', $id);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->num_rows !== 1) {
        die("Producto no encontrado.");
    }
    $prod = $result->fetch_assoc();
    $nombre = $prod['nombre'];
    $descripcion = $prod['descripcion'];
    $precio = $prod['precio'];
    $rutaImagen = $prod['imagen'];         // Ruta existente
    $categoria_id = $prod['categoria_id'];
    $stmt->close();
} else {
    // 4) Procesar POST para actualizar
    $nombre = trim($_POST['nombre'] ?? '');
    $descripcion = trim($_POST['descripcion'] ?? '');
    $precio = trim($_POST['precio'] ?? '');
    $categoria_id = (int)($_POST['categoria_id'] ?? 0);
    $rutaExistente = trim($_POST['ruta_existente'] ?? '');

    if ($nombre === '') {
        $errors[] = 'El nombre es obligatorio.';
    }
    if (!is_numeric($precio) || (float)$precio < 0) {
        $errors[] = 'El precio debe ser un número válido.';
    }
    $validCatIds = array_map('intval', array_column($cats, 'id'));
    if (!in_array((int)$categoria_id, $validCatIds, true)) {
        $errors[] = 'Categoría no válida.';
    }

    // 5) Manejar archivo nuevo
    $nuevaRuta = $rutaExistente;
    if (isset($_FILES['imagen']) && $_FILES['imagen']['error'] !== UPLOAD_ERR_NO_FILE) {
        if ($_FILES['imagen']['error'] !== UPLOAD_ERR_OK) {
            $errors[] = 'Error al subir la nueva imagen.';
        } else {
            if ($_FILES['imagen']['size'] > 2 * 1024 * 1024) {
                $errors[] = 'La nueva imagen supera 2 MB.';
            }
            $allowedExt = ['jpg', 'jpeg', 'png', 'gif'];
            $tmpName = $_FILES['imagen']['tmp_name'];
            $origName = basename($_FILES['imagen']['name']);
            $ext = strtolower(pathinfo($origName, PATHINFO_EXTENSION));

            if (!in_array($ext, $allowedExt, true)) {
                $errors[] = 'Formato de imagen no válido. Solo JPG, PNG o GIF.';
            } else {
                $nuevoNombre = uniqid('prod_') . '.' . $ext;
                $destino = __DIR__ . '/../img/productos/' . $nuevoNombre;

                if (move_uploaded_file($tmpName, $destino)) {
                    $nuevaRuta = '/PROYECTO2DS6/img/productos/' . $nuevoNombre;
                    // Borrar la imagen antigua (si existe)
                    if ($rutaExistente && file_exists(__DIR__ . '/..' . $rutaExistente)) {
                        @unlink(__DIR__ . '/..' . $rutaExistente);
                    }
                } else {
                    $errors[] = 'No se pudo guardar la nueva imagen en el servidor.';
                }
            }
        }
    }

    // 6) Si no hay errores, actualizar en BD
    if (empty($errors)) {
        $sqlUpd = "UPDATE productos 
                    SET nombre = ?, descripcion = ?, precio = ?, imagen = ?, categoria_id = ?, fecha_actualizacion = NOW()
                    WHERE id = ?";
        $stmt2 = $mysqli->prepare($sqlUpd);
        if (!$stmt2) {
            die("Error al preparar UPDATE de producto: " . $mysqli->error);
        }
        $stmt2->bind_param('ssdsii', $nombre, $descripcion, $precio, $nuevaRuta, $categoria_id, $id);
        if ($stmt2->execute()) {
            header('Location: index.php');
            exit;
        } else {
            $errors[] = "Error al actualizar en BD: " . $stmt2->error;
        }
        $stmt2->close();
    }

    $rutaImagen = $nuevaRuta;
}
?>

<div class="row justify-content-center">
    <div class="col-lg-8">
        <h2>Editar Producto</h2>

        <?php if (!empty($errors)): ?>
            <div class="alert alert-danger">
                <ul class="mb-0">
                    <?php foreach ($errors as $e): ?>
                        <li><?php echo htmlspecialchars($e); ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>

        <form method="post" enctype="multipart/form-data" novalidate>
            <!-- Campo oculto para ruta existente -->
            <input type="hidden" name="ruta_existente" value="<?php echo htmlspecialchars($rutaImagen); ?>">

            <div class="mb-3">
                <label for="nombre" class="form-label">Nombre</label>
                <input
                    type="text"
                    id="nombre"
                    name="nombre"
                    class="form-control"
                    value="<?php echo htmlspecialchars($nombre); ?>"
                    required
                >
            </div>

            <div class="mb-3">
                <label for="categoria_id" class="form-label">Categoría</label>
                <select
                    id="categoria_id"
                    name="categoria_id"
                    class="form-select"
                    required
                >
                    <option value="" disabled>-- Selecciona una categoría --</option>
                    <?php foreach ($cats as $c): ?>
                        <option
                            value="<?php echo $c['id']; ?>"
                            <?php if ($categoria_id == $c['id']) echo 'selected'; ?>
                        >
                            <?php echo htmlspecialchars($c['nombre']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="mb-3">
                <label for="precio" class="form-label">Precio ($)</label>
                <input
                    type="number"
                    step="0.01"
                    min="0"
                    id="precio"
                    name="precio"
                    class="form-control"
                    value="<?php echo htmlspecialchars($precio); ?>"
                    required
                >
            </div>

            <?php if ($rutaImagen): ?>
                <div class="mb-3">
                    <label class="form-label">Imagen actual</label><br>
                    <img
                        src="<?php echo htmlspecialchars($rutaImagen); ?>"
                        alt="Imagen producto"
                        style="width:100px; height:100px; object-fit:cover; margin-bottom:8px;"
                    >
                </div>
            <?php endif; ?>

            <div class="mb-3">
                <label for="imagen" class="form-label">Reemplazar Imagen 
                    <small class="text-muted">(opcional)</small>
                </label>
                <input
                    type="file"
                    id="imagen"
                    name="imagen"
                    class="form-control"
                    accept="image/*"
                >
                <div class="form-text">Si no subes nada, se mantendrá la imagen actual.</div>
            </div>

            <div class="mb-3">
                <label for="descripcion" class="form-label">Descripción 
                    <small class="text-muted">(opcional)</small>
                </label>
                <textarea
                    id="descripcion"
                    name="descripcion"
                    class="form-control"
                    rows="3"
                ><?php echo htmlspecialchars($descripcion); ?></textarea>
            </div>

            <button type="submit" class="btn btn-success">Actualizar</button>
            <a href="index.php" class="btn btn-secondary ms-2">Cancelar</a>
        </form>
    </div>
</div>

<script src="/PROYECTO2DS6/js/script.js"></script>
<?php require '../includes/footer.php'; ?>
