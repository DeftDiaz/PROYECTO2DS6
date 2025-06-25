<?php
// C:\xampp\htdocs\PROYECTO2DS6\includes\header.php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Si no hay usuario en sesión, redirigimos a login
if (!isset($_SESSION['usuario'])) {
    header('Location: /PROYECTO2DS6/auth/login.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    
    <title>Panel Administrativo</title>
    
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: #f4f6f8;
            color: #222;
        }
        /* Navbar Styles - Simple, flat, minimal */
        .navbar {
            background: #fff;
            border-bottom: 1px solid #e0e0e0;
            position: sticky;
            top: 0;
            z-index: 1000;
        }
        .navbar-container {
            max-width: 900px;
            margin: 0 auto;
            padding: 0 16px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            min-height: 56px;
        }
        .navbar-brand {
            color: #222;
            text-decoration: none;
            font-size: 1.3rem;
            font-weight: 600;
            letter-spacing: 0.5px;
        }
        .menu-toggle {
            display: none;
            flex-direction: column;
            cursor: pointer;
            padding: 6px;
            border-radius: 4px;
            background: transparent;
            border: none;
        }
        .hamburger-line {
            width: 22px;
            height: 2.5px;
            background: #222;
            margin: 3px 0;
            border-radius: 1.5px;
        }
        .navbar-nav {
            display: flex;
            list-style: none;
            align-items: center;
            gap: 1.2rem;
        }
        .navbar-nav.main-nav {
            margin-right: auto;
            margin-left: 2rem;
        }
        .nav-item {
            position: relative;
        }
        .nav-link {
            color: #222;
            text-decoration: none;
            padding: 8px 14px;
            border-radius: 4px;
            font-weight: 500;
            transition: background 0.2s;
        }
        .nav-link:hover, .nav-link.active {
            background: #e0e0e0;
            color: #111;
        }
        .nav-link.logout {
            background: #e57373;
            color: #fff;
            font-weight: 600;
            border: none;
        }
        .nav-link.logout:hover {
            background: #c62828;
        }
        /* Main Container */
        .main-container {
            max-width: 900px;
            margin: 1.5rem auto 0 auto;
            padding: 0 16px;
        }
        /* Welcome Message */
        .welcome-message {
            background: #fff;
            border-radius: 6px;
            padding: 1rem 1.2rem;
            margin-bottom: 1.2rem;
            border-left: 3px solid #1976d2;
            box-shadow: none;
        }
        .welcome-message h2 {
            color: #1976d2;
            margin-bottom: 0.3rem;
            font-size: 1.1rem;
        }
        .welcome-message p {
            color: #555;
            margin: 0;
            font-size: 0.97rem;
        }
        /* Responsive Design */
        @media (max-width: 768px) {
            .menu-toggle {
                display: flex;
            }
            .navbar-nav {
                position: absolute;
                top: 100%;
                left: 0;
                right: 0;
                background: #fff;
                flex-direction: column;
                padding: 0.5rem 0;
                box-shadow: 0 2px 8px rgba(0,0,0,0.04);
                transform: translateY(-100%);
                opacity: 0;
                visibility: hidden;
                transition: all 0.2s;
                gap: 0;
            }
            .navbar-nav.main-nav {
                margin: 0;
                position: static;
                background: none;
                box-shadow: none;
                transform: none;
                opacity: 1;
                visibility: visible;
                padding: 0;
            }
            .navbar-nav.show {
                transform: translateY(0);
                opacity: 1;
                visibility: visible;
            }
            .nav-item {
                width: 100%;
                text-align: center;
            }
            .nav-link {
                display: block;
                padding: 12px 0;
                border-radius: 0;
                border-bottom: 1px solid #eee;
            }
            .nav-link:last-child {
                border-bottom: none;
            }
            .navbar-container {
                flex-wrap: wrap;
                position: relative;
            }
            .main-container {
                margin: 1rem auto 0 auto;
                padding: 0 8px;
            }
        }
        @media (max-width: 480px) {
            .navbar-brand {
                font-size: 1rem;
            }
            .navbar-container {
                padding: 0 6px;
                min-height: 48px;
            }
        }
    </style>
</head>
<body>
    <!-- Navbar -->
    <nav class="navbar">
        <div class="navbar-container">
            <a href="/PROYECTO2DS6/" class="navbar-brand">Gestión de Inventario</a>
            
            <button class="menu-toggle" id="menuToggle" onclick="toggleMenu()">
                <div class="hamburger-line"></div>
                <div class="hamburger-line"></div>
                <div class="hamburger-line"></div>
            </button>

            <ul class="navbar-nav main-nav">
                <li class="nav-item">
                    <a href="/PROYECTO2DS6/categorias/index.php" class="nav-link">Categorías</a>
                </li>
                <li class="nav-item">
                    <a href="/PROYECTO2DS6/productos/index.php" class="nav-link">Productos</a>
                </li>
            </ul>

            <ul class="navbar-nav" id="mobileMenu">
                <li class="nav-item">
                    <a href="/PROYECTO2DS6/auth/logout.php" class="nav-link logout">Cerrar Sesión</a>
                </li>
            </ul>
        </div>
    </nav>

    <!-- Welcome Message -->
    <div class="main-container">

        <!-- Contenedor principal para el contenido de las páginas -->
        <main>

<script>
function toggleMenu() {
    const menuToggle = document.getElementById('menuToggle');
    const mobileMenu = document.getElementById('mobileMenu');
    
    menuToggle.classList.toggle('active');
    mobileMenu.classList.toggle('show');
}

// Cerrar menú al hacer clic fuera
document.addEventListener('click', function(event) {
    const navbar = document.querySelector('.navbar');
    const menuToggle = document.getElementById('menuToggle');
    const mobileMenu = document.getElementById('mobileMenu');
    
    if (!navbar.contains(event.target)) {
        menuToggle.classList.remove('active');
        mobileMenu.classList.remove('show');
    }
});

// Marcar enlace activo basado en la URL current
document.addEventListener('DOMContentLoaded', function() {
    const currentPath = window.location.pathname;
    const navLinks = document.querySelectorAll('.nav-link');
    
    navLinks.forEach(link => {
        if (link.getAttribute('href') && currentPath.includes(link.getAttribute('href'))) {
            link.classList.add('active');
        }
    });
});
</script>
