<?php
include 'seguridad.php';
include 'conexion.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] != 'POST') {
    echo json_encode(['success' => false, 'message' => 'Método no permitido']);
    exit;
}

$ip = $_SERVER['REMOTE_ADDR'];
if(!verificar_rate_limit("registro_$ip", 3, 3600)) {
    log_seguridad("Rate limit excedido en registro desde IP: $ip", 'WARNING');
    echo json_encode(['success' => false, 'message' => 'Demasiados registros. Espera 1 hora.']);
    exit;
}

$nombre = sanitizar_input($_POST['nombre'] ?? '');
$correo = sanitizar_input($_POST['correo'] ?? '');
$password = $_POST['password'] ?? '';
$confirm_password = $_POST['confirm_password'] ?? '';

if (empty($nombre) || empty($correo) || empty($password)) {
    echo json_encode(['success' => false, 'message' => 'Completa todos los campos']);
    exit;
}

if (!validar_email($correo)) {
    log_seguridad("Registro con email inválido: $correo", 'WARNING');
    echo json_encode(['success' => false, 'message' => 'Email inválido']);
    exit;
}

$validacion_password = validar_password_fuerte($password);
if (!$validacion_password['valid']) {
    echo json_encode(['success' => false, 'message' => $validacion_password['message']]);
    exit;
}

if ($password !== $confirm_password) {
    echo json_encode(['success' => false, 'message' => 'Las contraseñas no coinciden']);
    exit;
}

try {
    $stmt = $pdo->prepare("SELECT id FROM usuario WHERE correo = ?");
    $stmt->execute([$correo]);
    
    if ($stmt->fetch()) {
        echo json_encode(['success' => false, 'message' => 'El correo ya está registrado']);
        exit;
    }
    
    $password_hash = password_hash($password, PASSWORD_DEFAULT);
    $foto_default = 'default.jpg';
    $nivelusuario = 2;
    
    $stmt = $pdo->prepare("INSERT INTO usuario (nombre, correo, PassUsuario, foto, nivelusuario) VALUES (?, ?, ?, ?, ?)");
    
    if ($stmt->execute([$nombre, $correo, $password_hash, $foto_default, $nivelusuario])) {
        log_seguridad("Nuevo registro exitoso: $correo", 'INFO');
        echo json_encode(['success' => true, 'message' => 'Registro exitoso. Redirigiendo al login...']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Error al registrar']);
    }
    
} catch(PDOException $e) {
    error_log("Error en registro: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Error del servidor']);
}
?>