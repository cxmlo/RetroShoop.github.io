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
            saveUser();
            break;
        case 'update':
            updateUser();
            break;
        case 'delete':
            deleteUser();
            break;
        case 'get':
            getUser();
            break;
        case 'getOrders':
            getUserOrders();
            break;
        default:
            echo json_encode(['success' => false, 'message' => 'Acción no válida']);
    }

    function saveUser() {
        global $conexion;
        
        // Debug
        error_log("saveUser called");
        error_log("POST: " . print_r($_POST, true));
        
        $nombre = mysqli_real_escape_string($conexion, $_POST['nombre'] ?? '');
        $correo = mysqli_real_escape_string($conexion, $_POST['correo'] ?? '');
        $password = mysqli_real_escape_string($conexion, $_POST['password'] ?? '');
        $nivelusuario = 2; // Usuario normal por defecto
        
        // Validar datos requeridos
        if(empty($nombre) || empty($correo) || empty($password)) {
            echo json_encode(['success' => false, 'message' => 'Todos los campos son requeridos']);
            return;
        }
        
        // Validar contraseña mínima
        if(strlen($password) < 6) {
            echo json_encode(['success' => false, 'message' => 'La contraseña debe tener al menos 6 caracteres']);
            return;
        }
        
        // Verificar si el correo ya existe
        $check_query = "SELECT id FROM usuario WHERE correo = '$correo'";
        $check_result = mysqli_query($conexion, $check_query);
        
        if(mysqli_num_rows($check_result) > 0) {
            echo json_encode(['success' => false, 'message' => 'El correo ya está registrado']);
            return;
        }
        
        // Encriptar contraseña
        $password_hash = password_hash($password, PASSWORD_DEFAULT);
        
        // Manejo de foto
        $foto = 'default.jpg';
        if(isset($_FILES['foto']) && $_FILES['foto']['error'] == 0) {
            $uploaded_foto = uploadImage($_FILES['foto'], '../img/usuario/');
            if($uploaded_foto) {
                $foto = $uploaded_foto;
            }
        }
        
        // Usar las columnas correctas: foto (minúscula) y PassUsuario
        $query = "INSERT INTO usuario (nombre, correo, PassUsuario, nivelusuario, foto) 
                VALUES ('$nombre', '$correo', '$password_hash', '$nivelusuario', '$foto')";
        
        error_log("Query: " . $query);
        
        if(mysqli_query($conexion, $query)) {
            echo json_encode([
                'success' => true, 
                'message' => 'Usuario creado exitosamente',
                'id' => mysqli_insert_id($conexion)
            ]);
        } else {
            $error = mysqli_error($conexion);
            error_log("Error SQL: " . $error);
            echo json_encode(['success' => false, 'message' => 'Error al crear el usuario: ' . $error]);
        }
    }

    function updateUser() {
        global $conexion;
        
        // Debug
        error_log("updateUser called");
        error_log("POST: " . print_r($_POST, true));
        
        $id = mysqli_real_escape_string($conexion, $_POST['id'] ?? '');
        $nombre = mysqli_real_escape_string($conexion, $_POST['nombre'] ?? '');
        $correo = mysqli_real_escape_string($conexion, $_POST['correo'] ?? '');
        
        // Validar datos requeridos
        if(empty($id) || empty($nombre) || empty($correo)) {
            echo json_encode(['success' => false, 'message' => 'Faltan datos requeridos']);
            return;
        }
        
        // Verificar si el correo ya existe en otro usuario
        $check_query = "SELECT id FROM usuario WHERE correo = '$correo' AND id != '$id'";
        $check_result = mysqli_query($conexion, $check_query);
        
        if(mysqli_num_rows($check_result) > 0) {
            echo json_encode(['success' => false, 'message' => 'El correo ya está registrado por otro usuario']);
            return;
        }
        
        // Construir query base
        $updates = [
            "nombre = '$nombre'",
            "correo = '$correo'"
        ];
        
        // Actualizar contraseña si se proporcionó
        if(!empty($_POST['password'])) {
            $password = mysqli_real_escape_string($conexion, $_POST['password']);
            if(strlen($password) < 6) {
                echo json_encode(['success' => false, 'message' => 'La contraseña debe tener al menos 6 caracteres']);
                return;
            }
            $password_hash = password_hash($password, PASSWORD_DEFAULT);
            $updates[] = "PassUsuario = '$password_hash'";
        }
        
        // Actualizar foto si se subió una nueva
        if(isset($_FILES['foto']) && $_FILES['foto']['error'] == 0) {
            // Obtener foto anterior para eliminarla
            $query_old = "SELECT foto FROM usuario WHERE id = '$id'";
            $result = mysqli_query($conexion, $query_old);
            $old_user = mysqli_fetch_assoc($result);
            
            // Subir nueva foto
            $nueva_foto = uploadImage($_FILES['foto'], '../img/usuario/');
            if($nueva_foto) {
                // Eliminar foto anterior si no es default.jpg
                if($old_user && $old_user['foto'] && $old_user['foto'] != 'default.jpg') {
                    $old_path = '../img/usuario/' . $old_user['foto'];
                    if(file_exists($old_path)) {
                        unlink($old_path);
                    }
                }
                
                $updates[] = "foto = '$nueva_foto'";
            }
        }
        
        $query = "UPDATE usuario SET " . implode(', ', $updates) . " WHERE id = '$id'";
        
        error_log("Query: " . $query);
        
        if(mysqli_query($conexion, $query)) {
            echo json_encode(['success' => true, 'message' => 'Usuario actualizado exitosamente']);
        } else {
            $error = mysqli_error($conexion);
            error_log("Error SQL: " . $error);
            echo json_encode(['success' => false, 'message' => 'Error al actualizar el usuario: ' . $error]);
        }
    }

    function deleteUser() {
        global $conexion;
        
        $id = mysqli_real_escape_string($conexion, $_POST['id'] ?? '');
        
        if(empty($id)) {
            echo json_encode(['success' => false, 'message' => 'ID de usuario no válido']);
            return;
        }
        
        // No permitir eliminar al usuario actual
        if($id == $_SESSION['usuario_id']) {
            echo json_encode(['success' => false, 'message' => 'No puedes eliminar tu propia cuenta']);
            return;
        }
        
        // Obtener información del usuario para eliminar la foto
        $query = "SELECT foto FROM usuario WHERE id = '$id'";
        $result = mysqli_query($conexion, $query);
        $user = mysqli_fetch_assoc($result);
        
        // Eliminar items del carrito del usuario primero
        mysqli_query($conexion, "DELETE FROM carrito WHERE usuario_id = '$id'");
        
        // Eliminar el usuario de la base de datos
        $delete_query = "DELETE FROM usuario WHERE id = '$id'";
        
        if(mysqli_query($conexion, $delete_query)) {
            // Eliminar la foto del servidor si no es default.jpg
            if($user && $user['foto'] && $user['foto'] != 'default.jpg') {
                $foto_path = '../img/usuario/' . $user['foto'];
                if(file_exists($foto_path)) {
                    unlink($foto_path);
                }
            }
            
            echo json_encode(['success' => true, 'message' => 'Usuario eliminado exitosamente']);
        } else {
            $error = mysqli_error($conexion);
            error_log("Error SQL: " . $error);
            echo json_encode(['success' => false, 'message' => 'Error al eliminar el usuario: ' . $error]);
        }
    }

    function getUser() {
        global $conexion;
        
        $id = mysqli_real_escape_string($conexion, $_GET['id'] ?? '');
        
        if(empty($id)) {
            echo json_encode(['success' => false, 'message' => 'ID de usuario no válido']);
            return;
        }
        
        $query = "SELECT * FROM usuario WHERE id = '$id'";
        $result = mysqli_query($conexion, $query);
        
        if($user = mysqli_fetch_assoc($result)) {
            // No enviar la contraseña
            unset($user['PassUsuario']);
            echo json_encode(['success' => true, 'data' => $user]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Usuario no encontrado']);
        }
    }

    function getUserOrders() {
        global $conexion;
        
        $usuario_id = mysqli_real_escape_string($conexion, $_GET['usuario_id'] ?? '');
        
        if(empty($usuario_id)) {
            echo json_encode(['success' => false, 'message' => 'ID de usuario no válido']);
            return;
        }
        
        // Obtener items del carrito del usuario - usando talla en lugar de precio
        $query = "SELECT c.*, p.nombre, p.precio, p.imagen, c.talla
                FROM carrito c 
                INNER JOIN productos p ON c.producto_id = p.id 
                WHERE c.usuario_id = '$usuario_id'";
        
        $result = mysqli_query($conexion, $query);
        
        if(!$result) {
            error_log("Error SQL getUserOrders: " . mysqli_error($conexion));
            echo json_encode(['success' => false, 'message' => 'Error al consultar pedidos: ' . mysqli_error($conexion)]);
            return;
        }
        
        $orders = [];
        
        while($row = mysqli_fetch_assoc($result)) {
            $orders[] = $row;
        }
        
        echo json_encode(['success' => true, 'data' => $orders]);
    }

    function uploadImage($file, $target_dir) {
        // Crear directorio si no existe
        if (!file_exists($target_dir)) {
            mkdir($target_dir, 0777, true);
        }
        
        $allowed_types = ['image/jpeg', 'image/png', 'image/gif', 'image/jpg'];
        $max_size = 5 * 1024 * 1024; // 5MB
        
        // Validar tipo de archivo
        if(!in_array($file['type'], $allowed_types)) {
            error_log("Tipo de archivo no permitido: " . $file['type']);
            return false;
        }
        
        // Validar tamaño
        if($file['size'] > $max_size) {
            error_log("Archivo muy grande: " . $file['size']);
            return false;
        }
        
        // Generar nombre único
        $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
        $filename = uniqid() . '_' . time() . '.' . $extension;
        $target_file = $target_dir . $filename;
        
        // Mover archivo
        if(move_uploaded_file($file['tmp_name'], $target_file)) {
            return $filename;
        }
        
        error_log("Error al mover archivo a: " . $target_file);
        return false;
    }
    ?>