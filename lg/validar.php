<?php 
session_start(); 

include '../conexion.php';

if(isset($_POST['usuario'])){
    $usuario = htmlentities(mysqli_real_escape_string($conexion, $_POST['usuario']));
    $pw = htmlentities(mysqli_real_escape_string($conexion, $_POST['password']));
    
    $log = mysqli_query($conexion, "SELECT * FROM usuario WHERE correo='$usuario' AND PassUsuario='$pw'");
    
    if(mysqli_num_rows($log) > 0){
        $row = mysqli_fetch_array($log);
        
        // Guardar datos en la sesión
        $_SESSION["usuario_id"] = $row['id'];  // ← LÍNEA NUEVA - Guarda el ID del usuario
        $_SESSION["correo"] = $row['correo'];
        $_SESSION["nivelusuario"] = $row['nivelusuario'];
        
        // Opcional: Guardar más información del usuario
        $_SESSION["nombre"] = $row['nombre'] ?? '';
        
        // Redireccionar según el nivel de usuario
        if($_SESSION["nivelusuario"] == 1){
            echo '<script> window.location="../admin/admin.php"; </script>';
        }
        elseif($_SESSION["nivelusuario"] == 2) {
            echo '<script> window.location="../cliente/cliente.php"; </script>';
        }
    }
    else{
        header('location:../error.php');
    }
}
?>