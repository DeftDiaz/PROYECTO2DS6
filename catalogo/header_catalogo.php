<?php
// C:\xampp\htdocs\PROYECTO2DS6\catalogo\header_catalogo.php
session_start();

// Si no hay usuario logueado, redirigir a login
if (!isset($_SESSION['usuario'])) {
    header('Location: /PROYECTO2DS6/auth/login.php');
    exit;
}

// Si es rol 01 (Admin) y por alguna razón aterriza aquí, forzamos a área admin
if ($_SESSION['usuario']['rol'] === '01') {
    header('Location: /PROYECTO2DS6/categorias/index.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />

    <!-- Bootstrap CSS -->
    <link
        href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet"
    />

    <!-- Tus estilos personalizados -->
    <link rel="stylesheet" href="/PROYECTO2DS6/css/estilos.css" />

    <title>Catálogo</title>
</head>
<body class="bg-light">

    <!-- Navbar mínimo para catálogo -->
    <nav class="navbar navbar-expand-lg navbar-light bg-white border-bottom mb-4">
        <div class="container">
            <a class="navbar-brand" href="/PROYECTO2DS6/catalogo/index.php">Catálogo</a>
            <button
                class="navbar-toggler"
                type="button"
                data-bs-toggle="collapse"
                data-bs-target="#navbarCatalogo"
                aria-controls="navbarCatalogo"
                aria-expanded="false"
                aria-label="Mostrar menú"
            >
                <span class="navbar-toggler-icon"></span>
            </button>

            <div class="collapse navbar-collapse" id="navbarCatalogo">
                <ul class="navbar-nav ms-auto mb-2 mb-lg-0">
                    <!-- Opción “Cerrar Sesión” -->
                    <li class="nav-item">
                        <a class="nav-link" href="/PROYECTO2DS6/auth/logout.php">Cerrar Sesión</a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <main class="container">
