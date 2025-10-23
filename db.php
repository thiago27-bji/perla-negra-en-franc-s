<?php
$host = 'localhost';
$dbnome = 'salon_de_belleza';
$user =  'root';
$pass = 'Avila2001';
try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbnome; charset=utf8", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("No se pudo conectar a la base de datos: " . $e->getMessage());
} 