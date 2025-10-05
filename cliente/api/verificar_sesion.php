<?php
session_start();
header('Content-Type: application/json');

$loggedin = isset($_SESSION['correo']) && $_SESSION['nivelusuario'] == 2;

echo json_encode([
    'loggedin' => $loggedin
]);
?>