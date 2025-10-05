<?php
// Desactivar errores visibles (solo para producción)
error_reporting(0);
ini_set('display_errors', 0);

session_start();

// Limpiar cualquier output previo
if (ob_get_level()) ob_clean();

// Incluir conexión
include '../../conexion.php';

// Establecer header JSON
header('Content-Type: application/json; charset=utf-8');

// Verificar que el usuario esté autenticado
if(!isset($_SESSION['correo']) || !isset($_SESSION['usuario_id'])) {
    echo json_encode(['success' => false, 'message' => 'Usuario no autenticado']);
    exit;
}

// Verificar conexión a BD
if(!$conexion) {
    echo json_encode(['success' => false, 'message' => 'Error de conexión a BD']);
    exit;
}

$usuario_id = intval($_SESSION['usuario_id']);

// Obtener action desde GET o POST
$action = '';
if(isset($_GET['action'])) {
    $action = $_GET['action'];
} elseif(isset($_POST['action'])) {
    $action = $_POST['action'];
}

switch($action) {
    case 'agregar':
        try {
            $producto_id = isset($_POST['producto_id']) ? intval($_POST['producto_id']) : 0;
            $cantidad = isset($_POST['cantidad']) ? intval($_POST['cantidad']) : 1;
            $talla = isset($_POST['talla']) ? mysqli_real_escape_string($conexion, $_POST['talla']) : '';
            
            if($producto_id <= 0) {
                echo json_encode(['success' => false, 'message' => 'ID de producto inválido']);
                exit;
            }
            
            // Verificar que el producto existe
            $check_producto = mysqli_query($conexion, "SELECT id FROM productos WHERE id = $producto_id");
            if(!$check_producto || mysqli_num_rows($check_producto) == 0) {
                echo json_encode(['success' => false, 'message' => 'Producto no encontrado']);
                exit;
            }
            
            // Verificar si ya existe en el carrito
            if($talla != '') {
                $check_query = "SELECT id, cantidad FROM carrito 
                               WHERE usuario_id = $usuario_id 
                               AND producto_id = $producto_id 
                               AND talla = '$talla'";
            } else {
                $check_query = "SELECT id, cantidad FROM carrito 
                               WHERE usuario_id = $usuario_id 
                               AND producto_id = $producto_id 
                               AND (talla IS NULL OR talla = '')";
            }
            
            $check = mysqli_query($conexion, $check_query);
            
            if($check && mysqli_num_rows($check) > 0) {
                // Actualizar cantidad existente
                $row = mysqli_fetch_assoc($check);
                $nueva_cantidad = $row['cantidad'] + $cantidad;
                $update_query = "UPDATE carrito SET cantidad = $nueva_cantidad WHERE id = {$row['id']}";
                $update = mysqli_query($conexion, $update_query);
                
                if($update) {
                    echo json_encode(['success' => true, 'message' => 'Cantidad actualizada']);
                } else {
                    echo json_encode(['success' => false, 'message' => 'Error al actualizar: ' . mysqli_error($conexion)]);
                }
            } else {
                // Insertar nuevo item
                if($talla != '') {
                    $insert_query = "INSERT INTO carrito (usuario_id, producto_id, cantidad, talla) 
                                    VALUES ($usuario_id, $producto_id, $cantidad, '$talla')";
                } else {
                    $insert_query = "INSERT INTO carrito (usuario_id, producto_id, cantidad) 
                                    VALUES ($usuario_id, $producto_id, $cantidad)";
                }
                
                $insert = mysqli_query($conexion, $insert_query);
                
                if($insert) {
                    echo json_encode(['success' => true, 'message' => 'Producto agregado']);
                } else {
                    echo json_encode(['success' => false, 'message' => 'Error al agregar: ' . mysqli_error($conexion)]);
                }
            }
        } catch(Exception $e) {
            echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
        }
        break;
        
    case 'obtener':
        try {
            $query = "SELECT c.id as carrito_id, c.cantidad, c.talla, 
                             p.id, p.nombre, p.precio, p.imagen
                      FROM carrito c
                      INNER JOIN productos p ON c.producto_id = p.id
                      WHERE c.usuario_id = $usuario_id
                      ORDER BY c.fecha_agregado DESC";
            
            $resultado = mysqli_query($conexion, $query);
            $items = [];
            
            if($resultado) {
                while($row = mysqli_fetch_assoc($resultado)) {
                    $items[] = $row;
                }
                echo json_encode(['success' => true, 'items' => $items]);
            } else {
                echo json_encode(['success' => false, 'message' => 'Error al obtener carrito: ' . mysqli_error($conexion)]);
            }
        } catch(Exception $e) {
            echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
        }
        break;
        
    case 'actualizar':
        try {
            $carrito_id = isset($_POST['carrito_id']) ? intval($_POST['carrito_id']) : 0;
            $cantidad = isset($_POST['cantidad']) ? intval($_POST['cantidad']) : 0;
            
            if($carrito_id <= 0) {
                echo json_encode(['success' => false, 'message' => 'ID de carrito inválido']);
                exit;
            }
            
            if($cantidad > 0) {
                $update_query = "UPDATE carrito SET cantidad = $cantidad 
                                WHERE id = $carrito_id AND usuario_id = $usuario_id";
                $update = mysqli_query($conexion, $update_query);
                
                if($update) {
                    echo json_encode(['success' => true, 'message' => 'Cantidad actualizada']);
                } else {
                    echo json_encode(['success' => false, 'message' => 'Error al actualizar: ' . mysqli_error($conexion)]);
                }
            } else {
                // Si la cantidad es 0 o negativa, eliminar
                $delete_query = "DELETE FROM carrito WHERE id = $carrito_id AND usuario_id = $usuario_id";
                $delete = mysqli_query($conexion, $delete_query);
                
                if($delete) {
                    echo json_encode(['success' => true, 'message' => 'Producto eliminado']);
                } else {
                    echo json_encode(['success' => false, 'message' => 'Error al eliminar: ' . mysqli_error($conexion)]);
                }
            }
        } catch(Exception $e) {
            echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
        }
        break;
        
    case 'eliminar':
        try {
            $carrito_id = isset($_POST['carrito_id']) ? intval($_POST['carrito_id']) : 0;
            
            if($carrito_id <= 0) {
                echo json_encode(['success' => false, 'message' => 'ID de carrito inválido']);
                exit;
            }
            
            $delete_query = "DELETE FROM carrito WHERE id = $carrito_id AND usuario_id = $usuario_id";
            $delete = mysqli_query($conexion, $delete_query);
            
            if($delete) {
                echo json_encode(['success' => true, 'message' => 'Producto eliminado del carrito']);
            } else {
                echo json_encode(['success' => false, 'message' => 'Error al eliminar: ' . mysqli_error($conexion)]);
            }
        } catch(Exception $e) {
            echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
        }
        break;
        
    case 'vaciar':
        try {
            $delete_query = "DELETE FROM carrito WHERE usuario_id = $usuario_id";
            $delete_all = mysqli_query($conexion, $delete_query);
            
            if($delete_all) {
                echo json_encode(['success' => true, 'message' => 'Carrito vaciado']);
            } else {
                echo json_encode(['success' => false, 'message' => 'Error al vaciar: ' . mysqli_error($conexion)]);
            }
        } catch(Exception $e) {
            echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
        }
        break;
        
    case 'contar':
        try {
            $query = "SELECT SUM(cantidad) as total FROM carrito WHERE usuario_id = $usuario_id";
            $resultado = mysqli_query($conexion, $query);
            
            if($resultado) {
                $row = mysqli_fetch_assoc($resultado);
                $total = $row['total'] ?? 0;
                echo json_encode(['success' => true, 'total' => intval($total)]);
            } else {
                echo json_encode(['success' => false, 'message' => 'Error al contar: ' . mysqli_error($conexion)]);
            }
        } catch(Exception $e) {
            echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
        }
        break;
        
    default:
        echo json_encode(['success' => false, 'message' => 'Acción no válida: ' . $action]);
        break;
}

mysqli_close($conexion);