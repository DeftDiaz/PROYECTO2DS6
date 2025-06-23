<?php
header('Access-Control-Allow-Origin: *');
header('Content-Type: application/json');
require_once __DIR__ . '/../config/db.php';

$sql = "SELECT id, nombre, imagen FROM categorias";
$result = $mysqli->query($sql);

if (!$result) {
    echo json_encode([
        'error' => true,
        'message' => 'Error en la consulta SQL.'
    ]);
    exit;
}

$categorias = [];
while ($row = $result->fetch_assoc()) {
    $categorias[] = $row;
}

echo json_encode($categorias);
?>
