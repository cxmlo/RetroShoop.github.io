<?php
session_start();
include '../conexion.php';

header('Content-Type: application/json');

try {
    // Obtener 3 productos aleatorios
    $query = mysqli_query($conexion, "SELECT * FROM productos ORDER BY RAND() LIMIT 3");
    
    if (!$query) {
        throw new Exception('Error en la consulta: ' . mysqli_error($conexion));
    }
    
    $products = [];
    while ($producto = mysqli_fetch_assoc($query)) {
        $products[] = [
            'id' => $producto['id'],
            'nombre' => $producto['nombre'],
            'precio' => $producto['precio'],
            'imagen' => $producto['imagen'],
            'descripcion' => $producto['descripcion'],
            'categoria' => $producto['categoria']
        ];
    }
    
    echo json_encode([
        'success' => true,
        'products' => $products
    ]);
    
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}
?>