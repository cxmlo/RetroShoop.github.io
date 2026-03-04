<?php
$host = "maglev.proxy.rlwy.net";
$usuario = "root";
$password = "CozNdFsyyzdJLykxStvonkYOpnqqnfMv";
$base_datos = "railway";
$puerto = "43717";

// Conexión PDO
try {
    $pdo = new PDO(
        "mysql:host=$host;port=$puerto;dbname=$base_datos;charset=utf8mb4", 
        $usuario, 
        $password,
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_EMULATE_PREPARES => false,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
        ]
    );
} catch(PDOException $e) {
    die("Error PDO: " . $e->getMessage());
}

// MySQLi
$conexion = mysqli_connect($host, $usuario, $password, $base_datos, $puerto);

if(!$conexion) {
    die("Error mysqli: " . mysqli_connect_error());
}

mysqli_set_charset($conexion, "utf8mb4");
?>
