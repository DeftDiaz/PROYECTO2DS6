<?php
// C:\xampp\htdocs\PROYECTO2DS6\categorias\edit.php

require '../config/db.php';
require '../includes/header.php';

$errors = [];
$nombre = '';
$rutaImagen = ''; // guardaremos la ruta existente o la nueva
$id = 0;

// 1) Verificar que venga el ID por GET
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    die("ID de categoría no válido.");
}
$id = (int) $_GET['id'];

// 2) Si no es POST, cargamos datos de BD para precargar
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    $sql = "SELECT * FROM categorias WHERE id = ?";
    $stmt = $mysqli->prepare($sql);
    $stmt->bind_param('i', $id);
    $stmt->execute();
    $res = $stmt->get_result();
    if ($res->num_rows !== 1) {
        die("Categoría no encontrada.");
    }
    $cat = $res->fetch_assoc();
    $nombre = $cat['nombre'];
    $rutaImagen = $cat['imagen']; // ruta existente (puede ser cadena vacía)
    $stmt->close();
} else {
    // 3) Si es POST, procesamos la actualización
    $nombre = trim($_POST['nombre'] ?? '');
    // Tomamos la ruta que estaba guardada, para usarla si no suben nueva imagen
    $rutaExistente = trim($_POST['ruta_existente'] ?? '');

    if ($nombre === '') {
        $errors[] = 'El nombre es obligatorio.';
    }

    // 4) Manejar archivo nuevo
    $nuevaRuta = $rutaExistente; // si no suben nada, seguimos con la ruta antigua

    if (isset($_FILES['imagen']) && $_FILES['imagen']['error'] !== UPLOAD_ERR_NO_FILE) {
        // Subieron un archivo: validamos y movemos
        if ($_FILES['imagen']['error'] !== UPLOAD_ERR_OK) {
            $errors[] = 'Error al subir la nueva imagen.';
        } else {
            if ($_FILES['imagen']['size'] > 2 * 1024 * 1024) {
                $errors[] = 'La nueva imagen supera 2 MB.';
            }
            $allowedExt = ['jpg','jpeg','png','gif'];
            $tmpName = $_FILES['imagen']['tmp_name'];
            $origName = basename($_FILES['imagen']['name']);
            $ext = strtolower(pathinfo($origName, PATHINFO_EXTENSION));

            if (!in_array($ext, $allowedExt, true)) {
                $errors[] = 'Formato de imagen no válido. Solo JPG, PNG o GIF.';
            } else {
                // Generamos nombre único
                $nuevoNombre = uniqid('cat_') . '.' . $ext;
                $destino = __DIR__ . '/../img/categorias/' . $nuevoNombre;

                if (move_uploaded_file($tmpName, $destino)) {
                    $nuevaRuta = '/PROYECTO2DS6/img/categorias/' . $nuevoNombre;
                    // Opcional: borrar la imagen anterior para no acumular
                    if ($rutaExistente && file_exists(__DIR__ . '/..' . $rutaExistente)) {
                        @unlink(__DIR__ . '/..' . $rutaExistente);
                    }
                } else {
                    $errors[] = 'No se pudo guardar la nueva imagen en el servidor.';
                }
            }
        }
    }

    // 5) Si no hay errores, actualizamos en BD
    if (empty($errors)) {
        $sql = "UPDATE categorias 
                SET nombre = ?, imagen = ?, fecha_actualizacion = NOW() 
                WHERE id = ?";
        $stmt = $mysqli->prepare($sql);
        if (!$stmt) {
            die("Error al preparar UPDATE de categoría: " . $mysqli->error);
        }
        $stmt->bind_param('ssi', $nombre, $nuevaRuta, $id);
        if ($stmt->execute()) {
            header('Location: index.php');
            exit;
        } else {
            $errors[] = "Error al actualizar en BD: " . $stmt->error;
        }
        $stmt->close();
    }

    // En caso de error, dejamos $rutaImagen en la ruta antigua o en $nuevaRuta si se movió
    $rutaImagen = $nuevaRuta;
}
?>

<div class="row justify-content-center">
    <div class="col-md-6">
        <h2>Editar Categoría</h2>

        <?php if (!empty($errors)): ?>
            <div class="alert alert-danger">
                <ul class="mb-0">
                    <?php foreach ($errors as $e): ?>
                        <li><?php echo htmlspecialchars($e); ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>

        <!-- Formulario de edición -->
        <form method="post" enctype="multipart/form-data" novalidate>
            <!-- Campo oculto para llevar la ruta actual de la imagen -->
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

            <!-- Mostrar la imagen actual, si existe -->
            <?php if ($rutaImagen): ?>
                <div class="mb-3">
                    <label class="form-label">Imagen actual</label><br>
                    <img
                        src="<?php echo htmlspecialchars($rutaImagen); ?>"
                        alt="Imagen categoría"
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

            <button type="submit" class="btn btn-success">Actualizar</button>
            <a href="index.php" class="btn btn-secondary ms-2">Cancelar</a>
        </form>
    </div>
</div>

<script src="/PROYECTO2DS6/js/script.js"></script>
<?php require '../includes/footer.php'; ?>
