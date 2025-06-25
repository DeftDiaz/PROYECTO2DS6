<?php
// C:\xampp\htdocs\PROYECTO2DS6\catalogo\header_catalogo.php
session_start();

// Si no hay usuario logueado, ir al login
if (!isset($_SESSION['usuario'])) {
    header('Location: /PROYECTO2DS6/auth/login.php');
    exit;
}

// Si es Admin (rol 01), enviarlo al área administrativa
if ($_SESSION['usuario']['rol'] === '01') {
    header('Location: /PROYECTO2DS6/categorias/index.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" href="/PROYECTO2DS6/css/admin.css">
    <title>Catálogo</title>
</head>
<body>
    <!-- Navbar -->
    <nav class="navbar">
        <div class="navbar-container">
            <a href="/PROYECTO2DS6/catalogo/index.php" class="navbar-brand">Catálogo</a>
            <ul class="navbar-nav">
                <li class="nav-item">
                    <a href="/PROYECTO2DS6/auth/logout.php" class="nav-link logout">Cerrar Sesión</a>
                </li>
            </ul>
        </div>
    </nav>

    <!-- Main Container -->
    <main class="main-container">

