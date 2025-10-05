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
    case 'save':
        saveProduct();
        break;
    case 'update':
        updateProduct();
        break;
    case 'delete':
        deleteProduct();
        break;
    case 'get':
        getProduct();
        break;
    default:
        echo json_encode(['success' => false, 'message' => 'Acción no válida']);
}

function saveProduct() {
    global $conexion;
    
    $nombre = mysqli_real_escape_string($conexion, $_POST['nombre'] ?? '');
    $categoria = mysqli_real_escape_string($conexion, $_POST['categoria'] ?? '');
    $precio = mysqli_real_escape_string($conexion, $_POST['precio'] ?? 0);
    $stock = mysqli_real_escape_string($conexion, $_POST['stock'] ?? 10);
    $descripcion = mysqli_real_escape_string($conexion, $_POST['descripcion'] ?? '');
    
    // Validar datos requeridos
    if(empty($nombre) || empty($categoria) || empty($precio)) {
        echo json_encode(['success' => false, 'message' => 'Faltan datos requeridos']);
        return;
    }
    
    // Manejo de imagen
    $imagen = '';
    if(isset($_FILES['imagen']) && $_FILES['imagen']['error'] == 0) {
        $imagen = uploadImage($_FILES['imagen'], '../img/producto/');
        if(!$imagen) {
            echo json_encode(['success' => false, 'message' => 'Error al subir la imagen. Verifica permisos de carpeta (755 o 777).']);
            return;
        }
    } else {
        $error = $_FILES['imagen']['error'] ?? 'No file';
        echo json_encode(['success' => false, 'message' => 'La imagen es requerida. Error: ' . $error]);
        return;
    }
    
    $query = "INSERT INTO productos (nombre, categoria, precio, stock, descripcion, imagen) 
              VALUES ('$nombre', '$categoria', '$precio', '$stock', '$descripcion', '$imagen')";
    
    if(mysqli_query($conexion, $query)) {
        echo json_encode([
            'success' => true, 
            'message' => 'Producto creado exitosamente',
            'id' => mysqli_insert_id($conexion)
        ]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Error al crear el producto: ' . mysqli_error($conexion)]);
    }
}

function updateProduct() {
    global $conexion;
    
    $id = mysqli_real_escape_string($conexion, $_POST['id']);
    $nombre = mysqli_real_escape_string($conexion, $_POST['nombre']);
    $categoria = mysqli_real_escape_string($conexion, $_POST['categoria']);
    $precio = mysqli_real_escape_string($conexion, $_POST['precio']);
    $stock = mysqli_real_escape_string($conexion, $_POST['stock'] ?? 10);
    $descripcion = mysqli_real_escape_string($conexion, $_POST['descripcion'] ?? '');
    
    // Verificar si se subió una nueva imagen
    if(isset($_FILES['imagen']) && $_FILES['imagen']['error'] == 0) {
        // Obtener imagen anterior
        $query_old = "SELECT imagen FROM productos WHERE id = '$id'";
        $result = mysqli_query($conexion, $query_old);
        $old_product = mysqli_fetch_assoc($result);
        
        // Subir nueva imagen
        $nueva_imagen = uploadImage($_FILES['imagen'], '../img/producto/');
        if(!$nueva_imagen) {
            echo json_encode(['success' => false, 'message' => 'Error al subir la nueva imagen']);
            return;
        }
        
        // Eliminar imagen anterior
        if($old_product && $old_product['imagen']) {
            $old_path = '../img/producto/' . $old_product['imagen'];
            if(file_exists($old_path)) {
                unlink($old_path);
            }
        }
        
        $query = "UPDATE productos SET 
                  nombre = '$nombre',
                  categoria = '$categoria',
                  precio = '$precio',
                  stock = '$stock',
                  descripcion = '$descripcion',
                  imagen = '$nueva_imagen'
                  WHERE id = '$id'";
    } else {
        $query = "UPDATE productos SET 
                  nombre = '$nombre',
                  categoria = '$categoria',
                  precio = '$precio',
                  stock = '$stock',
                  descripcion = '$descripcion'
                  WHERE id = '$id'";
    }
    
    if(mysqli_query($conexion, $query)) {
        echo json_encode(['success' => true, 'message' => 'Producto actualizado exitosamente']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Error al actualizar: ' . mysqli_error($conexion)]);
    }
}

function deleteProduct() {
    global $conexion;
    
    $id = mysqli_real_escape_string($conexion, $_POST['id']);
    
    $query = "SELECT imagen FROM productos WHERE id = '$id'";
    $result = mysqli_query($conexion, $query);
    $product = mysqli_fetch_assoc($result);
    
    $delete_query = "DELETE FROM productos WHERE id = '$id'";
    
    if(mysqli_query($conexion, $delete_query)) {
        if($product && $product['imagen']) {
            $image_path = '../img/producto/' . $product['imagen'];
            if(file_exists($image_path)) {
                unlink($image_path);
            }
        }
        echo json_encode(['success' => true, 'message' => 'Producto eliminado exitosamente']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Error al eliminar: ' . mysqli_error($conexion)]);
    }
}

function getProduct() {
    global $conexion;
    
    $id = mysqli_real_escape_string($conexion, $_GET['id']);
    $query = "SELECT * FROM productos WHERE id = '$id'";
    $result = mysqli_query($conexion, $query);
    
    if($product = mysqli_fetch_assoc($result)) {
        echo json_encode(['success' => true, 'data' => $product]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Producto no encontrado']);
    }
}

function uploadImage($file, $target_dir) {
    // Crear directorio si no existe CON PERMISOS
    if (!file_exists($target_dir)) {
        if(!mkdir($target_dir, 0755, true)) {
            error_log("ERROR: No se pudo crear directorio " . $target_dir);
            return false;
        }
        chmod($target_dir, 0755);
    }
    
    // Verificar permisos de escritura
    if(!is_writable($target_dir)) {
        error_log("ERROR: Directorio no escribible " . $target_dir);
        error_log("Permisos actuales: " . substr(sprintf('%o', fileperms($target_dir)), -4));
        return false;
    }
    
    $allowed_types = ['image/jpeg', 'image/png', 'image/gif', 'image/jpg'];
    $max_size = 20 * 1024 * 1024; // 10MB
    
    // Validar tipo
    if(!in_array($file['type'], $allowed_types)) {
        error_log("ERROR: Tipo no permitido " . $file['type']);
        return false;
    }
    
    // Validar tamaño
    if($file['size'] > $max_size) {
        error_log("ERROR: Archivo muy grande " . $file['size']);
        return false;
    }
    
    // Generar nombre único
    $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
    $filename = 'producto_' . uniqid() . '_' . time() . '.' . $extension;
    $target_file = $target_dir . $filename;
    
    // Mover archivo
    if(move_uploaded_file($file['tmp_name'], $target_file)) {
        chmod($target_file, 0644);
        error_log("SUCCESS: Imagen guardada " . $target_file);
        return $filename;
    }
    
    error_log("ERROR: No se pudo mover archivo de " . $file['tmp_name'] . " a " . $target_file);
    return false;
}
?>