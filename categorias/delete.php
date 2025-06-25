<?php
// C:\xampp\htdocs\PROYECTO2DS6\categorias\delete.php

require '../config/db.php';
session_start();

// Solo Admin
if (!isset($_SESSION['usuario']) || $_SESSION['usuario']['rol'] !== '01') {
    header('Location: ../catalogo/index.php');
    exit;
}

// Validar ID
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    die("ID de categoría no válido.");
}
$id = (int) $_GET['id'];

// Ejecutar DELETE
$sql = "DELETE FROM categorias WHERE id = ?";
$stmt = $mysqli->prepare($sql);
if (!$stmt) {
    die("Error al preparar DELETE de categoría: " . $mysqli->error);
}
$stmt->bind_param('i', $id);
if ($stmt->execute()) {
    // Redirigir de vuelta al listado
    header('Location: index.php');
    exit;
} else {
    die("Error al eliminar categoría: " . $stmt->error);
}
?>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" href="/PROYECTO2DS6/css/admin.css">
    <title>Eliminar Categoría</title>
</head>
<script src="/PROYECTO2DS6/js/script.js"></script>
</body>
</html>