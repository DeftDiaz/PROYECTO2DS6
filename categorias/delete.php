<?php
// C:\xampp\htdocs\PROYECTO2DS6\categorias\delete.php

require '../config/db.php';
require '../includes/header.php';

// 1) Verificar que venga ID válido
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    die("ID de categoría no válido.");
}
$id = (int)$_GET['id'];

// 2) Ejecutar DELETE
$sql = "DELETE FROM categorias WHERE id = ?";
$stmt = $mysqli->prepare($sql);
if (!$stmt) {
    die("Error al preparar DELETE de categoría: " . $mysqli->error);
}
$stmt->bind_param('i', $id);
if ($stmt->execute()) {
    header('Location: index.php');
    exit;
} else {
    die("Error al eliminar categoría: " . $stmt->error);
}
