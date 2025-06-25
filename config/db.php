<?php
// /proyecto-web/config/db.php

// Datos de conexión
$host = 'localhost';        
$db   = 'proyecto2ds6';    
$user = 'd62025';            
$pass = '1234';                

// Intentamos la conexión
$mysqli = new mysqli($host, $user, $pass, $db);

//Verificamos errores de conexión
if ($mysqli->connect_errno) {
    // Si algo falla detenemos la ejecución y mostramos texto.
    die("Error de conexión MySQLi (código {$mysqli->connect_errno}): {$mysqli->connect_error}");
}

$mysqli->set_charset("utf8mb4");
