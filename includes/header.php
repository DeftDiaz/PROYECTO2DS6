<?php
// C:\xampp\htdocs\PROYECTO2DS6\includes\header.php
session_start();

// 1) Si no hay usuario en sesión, redirigimos a login
if (!isset($_SESSION['usuario'])) {
    header('Location: /PROYECTO2DS6/auth/login.php');
    exit;
}

// A partir de aquí, $_SESSION['usuario'] contiene:
//   ['cedula' => '...', 'rol' => '01' ó '02']
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- Bootstrap 5 CSS (CDN) -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- Tus estilos personalizados -->
    <link rel="stylesheet" href="/PROYECTO2DS6/css/estilos.css">

    <title>Panel Administrativo</title>
</head>
<body class="bg-light">

    <!-- Navbar con Bootstrap -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark mb-4">
        <div class="container">
            <a class="navbar-brand" href="/PROYECTO2DS6/">Mi Proyecto</a>
            <button
                class="navbar-toggler"
                type="button"
                data-bs-toggle="collapse"
                data-bs-target="#navbarContenido"
                aria-controls="navbarContenido"
                aria-expanded="false"
                aria-label="Mostrar menú"
            >
                <span class="navbar-toggler-icon"></span>
            </button>

            <div class="collapse navbar-collapse" id="navbarContenido">
                <ul class="navbar-nav me-auto mb-2 mb-lg-0">
                    <!-- Link a Categorías -->
                    <li class="nav-item">
                        <a class="nav-link" href="/PROYECTO2DS6/categorias/index.php">Categorías</a>
                    </li>
                    <!-- Link a Productos -->
                    <li class="nav-item">
                        <a class="nav-link" href="/PROYECTO2DS6/productos/index.php">Productos</a>
                    </li>
                </ul>
                <ul class="navbar-nav ms-auto mb-2 mb-lg-0">
                    <li class="nav-item">
                        <a class="nav-link" href="/PROYECTO2DS6/auth/logout.php">Cerrar Sesión</a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Contenedor principal -->
    <main class="container">
