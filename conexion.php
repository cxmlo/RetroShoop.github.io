<?php
$host = "sql105.infinityfree.com";
$usuario = "if0_40086036";
$password = "5HSZoFtZkoS";
$base_datos = "if0_40086036_ropa";

// Conexión PDO (Segura - nueva)
try {
    $pdo = new PDO(
        "mysql:host=$host;dbname=$base_datos;charset=utf8mb4", 
        $usuario, 
        $password,
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_EMULATE_PREPARES => false,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
        ]
    );
} catch(PDOException $e) {
    error_log("Error PDO: " . $e->getMessage());
    die("Error de conexión");
}

// MySQLi (para compatibilidad con código antiguo)
$conexion = mysqli_connect($host, $usuario, $password, $base_datos);

if(!$conexion) {
    die("Error mysqli: " . mysqli_connect_error());
}

mysqli_set_charset($conexion, "utf8mb4");
?>