<?php
header('Access-Control-Allow-Origin: *');
header('Content-Type: application/json');
require_once __DIR__ . '/../config/db.php';

$categoria_id = isset($_GET['categoria_id']) ? intval($_GET['categoria_id']) : 0;
$base_url = 'http://10.0.2.2/PROYECTO2DS6/img/productos/';

if ($categoria_id > 0) {
    $sql = "SELECT id, nombre, descripcion, precio, imagen FROM productos WHERE categoria_id = $categoria_id";
} else {
    $sql = "SELECT id, nombre, descripcion, precio, imagen FROM productos";
}

$result = $mysqli->query($sql);

if (!$result) {
    echo json_encode([
        'error' => true,
        'message' => 'Error en la consulta SQL.'
    ]);
    exit;
}

$productos = [];
while ($row = $result->fetch_assoc()) {
    if (!empty($row['imagen'])) {
        // Si la imagen ya es una URL absoluta, no concatenar
        if (filter_var($row['imagen'], FILTER_VALIDATE_URL)) {
            $row['imagen'] = $row['imagen'];
        } else {
            // Si la imagen contiene la subruta, extraer solo el nombre del archivo
            $filename = basename($row['imagen']);
            $row['imagen'] = $base_url . $filename;
        }
    }
    $productos[] = $row;
}

echo json_encode($productos);
?>
