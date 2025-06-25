<?php
// C:\xampp\htdocs\PROYECTO2DS6\auth\login.php

require '../config/db.php'; // carga $mysqli
session_start();

// Si ya está logueado, redirige a categorías
if (isset($_SESSION['usuario'])) {
    header('Location: /PROYECTO2DS6/categorias/index.php');
    exit;
}

$usuario = '';

// Procesar formulario POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $usuario = trim($_POST['usuario'] ?? '');
    $password = trim($_POST['password'] ?? '');

    // Validar campos no vacíos
    if ($usuario !== '' && $password !== '') {
        // Buscar usuario en BD
        $sql = "SELECT usuario, password, rol, activo 
                FROM usuarios 
                WHERE usuario = ? LIMIT 1";
        $stmt = $mysqli->prepare($sql);
        $stmt->bind_param('s', $usuario);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows === 1) {
            $user = $result->fetch_assoc();

            // Verificar que esté activo (activo = 1)
            if ((int)$user['activo'] === 1) {
                // Comparar contraseña en texto plano
                if ($password === $user['password']) {
                    // Credenciales válidas
                    $_SESSION['usuario'] = [
                        'usuario' => $user['usuario'],
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
                    // Contraseña incorrecta
                    header('Location: login.php?error=contrasena&usuario=' . urlencode($usuario));
                    exit;
                }
            } else {
                // Usuario no activo: ahora se trata igual que usuario no registrado
                header('Location: login.php?error=usuario&usuario=' . urlencode($usuario));
                exit;
            }
        } else {
            // Usuario no registrado
            header('Location: login.php?error=usuario&usuario=' . urlencode($usuario));
            exit;
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
    <link rel="stylesheet" href="/PROYECTO2DS6/css/login.css">
    <title>Iniciar Sesión</title>
</head>
<body class="login-bg">
    <div class="login-center">
        <div class="login-card">
            <h3 class="login-title">Iniciar Sesión</h3>

            <!-- Formulario de login -->
            <form method="post" class="login-form" novalidate>
                <div class="form-group">
                    <label for="usuario">Usuario</label>
                    <input
                        type="text"
                        id="usuario"
                        name="usuario"
                        value="<?php echo htmlspecialchars($_GET['usuario'] ?? $usuario); ?>"
                        required 
                        autocomplete="off"
                    >
                </div>
                
                <div class="form-group">
                    <label for="password">Contraseña</label>
                    <input
                        type="password"
                        id="password"
                        name="password"
                        required
                    >
                </div>
                
                <button type="submit">Ingresar</button>
            </form>
        </div>
    </div>
    <script src="/PROYECTO2DS6/js/script.js"></script>
</body>
</html>
