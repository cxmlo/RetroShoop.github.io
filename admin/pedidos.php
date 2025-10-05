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

$action = $_POST['action'] ?? $_GET['action'] ?? '';

switch($action) {
    case 'list':
        listPedidos();
        break;
    case 'updateStatus':
        updatePedidoStatus();
        break;
    case 'getDetails':
        getPedidoDetails();
        break;
    case 'delete':
        deletePedido();
        break;
    default:
        echo json_encode(['success' => false, 'message' => 'Acción no válida']);
}

function listPedidos() {
    global $conexion;
    
    // Obtener todos los pedidos con información del usuario
    $query = "SELECT p.*, u.nombre as usuario_nombre, u.correo
              FROM pedidos p
              INNER JOIN usuario u ON p.usuario_id = u.id
              ORDER BY p.fecha_pedido DESC";
    
    $result = mysqli_query($conexion, $query);
    
    if(!$result) {
        echo json_encode(['success' => false, 'message' => 'Error al consultar pedidos']);
        return;
    }
    
    $pedidos = [];
    
    while($row = mysqli_fetch_assoc($result)) {
        // Obtener items de cada pedido
        $pedido_id = $row['id'];
        $items_query = "SELECT * FROM pedidos_items WHERE pedido_id = $pedido_id";
        $items_result = mysqli_query($conexion, $items_query);
        
        $items = [];
        while($item = mysqli_fetch_assoc($items_result)) {
            $items[] = $item;
        }
        
        $row['items'] = $items;
        $pedidos[] = $row;
    }
    
    echo json_encode(['success' => true, 'data' => $pedidos]);
}

function updatePedidoStatus() {
    global $conexion;
    
    $pedido_id = mysqli_real_escape_string($conexion, $_POST['pedido_id']);
    $nuevo_estado = mysqli_real_escape_string($conexion, $_POST['estado']);
    
    $estados_validos = ['pendiente', 'procesando', 'enviado', 'entregado', 'cancelado'];
    
    if(!in_array($nuevo_estado, $estados_validos)) {
        echo json_encode(['success' => false, 'message' => 'Estado no válido']);
        return;
    }
    
    $query = "UPDATE pedidos SET estado = '$nuevo_estado' WHERE id = '$pedido_id'";
    
    if(mysqli_query($conexion, $query)) {
        echo json_encode(['success' => true, 'message' => 'Estado actualizado correctamente']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Error al actualizar estado']);
    }
}

function getPedidoDetails() {
    global $conexion;
    
    $pedido_id = mysqli_real_escape_string($conexion, $_GET['pedido_id']);
    
    $query = "SELECT p.*, u.nombre as usuario_nombre, u.correo, u.foto
              FROM pedidos p
              INNER JOIN usuario u ON p.usuario_id = u.id
              WHERE p.id = '$pedido_id'";
    
    $result = mysqli_query($conexion, $query);
    
    if(!$result) {
        echo json_encode(['success' => false, 'message' => 'Error al consultar pedido']);
        return;
    }
    
    $pedido = mysqli_fetch_assoc($result);
    
    if(!$pedido) {
        echo json_encode(['success' => false, 'message' => 'Pedido no encontrado']);
        return;
    }
    
    // Obtener items
    $items_query = "SELECT * FROM pedidos_items WHERE pedido_id = '$pedido_id'";
    $items_result = mysqli_query($conexion, $items_query);
    
    $items = [];
    while($item = mysqli_fetch_assoc($items_result)) {
        $items[] = $item;
    }
    
    $pedido['items'] = $items;
    
    echo json_encode(['success' => true, 'data' => $pedido]);
}

function deletePedido() {
    global $conexion;
    
    $pedido_id = mysqli_real_escape_string($conexion, $_POST['pedido_id']);
    
    // Eliminar primero los items
    mysqli_query($conexion, "DELETE FROM pedidos_items WHERE pedido_id = '$pedido_id'");
    
    // Eliminar el pedido
    $query = "DELETE FROM pedidos WHERE id = '$pedido_id'";
    
    if(mysqli_query($conexion, $query)) {
        echo json_encode(['success' => true, 'message' => 'Pedido eliminado correctamente']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Error al eliminar pedido']);
    }
}
?>