<?php
session_start();
include '../seguridad.php';
include '../conexion.php';

// Verificar que sea administrador
if(!isset($_SESSION['correo']) || $_SESSION["nivelusuario"] != 1) {
    http_response_code(403);
    exit('Acceso denegado');
}

// Verificar que se recibió el archivo
if(!isset($_FILES['image_file']) || $_FILES['image_file']['error'] !== UPLOAD_ERR_OK) {
    http_response_code(400);
    exit(json_encode(['error' => 'No se recibió archivo válido', 'code' => 400]));
}

$api_key = 'Ktri9L8X24kmBtX4G6bTN9oA'; // ⚠️ Coloca tu API key real aquí

$image_file = $_FILES['image_file']['tmp_name'];

// Validar que es una imagen real
$image_info = getimagesize($image_file);
if($image_info === false) {
    http_response_code(400);
    exit(json_encode(['error' => 'El archivo no es una imagen válida', 'code' => 400]));
}

// Detectar mime type correcto
$mime_type = $image_info['mime'];
$allowed_types = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif'];

if(!in_array($mime_type, $allowed_types)) {
    http_response_code(400);
    exit(json_encode(['error' => 'Formato no permitido. Use JPG, PNG o GIF', 'code' => 400]));
}

// Preparar el archivo correctamente para cURL
$cfile = new CURLFile($image_file, $mime_type, basename($_FILES['image_file']['name']));

$ch = curl_init();

curl_setopt($ch, CURLOPT_URL, 'https://api.remove.bg/v1.0/removebg');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
curl_setopt($ch, CURLOPT_POST, 1);
curl_setopt($ch, CURLOPT_POSTFIELDS, array(
    'image_file' => $cfile,
    'size' => 'auto'
));

curl_setopt($ch, CURLOPT_HTTPHEADER, array(
    'X-Api-Key: ' . $api_key
));

// Debug: log request
error_log("Enviando imagen a Remove.bg: " . $mime_type);

$result = curl_exec($ch);
$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);

if (curl_errno($ch)) {
    $error = curl_error($ch);
    curl_close($ch);
    http_response_code(500);
    exit(json_encode(['error' => 'cURL error: ' . $error, 'code' => 500]));
}

curl_close($ch);

// Log de respuesta
error_log("Remove.bg HTTP code: " . $http_code);

if($http_code == 200) {
    header('Content-Type: image/png');
    echo $result;
} else {
    http_response_code($http_code);
    header('Content-Type: application/json');
    
    $error_data = json_decode($result, true);
    $error_message = $error_data['errors'][0]['title'] ?? 'Error desconocido';
    
    // Log del error completo
    error_log("Remove.bg error: " . print_r($error_data, true));
    
    echo json_encode([
        'error' => $error_message,
        'code' => $http_code,
        'details' => $error_data
    ]);
}
?>