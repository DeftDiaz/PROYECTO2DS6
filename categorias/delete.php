<?php
// C:\xampp\htdocs\PROYECTO2DS6\categorias\delete.php

require '../config/db.php';
require '../includes/header.php';

// 1) Solo Admin
if ($_SESSION['usuario']['rol'] !== '01') {
    header('Location: ../catalogo/index.php');
    exit;
}

// 2) Validar ID
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    die("ID de categoría no válido.");
}
$id = (int) $_GET['id'];

// 3) Ejecutar DELETE
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