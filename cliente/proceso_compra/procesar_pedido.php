<?php
// Mostrar errores para debug
error_reporting(E_ALL);
ini_set('display_errors', 1);

session_start();
include '../../conexion.php';

// Verificar que sea cliente
if(!isset($_SESSION['correo']) || $_SESSION["nivelusuario"] != 2) {
    header('location: ../../error.php');
    exit;
}

$usuario_id = $_SESSION['usuario_id'];

// Validar que venga del formulario
if($_SERVER['REQUEST_METHOD'] !== 'POST') {
    $_SESSION['error_message'] = 'Acceso no válido';
    header('location: checkout.php');
    exit;
}

// Log para debug
error_log("=== INICIANDO PROCESO DE PEDIDO ===");
error_log("Usuario ID: " . $usuario_id);

// Obtener datos del formulario
$nombre = mysqli_real_escape_string($conexion, $_POST['nombre'] ?? '');
$telefono = mysqli_real_escape_string($conexion, $_POST['telefono'] ?? '');
$direccion = mysqli_real_escape_string($conexion, $_POST['direccion'] ?? '');
$notas = mysqli_real_escape_string($conexion, $_POST['notas'] ?? '');
$metodo_pago = mysqli_real_escape_string($conexion, $_POST['metodo_pago'] ?? '');

error_log("Datos recibidos - Nombre: $nombre, Teléfono: $telefono, Método: $metodo_pago");

// Validar datos
if(empty($nombre) || empty($telefono) || empty($direccion) || empty($metodo_pago)) {
    $_SESSION['error_message'] = 'Faltan datos del formulario';
    header('location: checkout.php');
    exit;
}

// Obtener items del carrito
$query_cart = "SELECT c.id, c.cantidad, c.talla, c.producto_id,
                      p.nombre, p.precio
               FROM carrito c
               INNER JOIN productos p ON c.producto_id = p.id
               WHERE c.usuario_id = $usuario_id";

error_log("Query carrito: " . $query_cart);

$result_cart = mysqli_query($conexion, $query_cart);

if(!$result_cart) {
    error_log("Error en query carrito: " . mysqli_error($conexion));
    $_SESSION['error_message'] = 'Error al obtener el carrito: ' . mysqli_error($conexion);
    header('location: checkout.php');
    exit;
}

if(mysqli_num_rows($result_cart) == 0) {
    error_log("Carrito vacío para usuario $usuario_id");
    $_SESSION['error_message'] = 'Tu carrito está vacío';
    header('location: ../cliente.php');
    exit;
}

// Calcular totales
$subtotal = 0;
$items = [];

while($item = mysqli_fetch_assoc($result_cart)) {
    $items[] = $item;
    $subtotal += ($item['precio'] * $item['cantidad']);
}

error_log("Items en carrito: " . count($items) . ", Subtotal: $subtotal");

$envio = 0;
$total = $subtotal + $envio;

// Iniciar transacción
mysqli_begin_transaction($conexion);

try {
    // 1. Insertar el pedido principal
    $query_pedido = "INSERT INTO pedidos (
        usuario_id, 
        total, 
        subtotal, 
        estado, 
        metodo_pago, 
        direccion_envio, 
        telefono, 
        notas
    ) VALUES (
        $usuario_id,
        $total,
        $subtotal,
        'pendiente',
        '$metodo_pago',
        '$direccion',
        '$telefono',
        '$notas'
    )";
    
    error_log("Query pedido: " . $query_pedido);
    
    if(!mysqli_query($conexion, $query_pedido)) {
        throw new Exception('Error al crear el pedido: ' . mysqli_error($conexion));
    }
    
    $pedido_id = mysqli_insert_id($conexion);
    error_log("Pedido creado con ID: $pedido_id");
    
    if(!$pedido_id) {
        throw new Exception('No se pudo obtener el ID del pedido');
    }
    
    // 2. Insertar los items del pedido
    foreach($items as $item) {
        $subtotal_item = $item['precio'] * $item['cantidad'];
        $talla = mysqli_real_escape_string($conexion, $item['talla']);
        $nombre_producto = mysqli_real_escape_string($conexion, $item['nombre']);
        
        $query_item = "INSERT INTO pedidos_items (
            pedido_id,
            producto_id,
            nombre_producto,
            precio,
            cantidad,
            talla,
            subtotal
        ) VALUES (
            $pedido_id,
            {$item['producto_id']},
            '$nombre_producto',
            {$item['precio']},
            {$item['cantidad']},
            '$talla',
            $subtotal_item
        )";
        
        error_log("Query item: " . $query_item);
        
        if(!mysqli_query($conexion, $query_item)) {
            throw new Exception('Error al guardar item del pedido: ' . mysqli_error($conexion));
        }
    }
    
    error_log("Items del pedido guardados correctamente");
    
    // 3. Vaciar el carrito del usuario
    $query_vaciar = "DELETE FROM carrito WHERE usuario_id = $usuario_id";
    
    if(!mysqli_query($conexion, $query_vaciar)) {
        throw new Exception('Error al vaciar el carrito: ' . mysqli_error($conexion));
    }
    
    error_log("Carrito vaciado");
    
    // Confirmar transacción
    mysqli_commit($conexion);
    error_log("=== TRANSACCIÓN COMPLETADA EXITOSAMENTE ===");
    
    // Redirigir a página de confirmación
    $_SESSION['pedido_id'] = $pedido_id;
    header('location: confirmacion.php');
    exit;
    
} catch(Exception $e) {
    // Revertir cambios si hay error
    mysqli_rollback($conexion);
    error_log("ERROR EN TRANSACCIÓN: " . $e->getMessage());
    
    $_SESSION['error_message'] = 'Error al procesar el pedido: ' . $e->getMessage();
    header('location: checkout.php');
    exit;
}
?>