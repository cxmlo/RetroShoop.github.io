<?php
session_start();
include '../conexion.php';

// DEBUG - ELIMINAR DESPUÉS
error_log("=== DEBUG PROCESAR_PERFIL ===");
error_log("POST action: " . ($_POST['action'] ?? 'NO ACTION'));
error_log("FILES: " . print_r($_FILES, true));
error_log("SESSION usuario_id: " . ($_SESSION['usuario_id'] ?? 'NO ID'));
error_log("SESSION nivelusuario: " . ($_SESSION['nivelusuario'] ?? 'NO NIVEL'));
// FIN DEBUG

// Verificar que sea cliente
if(!isset($_SESSION['correo']) || $_SESSION["nivelusuario"] != 2) {
    echo json_encode(['success' => false, 'message' => 'No autorizado']);
    exit;
}

// ... resto del código

// Verificar que sea cliente
if(!isset($_SESSION['correo']) || $_SESSION["nivelusuario"] != 2) {
    echo json_encode(['success' => false, 'message' => 'No autorizado']);
    exit;
}

$usuario_id = $_SESSION['usuario_id'];
$action = $_POST['action'] ?? '';

// ==================== SUBIR FOTO DE PERFIL ====================

if ($action === 'upload_photo') {
    if (!isset($_FILES['foto']) || $_FILES['foto']['error'] !== UPLOAD_ERR_OK) {
        echo json_encode(['success' => false, 'message' => 'Error al subir el archivo']);
        exit;
    }
    
    $foto = $_FILES['foto'];
    $tmp_name = $foto['tmp_name'];
    $tamano = $foto['size'];
    
    // El blob siempre viene como JPEG desde JavaScript
    $extension = 'jpg';
    
    // Validar tamaño (máx 1MB después de optimización)
    if ($tamano > 1024 * 1024) {
        echo json_encode(['success' => false, 'message' => 'La imagen debe ser menor a 1MB']);
        exit;
    }
    
    // Generar nombre único
    $nuevo_nombre = 'user_' . $usuario_id . '_' . time() . '.jpg';
    $ruta_destino = '../img/usuario/' . $nuevo_nombre;
    
    // Crear directorio si no existe
    if (!file_exists('../img/usuario/')) {
        mkdir('../img/usuario/', 0777, true);
        chmod('../img/usuario/', 0755);
    }
    
    // Obtener foto anterior
    $query_foto_anterior = mysqli_query($conexion, "SELECT foto FROM usuario WHERE id = $usuario_id");
    $foto_anterior = mysqli_fetch_assoc($query_foto_anterior)['foto'] ?? 'default.jpg';
    
    // Mover archivo
    if (move_uploaded_file($tmp_name, $ruta_destino)) {
        // Cambiar permisos del archivo
        chmod($ruta_destino, 0644);
        
        // Actualizar base de datos
        $query = mysqli_query($conexion, "UPDATE usuario SET foto = '$nuevo_nombre' WHERE id = $usuario_id");
        
        if ($query) {
            // Eliminar foto anterior
            if ($foto_anterior && $foto_anterior != 'default.jpg' && file_exists('../img/usuario/' . $foto_anterior)) {
                unlink('../img/usuario/' . $foto_anterior);
            }
            
            echo json_encode([
                'success' => true, 
                'message' => 'Foto actualizada correctamente', 
                'foto' => $nuevo_nombre
            ]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Error al actualizar la base de datos: ' . mysqli_error($conexion)]);
        }
    } else {
        echo json_encode(['success' => false, 'message' => 'Error al guardar la imagen. Verifica permisos de carpeta.']);
    }
    exit;
}

// ==================== ACTUALIZAR DATOS PERSONALES ====================

if ($action === 'update_datos') {
    $nombre = mysqli_real_escape_string($conexion, trim($_POST['nombre']));
    $correo = mysqli_real_escape_string($conexion, trim($_POST['correo']));
    
    // Validar campos
    if (empty($nombre) || empty($correo)) {
        echo json_encode(['success' => false, 'message' => 'Todos los campos son obligatorios']);
        exit;
    }
    
    // Validar formato de correo
    if (!filter_var($correo, FILTER_VALIDATE_EMAIL)) {
        echo json_encode(['success' => false, 'message' => 'Correo electrónico no válido']);
        exit;
    }
    
    // Verificar si el correo ya existe (excepto el del usuario actual)
    $check_correo = mysqli_query($conexion, "SELECT id FROM usuario WHERE correo = '$correo' AND id != $usuario_id");
    if (mysqli_num_rows($check_correo) > 0) {
        echo json_encode(['success' => false, 'message' => 'El correo ya está registrado']);
        exit;
    }
    
    // Actualizar datos
    $query = mysqli_query($conexion, "
        UPDATE usuario 
        SET nombre = '$nombre', 
            correo = '$correo' 
        WHERE id = $usuario_id
    ");
    
    if ($query) {
        // Actualizar sesión
        $_SESSION['correo'] = $correo;
        
        echo json_encode(['success' => true, 'message' => 'Datos actualizados correctamente']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Error al actualizar los datos']);
    }
    exit;
}

// ==================== CAMBIAR CONTRASEÑA ====================

if ($action === 'change_password') {
    $current_password = $_POST['current_password'];
    $new_password = $_POST['new_password'];
    $confirm_password = $_POST['confirm_password'];
    
    // Validar campos
    if (empty($current_password) || empty($new_password) || empty($confirm_password)) {
        echo json_encode(['success' => false, 'message' => 'Todos los campos son obligatorios']);
        exit;
    }
    
    // Validar que las contraseñas nuevas coincidan
    if ($new_password !== $confirm_password) {
        echo json_encode(['success' => false, 'message' => 'Las contraseñas nuevas no coinciden']);
        exit;
    }
    
    // Validar longitud mínima
    if (strlen($new_password) < 6) {
        echo json_encode(['success' => false, 'message' => 'La contraseña debe tener al menos 6 caracteres']);
        exit;
    }
    
    // Obtener contraseña actual de la base de datos
    $query = mysqli_query($conexion, "SELECT PassUsuario FROM usuario WHERE id = $usuario_id");
    $usuario = mysqli_fetch_assoc($query);
    
    // Verificar contraseña actual
    if (!password_verify($current_password, $usuario['PassUsuario'])) {
        echo json_encode(['success' => false, 'message' => 'La contraseña actual es incorrecta']);
        exit;
    }
    
    // Encriptar nueva contraseña
    $new_password_hash = password_hash($new_password, PASSWORD_DEFAULT);
    
    // Actualizar contraseña
    $query = mysqli_query($conexion, "
        UPDATE usuario 
        SET PassUsuario = '$new_password_hash' 
        WHERE id = $usuario_id
    ");
    
    if ($query) {
        echo json_encode(['success' => true, 'message' => 'Contraseña actualizada correctamente']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Error al actualizar la contraseña']);
    }
    exit;
}
?>