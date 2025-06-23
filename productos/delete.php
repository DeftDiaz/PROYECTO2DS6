<?php
// C:\xampp\htdocs\PROYECTO2DS6\productos\delete.php

require '../config/db.php';
session_start();

// Verificar ID por GET
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    die("ID de producto no válido.");
}
$id = (int) $_GET['id'];

// 1) Obtener la ruta de la imagen para borrarla del disco (opcional)
$sqlSel = "SELECT imagen FROM productos WHERE id = ?";
$stmtSel = $mysqli->prepare($sqlSel);
$stmtSel->bind_param('i', $id);
$stmtSel->execute();
$res = $stmtSel->get_result();
if ($res->num_rows === 1) {
    $prod = $res->fetch_assoc();
    $rutaAntigua = $prod['imagen']; // p.ej. "/PROYECTO2DS6/img/productos/foo.jpg"
    if ($rutaAntigua && file_exists(__DIR__ . '/..' . $rutaAntigua)) {
        @unlink(__DIR__ . '/..' . $rutaAntigua);
    }
}
$stmtSel->close();

// 2) Eliminar de la BD
$sql = "DELETE FROM productos WHERE id = ?";
$stmt = $mysqli->prepare($sql);
if (!$stmt) {
    die("Error al preparar DELETE de producto: " . $mysqli->error);
}
$stmt->bind_param('i', $id);
if ($stmt->execute()) {
    header('Location: index.php');
    exit;
} else {
    die("Error al eliminar producto: " . $stmt->error);
}
?>
<script src="/PROYECTO2DS6/js/script.js"></script>
</body>
</html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" href="/PROYECTO2DS6/css/admin.css">
    <title>Eliminar Producto</title>
</head>
