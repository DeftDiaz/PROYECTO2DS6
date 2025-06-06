<?php
// C:\xampp\htdocs\PROYECTO2DS6\productos\create.php

require '../config/db.php';
require '../includes/header.php';

// Asegurarse de que solo Admin (rol 01) pueda acceder a esta página
if ($_SESSION['usuario']['rol'] !== '01') {
    header('Location: index.php');
    exit;
}

$errors = [];
$nombre      = '';
$descripcion = '';
$precio      = '';
$categoria_id = 0;    // inicializar como entero
$rutaImagen  = '';

// 1) Obtener lista de categorías para el <select>
$sqlCats = "SELECT id, nombre FROM categorias ORDER BY nombre";
$resCats = $mysqli->query($sqlCats);
if (!$resCats) {
    die("Error al obtener categorías: " . $mysqli->error);
}
$cats = [];
while ($fila = $resCats->fetch_assoc()) {
    $cats[] = $fila; // cada $fila['id'], $fila['nombre']
}
$resCats->free();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // 2) Validar nombre
    $nombre = trim($_POST['nombre'] ?? '');
    if ($nombre === '') {
        $errors[] = 'El nombre es obligatorio.';
    }

    // 3) Validar precio
    $precioRaw = $_POST['precio'] ?? '';
    if ($precioRaw === '' || !is_numeric($precioRaw) || (float)$precioRaw < 0) {
        $errors[] = 'El precio debe ser un número válido.';
    } else {
        $precio = (float) $precioRaw;
    }

    // 4) Descripción opcional
    $descripcion = trim($_POST['descripcion'] ?? '');

    // 5) Validar categoría seleccionada
    $categoria_id = (int)($_POST['categoria_id'] ?? 0);
    $validCatIds  = array_map('intval', array_column($cats, 'id'));
    if (!in_array($categoria_id, $validCatIds, true)) {
        $errors[] = 'Categoría no válida.';
    }

    // 6) Manejar archivo si se sube
    if (isset($_FILES['imagen']) && $_FILES['imagen']['error'] !== UPLOAD_ERR_NO_FILE) {
        if ($_FILES['imagen']['error'] !== UPLOAD_ERR_OK) {
            $errors[] = 'Error al subir la imagen.';
        } else {
            if ($_FILES['imagen']['size'] > 2 * 1024 * 1024) {
                $errors[] = 'La imagen supera 2 MB.';
            }
            $allowedExt = ['jpg', 'jpeg', 'png', 'gif'];
            $origName   = $_FILES['imagen']['name'];
            $ext        = strtolower(pathinfo($origName, PATHINFO_EXTENSION));

            if (!in_array($ext, $allowedExt, true)) {
                $errors[] = 'Formato de imagen no válido. Solo JPG, PNG o GIF.';
            } else {
                // Generar nombre único
                $nuevoNombre = uniqid('prod_') . '.' . $ext;
                $destino     = __DIR__ . '/../img/productos/' . $nuevoNombre;

                if (move_uploaded_file($_FILES['imagen']['tmp_name'], $destino)) {
                    $rutaImagen = '/PROYECTO2DS6/img/productos/' . $nuevoNombre;
                } else {
                    $errors[] = 'No se pudo guardar la imagen en el servidor.';
                }
            }
        }
    }

    // 7) Si no hay errores, INSERT en BD
    if (empty($errors)) {
        $sql = "INSERT INTO productos 
                    (nombre, descripcion, precio, imagen, categoria_id, fecha_creacion)
                VALUES (?, ?, ?, ?, ?, NOW())";
        $stmt = $mysqli->prepare($sql);
        if (!$stmt) {
            die("Error al preparar INSERT de producto: " . $mysqli->error);
        }
        $stmt->bind_param('ssdsi', $nombre, $descripcion, $precio, $rutaImagen, $categoria_id);
        if ($stmt->execute()) {
            header('Location: index.php');
            exit;
        } else {
            $errors[] = "Error al guardar en BD: " . $stmt->error;
        }
        $stmt->close();
    }
}
?>

<div class="row justify-content-center">
    <div class="col-lg-8">
        <h2>Nuevo Producto</h2>

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
                    <option value="" disabled <?php if ($categoria_id === 0) echo 'selected'; ?>>
                        -- Selecciona una categoría --
                    </option>
                    <?php foreach ($cats as $c): ?>
                        <option
                            value="<?php echo (int)$c['id']; ?>"
                            <?php if ($categoria_id === (int)$c['id']) echo 'selected'; ?>
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

            <div class="mb-3">
                <label for="imagen" class="form-label">Subir Imagen <small class="text-muted">(opcional)</small></label>
                <input
                    type="file"
                    id="imagen"
                    name="imagen"
                    class="form-control"
                    accept="image/*"
                >
                <div class="form-text">Máximo 2 MB. Formatos: JPG, PNG, GIF.</div>
            </div>

            <div class="mb-3">
                <label for="descripcion" class="form-label">Descripción <small class="text-muted">(opcional)</small></label>
                <textarea
                    id="descripcion"
                    name="descripcion"
                    class="form-control"
                    rows="3"
                ><?php echo htmlspecialchars($descripcion); ?></textarea>
            </div>

            <button type="submit" class="btn btn-success">Guardar</button>
            <a href="index.php" class="btn btn-secondary ms-2">Cancelar</a>
        </form>
    </div>
</div>

<?php require '../includes/footer.php'; ?>
