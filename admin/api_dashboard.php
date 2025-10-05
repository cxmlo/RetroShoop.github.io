<?php
session_start();
include '../conexion.php';

if(!isset($_SESSION['correo']) || $_SESSION["nivelusuario"] != 1) {
    echo json_encode(['success' => false, 'message' => 'No autorizado']);
    exit;
}

header('Content-Type: application/json');

$action = $_GET['action'] ?? '';

switch($action) {
    case 'ventas_mensuales':
        getVentasMensuales();
        break;
    case 'productos_mas_vendidos':
        getProductosMasVendidos();
        break;
    default:
        echo json_encode(['success' => false, 'message' => 'Acción no válida']);
}

function getVentasMensuales() {
    global $conexion;
    
    $query = "SELECT 
                DATE_FORMAT(fecha_pedido, '%Y-%m') as mes,
                SUM(total) as total_ventas
              FROM pedidos 
              WHERE estado != 'cancelado'
              AND fecha_pedido >= DATE_SUB(CURRENT_DATE, INTERVAL 6 MONTH)
              GROUP BY DATE_FORMAT(fecha_pedido, '%Y-%m')
              ORDER BY mes ASC";
    
    $result = mysqli_query($conexion, $query);
    
    $meses = [];
    $ventas = [];
    
    $meses_nombres = [
        '01' => 'Ene', '02' => 'Feb', '03' => 'Mar', '04' => 'Abr',
        '05' => 'May', '06' => 'Jun', '07' => 'Jul', '08' => 'Ago',
        '09' => 'Sep', '10' => 'Oct', '11' => 'Nov', '12' => 'Dic'
    ];
    
    while($row = mysqli_fetch_assoc($result)) {
        $mes_num = substr($row['mes'], 5, 2);
        $meses[] = $meses_nombres[$mes_num];
        $ventas[] = floatval($row['total_ventas']);
    }
    
    echo json_encode([
        'success' => true,
        'meses' => $meses,
        'ventas' => $ventas
    ]);
}

function getProductosMasVendidos() {
    global $conexion;
    
    $query = "SELECT 
                pi.nombre_producto,
                SUM(pi.cantidad) as total_vendido
              FROM pedidos_items pi
              JOIN pedidos p ON pi.pedido_id = p.id
              WHERE p.estado != 'cancelado'
              GROUP BY pi.nombre_producto
              ORDER BY total_vendido DESC
              LIMIT 5";
    
    $result = mysqli_query($conexion, $query);
    
    $productos = [];
    $cantidades = [];
    
    while($row = mysqli_fetch_assoc($result)) {
        $productos[] = $row['nombre_producto'];
        $cantidades[] = intval($row['total_vendido']);
    }
    
    echo json_encode([
        'success' => true,
        'productos' => $productos,
        'cantidades' => $cantidades
    ]);
}
?>