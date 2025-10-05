<?php
session_start();
include '../seguridad.php';
include '../conexion.php';

if(!isset($_SESSION['correo']) || $_SESSION["nivelusuario"] != 1) {
    echo json_encode(['success' => false, 'message' => 'Acceso denegado']);
    exit;
}

header('Content-Type: application/json');

$action = $_POST['action'] ?? $_GET['action'] ?? '';

switch($action) {
    case 'save':
        saveSlide();
        break;
    case 'update':
        updateSlide();
        break;
    case 'delete':
        deleteSlide();
        break;
    default:
        echo json_encode(['success' => false, 'message' => 'Acción no válida']);
}

function saveSlide() {
    global $conexion;
    
    $titulo = mysqli_real_escape_string($conexion, $_POST['titulo']);
    $descripcion = mysqli_real_escape_string($conexion, $_POST['descripcion']);
    $orden = intval($_POST['orden'] ?? 1);
    $activo = isset($_POST['activo']) ? 1 : 0;
    
    if(empty($titulo) || empty($descripcion)) {
        echo json_encode(['success' => false, 'message' => 'Faltan datos requeridos']);
        return;
    }
    
    $imagen = '';
    if(isset($_FILES['imagen']) && $_FILES['imagen']['error'] == 0) {
        $imagen = uploadCarouselImage($_FILES['imagen']);
        if(!$imagen) {
            echo json_encode(['success' => false, 'message' => 'Error al subir la imagen']);
            return;
        }
    } else {
        echo json_encode(['success' => false, 'message' => 'La imagen es requerida']);
        return;
    }
    
    $query = "INSERT INTO carousel_slides (titulo, descripcion, imagen, orden, activo) 
              VALUES ('$titulo', '$descripcion', '$imagen', $orden, $activo)";
    
    if(mysqli_query($conexion, $query)) {
        echo json_encode(['success' => true, 'message' => 'Slide creado exitosamente']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Error: ' . mysqli_error($conexion)]);
    }
}

function updateSlide() {
    global $conexion;
    
    $id = intval($_POST['id']);
    $titulo = mysqli_real_escape_string($conexion, $_POST['titulo']);
    $descripcion = mysqli_real_escape_string($conexion, $_POST['descripcion']);
    $orden = intval($_POST['orden'] ?? 1);
    $activo = isset($_POST['activo']) ? 1 : 0;
    
    if(isset($_FILES['imagen']) && $_FILES['imagen']['error'] == 0) {
        $query_old = "SELECT imagen FROM carousel_slides WHERE id = $id";
        $result = mysqli_query($conexion, $query_old);
        $old_slide = mysqli_fetch_assoc($result);
        
        $nueva_imagen = uploadCarouselImage($_FILES['imagen']);
        if(!$nueva_imagen) {
            echo json_encode(['success' => false, 'message' => 'Error al subir imagen']);
            return;
        }
        
        if($old_slide && $old_slide['imagen']) {
            $old_path = '../img/carousel/' . $old_slide['imagen'];
            if(file_exists($old_path)) unlink($old_path);
        }
        
        $query = "UPDATE carousel_slides SET 
                  titulo = '$titulo',
                  descripcion = '$descripcion',
                  imagen = '$nueva_imagen',
                  orden = $orden,
                  activo = $activo
                  WHERE id = $id";
    } else {
        $query = "UPDATE carousel_slides SET 
                  titulo = '$titulo',
                  descripcion = '$descripcion',
                  orden = $orden,
                  activo = $activo
                  WHERE id = $id";
    }
    
    if(mysqli_query($conexion, $query)) {
        echo json_encode(['success' => true, 'message' => 'Slide actualizado']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Error: ' . mysqli_error($conexion)]);
    }
}

function deleteSlide() {
    global $conexion;
    
    $id = intval($_POST['id']);
    
    $query = "SELECT imagen FROM carousel_slides WHERE id = $id";
    $result = mysqli_query($conexion, $query);
    $slide = mysqli_fetch_assoc($result);
    
    if(mysqli_query($conexion, "DELETE FROM carousel_slides WHERE id = $id")) {
        if($slide && $slide['imagen']) {
            $image_path = '../img/carousel/' . $slide['imagen'];
            if(file_exists($image_path)) unlink($image_path);
        }
        echo json_encode(['success' => true, 'message' => 'Slide eliminado']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Error: ' . mysqli_error($conexion)]);
    }
}

function uploadCarouselImage($file) {
    $target_dir = '../img/carousel/';
    
    if (!file_exists($target_dir)) {
        mkdir($target_dir, 0755, true);
    }
    
    $allowed = ['image/jpeg', 'image/png', 'image/jpg', 'image/webp'];
    if(!in_array($file['type'], $allowed)) return false;
    
    if($file['size'] > 5 * 1024 * 1024) return false;
    
    $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
    $filename = 'slide_' . uniqid() . '_' . time() . '.' . $extension;
    $target_file = $target_dir . $filename;
    
    if(move_uploaded_file($file['tmp_name'], $target_file)) {
        return $filename;
    }
    
    return false;
}
?>