<?php
session_start();
include '../seguridad.php';
include '../conexion.php';

// Verificar que sea administrador
if(!isset($_SESSION['correo']) || $_SESSION["nivelusuario"] != 1) {
    echo json_encode(['success' => false, 'message' => 'Acceso denegado']);
    exit;
}

header('Content-Type: application/json');

$action = $_GET['action'] ?? $_POST['action'] ?? '';

switch($action) {
    case 'list':
        listCarritosActivos();
        break;
    case 'clear':
        clearCarritoUsuario();
        break;
    default:
        echo json_encode(['success' => false, 'message' => 'Acción no válida']);
}

function listCarritosActivos() {
    global $conexion;
    
    // Obtener todos los carritos activos agrupados por usuario
    $query = "SELECT c.id, c.cantidad, c.talla, c.fecha_agregado,
                     p.nombre, p.precio, p.imagen,
                     u.id as usuario_id, u.nombre as usuario_nombre, u.correo
              FROM carrito c
              INNER JOIN productos p ON c.producto_id = p.id
              INNER JOIN usuario u ON c.usuario_id = u.id
              ORDER BY c.usuario_id, c.fecha_agregado DESC";
    
    $result = mysqli_query($conexion, $query);
    
    if(!$result) {
        echo json_encode(['success' => false, 'message' => 'Error al consultar carritos']);
        return;
    }
    
    $carritos = [];
    
    while($row = mysqli_fetch_assoc($result)) {
        $usuario_id = $row['usuario_id'];
        
        if(!isset($carritos[$usuario_id])) {
            $carritos[$usuario_id] = [
                'usuario_id' => $usuario_id,
                'usuario' => $row['usuario_nombre'],
                'correo' => $row['correo'],
                'items' => [],
                'total' => 0
            ];
        }
        
        $subtotal = $row['precio'] * $row['cantidad'];
        
        $carritos[$usuario_id]['items'][] = [
            'id' => $row['id'],
            'nombre' => $row['nombre'],
            'precio' => $row['precio'],
            'cantidad' => $row['cantidad'],
            'talla' => $row['talla'],
            'subtotal' => $subtotal,
            'imagen' => $row['imagen'],
            'fecha_agregado' => $row['fecha_agregado']
        ];
        
        $carritos[$usuario_id]['total'] += $subtotal;
    }
    
    echo json_encode(['success' => true, 'data' => array_values($carritos)]);
}

function clearCarritoUsuario() {
    global $conexion;
    
    $usuario_id = mysqli_real_escape_string($conexion, $_POST['usuario_id']);
    
    $query = "DELETE FROM carrito WHERE usuario_id = '$usuario_id'";
    
    if(mysqli_query($conexion, $query)) {
        echo json_encode(['success' => true, 'message' => 'Carrito limpiado exitosamente']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Error al limpiar carrito']);
    }
}
?>