<?php
// C:\xampp\htdocs\PROYECTO2DS6\auth\login.php

require '../config/db.php'; // carga $mysqli
session_start();

// 1) Si ya está logueado, redirige a categorías
if (isset($_SESSION['usuario'])) {
    header('Location: /PROYECTO2DS6/categorias/index.php');
    exit;
}

$errors = [];
$cedula = '';

// 2) Procesar formulario POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $cedula = trim($_POST['cedula'] ?? '');
    $password = trim($_POST['password'] ?? '');

    // 3) Validar campos no vacíos
    if ($cedula === '' || $password === '') {
        $errors[] = 'Cédula y contraseña son obligatorios.';
    } else {
        // 4) Buscar usuario en BD
        $sql = "SELECT cedula, password, rol, activo 
                FROM usuarios 
                WHERE cedula = ? LIMIT 1";
        $stmt = $mysqli->prepare($sql);
        $stmt->bind_param('s', $cedula);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows === 1) {
            $user = $result->fetch_assoc();

            // 5) Verificar que esté activo (activo = 1)
            if ((int)$user['activo'] === 1) {
                // 6) Comparar contraseña en texto plano (así está en tu volcado)
                if ($password === $user['password']) {
                    // Credenciales válidas
                    $_SESSION['usuario'] = [
                        'cedula' => $user['cedula'],
                        'rol'    => $user['rol']
                    ];

                    //redirigir segun el rol
                    if ($user['rol'] === '01') {
                        //admin 
                        header('Location: /PROYECTO2DS6/categorias/index.php');
                        exit;
                    } else {
                        //consulta
                        header('Location: /PROYECTO2DS6/catalogo/index.php');
                        exit;
                    }
                } else {
                    $errors[] = 'Contraseña incorrecta.';
                }
            } else {
                $errors[] = 'Usuario no está activo.';
            }
        } else {
            $errors[] = 'Cédula no registrada.';
        }

        $stmt->close();
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <title>Iniciar Sesión</title>
</head>
<body class="bg-light">
    <div class="d-flex justify-content-center align-items-center vh-100">
        <div class="card shadow-sm" style="width: 360px;">
            <div class="card-body">
                <h3 class="card-title mb-4 text-center">Iniciar Sesión</h3>

                <!-- Mostrar errores si los hay -->
                <?php if (!empty($errors)): ?>
                    <div class="alert alert-danger">
                        <ul class="mb-0">
                            <?php foreach ($errors as $e): ?>
                                <li><?php echo htmlspecialchars($e); ?></li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                <?php endif; ?>

                <!-- Formulario de login -->
                <form method="post" novalidate>
                    <div class="mb-3">
                        <label for="cedula" class="form-label">Cédula</label>
                        <input
                            type="text"
                            id="cedula"
                            name="cedula"
                            class="form-control"
                            value="<?php echo htmlspecialchars($cedula); ?>"
                            required
                        >
                    </div>

                    <div class="mb-3">
                        <label for="password" class="form-label">Contraseña</label>
                        <input
                            type="password"
                            id="password"
                            name="password"
                            class="form-control"
                            required
                        >
                    </div>

                    <button type="submit" class="btn btn-primary w-100">Ingresar</button>
                </form>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script
        src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"
        integrity="sha384-in8JYL8i7Av/XFX6c4hMVK9OjnLTWO+6x2SXXzFQHILnzo6uTC+jWWMZ5Wdl9OMR"
        crossorigin="anonymous"
    ></script>
</body>
</html>
