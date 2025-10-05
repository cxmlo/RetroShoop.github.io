<?php
include 'seguridad.php';
include 'conexion.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] != 'POST') {
    echo json_encode(['success' => false, 'message' => 'Método no permitido']);
    exit;
}

$ip = $_SERVER['REMOTE_ADDR'];
if(!verificar_rate_limit("login_$ip", 10, 10)) {
    log_seguridad("Rate limit excedido en login desde IP: $ip", 'WARNING');
    echo json_encode(['success' => false, 'message' => 'Demasiados intentos. Espera 10 segundos.']);
    exit;
}

$correo = sanitizar_input($_POST['correo'] ?? '');
$password = $_POST['password'] ?? '';

if (empty($correo) || empty($password)) {
    echo json_encode(['success' => false, 'message' => 'Completa todos los campos']);
    exit;
}

if (!validar_email($correo)) {
    log_seguridad("Login con email inválido: $correo", 'WARNING');
    echo json_encode(['success' => false, 'message' => 'Email inválido']);
    exit;
}

try {
    $stmt = $pdo->prepare("SELECT * FROM usuario WHERE correo = ?");
    $stmt->execute([$correo]);
    $usuario = $stmt->fetch();
    
    if (!$usuario) {
        log_seguridad("Login fallido - usuario no existe: $correo", 'WARNING');
        echo json_encode(['success' => false, 'message' => 'Correo o contraseña incorrectos']);
        exit;
    }
    
    $password_valida = false;
    
    if (password_verify($password, $usuario['PassUsuario'])) {
        $password_valida = true;
    } else if ($password === $usuario['PassUsuario']) {
        $password_valida = true;
        $new_hash = password_hash($password, PASSWORD_DEFAULT);
        $update_stmt = $pdo->prepare("UPDATE usuario SET PassUsuario = ? WHERE id = ?");
        $update_stmt->execute([$new_hash, $usuario['id']]);
        log_seguridad("Contraseña actualizada a hash para: $correo", 'INFO');
    }
    
    if (!$password_valida) {
        log_seguridad("Contraseña incorrecta para: $correo", 'WARNING');
        echo json_encode(['success' => false, 'message' => 'Correo o contraseña incorrectos']);
        exit;
    }
    
    session_regenerate_id(true);
    
    $_SESSION['usuario_id'] = $usuario['id'];
    $_SESSION['correo'] = $usuario['correo'];
    $_SESSION['nombre'] = $usuario['nombre'];
    $_SESSION['nivelusuario'] = $usuario['nivelusuario'];
    $_SESSION['LAST_ACTIVITY'] = time();
    
    log_seguridad("Login exitoso: $correo (Nivel: {$usuario['nivelusuario']})", 'INFO');
    
    $redirect = ($usuario['nivelusuario'] == 1) ? 'admin/admin.php' : 'cliente/cliente.php';
    
    echo json_encode([
        'success' => true, 
        'message' => 'Login exitoso. Redirigiendo...',
        'redirect' => $redirect
    ]);
    
} catch(PDOException $e) {
    error_log("Error en login: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Error del servidor']);
}
?>