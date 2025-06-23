<?php
// C:\xampp\htdocs\PROYECTO2DS6\categorias\create.php
require '../includes/header.php';
require '../config/db.php';

$errors = [];
$nombre = '';

// Aquí guardaremos la ruta relativa de la imagen en la BD
$rutaImagen = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // 1) Validar nombre
    if (empty(trim($_POST['nombre']))) {
        $errors[] = 'El nombre es obligatorio.';
    } else {
        $nombre = trim($_POST['nombre']);
    }

    // 2) Manejar el archivo si se envió
    if (isset($_FILES['imagen']) && $_FILES['imagen']['error'] !== UPLOAD_ERR_NO_FILE) {
        // Si hay error distinto de “no file”, validamos
        if ($_FILES['imagen']['error'] !== UPLOAD_ERR_OK) {
            $errors[] = 'Error al subir la imagen.';
        } else {
            // Validar tamaño (por ejemplo, <= 2MB)
            if ($_FILES['imagen']['size'] > 2 * 1024 * 1024) {
                $errors[] = 'La imagen supera el tamaño máximo de 2 MB.';
            }

            // Validar extensión
            $allowedExt = ['jpg','jpeg','png','gif'];
            $tmpName = $_FILES['imagen']['tmp_name'];
            $origName = basename($_FILES['imagen']['name']);
            $ext = strtolower(pathinfo($origName, PATHINFO_EXTENSION));

            if (!in_array($ext, $allowedExt, true)) {
                $errors[] = 'Formato de imagen no válido. Solo JPG, PNG o GIF.';
            } else {
                // Generar nombre único para evitar colisiones
                $nuevoNombre = uniqid('cat_') . '.' . $ext;
                $destino = __DIR__ . '/../img/categorias/' . $nuevoNombre;

                if (move_uploaded_file($tmpName, $destino)) {
                    // Ruta que guardaremos en la BD (URL relativa)
                    $rutaImagen = '/PROYECTO2DS6/img/categorias/' . $nuevoNombre;
                } else {
                    $errors[] = 'No se pudo guardar la imagen en el servidor.';
                }
            }
        }
    }

    // 3) Si no hay errores, INSERT en BD
    if (empty($errors)) {
        $sql = "INSERT INTO categorias (nombre, imagen, fecha_creacion)
                VALUES (?, ?, NOW())";
        $stmt = $mysqli->prepare($sql);
        if (!$stmt) {
            die("Error al preparar INSERT de categoría: " . $mysqli->error);
        }
        $stmt->bind_param('ss', $nombre, $rutaImagen);
        if ($stmt->execute()) {
            header('Location: index.php');
            exit;
        } else {
            $errors[] = "Error al guardar en BD: " . $stmt->error;
        }
        $stmt->close();
    }
}

require '../includes/header.php';
?>

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" href="/PROYECTO2DS6/css/admin.css">
    <title>Nueva Categoría</title>
</head>

<div class="row justify-content-center">
    <div class="col-md-6">
        <h2>Nueva Categoría</h2>

        <?php if (!empty($errors)): ?>
            <div class="alert alert-danger">
                <ul class="mb-0">
                    <?php foreach ($errors as $e): ?>
                        <li><?php echo htmlspecialchars($e); ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>

        <!-- Atributo enctype="multipart/form-data" es para subir archivos -->
        <form method="post" enctype="multipart/form-data" novalidate>
            <div class="mb-3">
                <label for="nombre" class="form-label">Nombre</label>
                <input
                    type="text"
                    id="nombre"
                    name="nombre"
                    class="form-control"
                    value="<?php echo htmlspecialchars($nombre); ?>"
                    required autocomplete="off"
                >
            </div>

            <div class="mb-3">
                <label for="imagen" class="form-label">Subir Imagen 
                </label>
                <input
                    type="file"
                    id="imagen"
                    name="imagen"
                    class="form-control"
                    accept="image/*"
                >
                <div class="form-text">Máximo 2 MB. Formatos: JPG, PNG, GIF.</div>
            </div>

            <button type="submit" class="btn btn-success">Guardar</button>
            <a href="index.php" class="btn btn-secondary ms-2">Cancelar</a>
        </form>
    </div>
</div>

<script src="/PROYECTO2DS6/js/script.js"></script>
<?php require '../includes/footer.php'; ?>
