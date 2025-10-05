<?php
/**
 * Archivo de Seguridad Consolidado
 */

// Configurar sesión ANTES de iniciarla
if (session_status() === PHP_SESSION_NONE) {
    ini_set('session.cookie_httponly', 1);
    ini_set('session.cookie_samesite', 'Strict');
    session_start();
}

// Regenerar ID periódicamente
if(!isset($_SESSION['regenerated'])) {
    session_regenerate_id(true);
    $_SESSION['regenerated'] = time();
} elseif(time() - $_SESSION['regenerated'] > 300) {
    session_regenerate_id(true);
    $_SESSION['regenerated'] = time();
}

// Timeout 30 minutos
if(isset($_SESSION['LAST_ACTIVITY']) && (time() - $_SESSION['LAST_ACTIVITY'] > 1800)) {
    session_unset();
    session_destroy();
    header('location: /login.php');
    exit;
}
$_SESSION['LAST_ACTIVITY'] = time();

// Funciones de escape
function escape($string) {
    if($string === null) return '';
    return htmlspecialchars($string, ENT_QUOTES, 'UTF-8');
}

// Rate limiting
function verificar_rate_limit($identificador, $max_intentos = 5, $tiempo_bloqueo = 900) {
    $archivo = sys_get_temp_dir() . '/rate_limit_' . md5($identificador) . '.json';
    $intentos = file_exists($archivo) ? json_decode(file_get_contents($archivo), true) : [];
    $intentos = array_filter($intentos, function($tiempo) use ($tiempo_bloqueo) {
        return time() - $tiempo < $tiempo_bloqueo;
    });
    if(count($intentos) >= $max_intentos) return false;
    $intentos[] = time();
    file_put_contents($archivo, json_encode($intentos));
    return true;
}

// Validaciones
function validar_email($email) {
    $email = filter_var($email, FILTER_SANITIZE_EMAIL);
    return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
}

function validar_password_fuerte($password) {
    if(strlen($password) < 6) {
        return ['valid' => false, 'message' => 'Mínimo 6 caracteres'];
    }
    return ['valid' => true];
}

function sanitizar_input($data) {
    if(is_array($data)) return array_map('sanitizar_input', $data);
    return trim(stripslashes($data));
}

// Log de seguridad
function log_seguridad($mensaje, $nivel = 'INFO') {
    $log_file = sys_get_temp_dir() . '/seguridad.log';
    $timestamp = date('Y-m-d H:i:s');
    $ip = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
    $user = $_SESSION['correo'] ?? 'guest';
    $log_entry = "[$timestamp] [$nivel] [IP: $ip] [User: $user] $mensaje\n";
    file_put_contents($log_file, $log_entry, FILE_APPEND);
}

// Headers de seguridad
header("X-Content-Type-Options: nosniff");
header("X-Frame-Options: SAMEORIGIN");
header("X-XSS-Protection: 1; mode=block");
?>