<?php
session_start();
include '../../conexion.php';

// Verificar que sea cliente
if(!isset($_SESSION['correo']) || $_SESSION["nivelusuario"] != 2) {
    header('location: ../../error.php');
    exit;
}

// Verificar que venga de procesar_pedido
if(!isset($_SESSION['pedido_id'])) {
    header('location: ../cliente.php');
    exit;
}

$pedido_id = $_SESSION['pedido_id'];
$usuario_id = $_SESSION['usuario_id'];

// Obtener datos del pedido
$query_pedido = mysqli_query($conexion, "
    SELECT * FROM pedidos 
    WHERE id = $pedido_id AND usuario_id = $usuario_id
");

$pedido = mysqli_fetch_assoc($query_pedido);

if(!$pedido) {
    header('location: ../cliente.php');
    exit;
}

// Obtener items del pedido
$query_items = mysqli_query($conexion, "
    SELECT * FROM pedidos_items 
    WHERE pedido_id = $pedido_id
");

// Limpiar la sesión
unset($_SESSION['pedido_id']);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <?php include('../../google.php'); ?>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pedido Confirmado | RetroVibes</title>
    <link rel="shortcut icon" href="../../img/gato.gif" />
    <link rel="stylesheet" href="../css/style.css">
    <link rel="stylesheet" href="checkout.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <style>
        .confirmation-container {
            max-width: 800px;
            margin: 3rem auto;
            padding: 2rem;
        }
        
        .success-icon {
            text-align: center;
            font-size: 5rem;
            margin-bottom: 1rem;
            animation: bounce 1s;
        }
        
        @keyframes bounce {
            0%, 20%, 50%, 80%, 100% {transform: translateY(0);}
            40% {transform: translateY(-30px);}
            60% {transform: translateY(-15px);}
        }
        
        .confirmation-card {
            background: #fff;
            border: 3px solid #000;
            padding: 3rem;
            text-align: center;
        }
        
        .confirmation-card h1 {
            font-size: 2rem;
            letter-spacing: 3px;
            margin-bottom: 1rem;
        }
        
        .order-number {
            font-size: 1.5rem;
            color: #666;
            margin: 1rem 0 2rem;
        }
        
        .order-details {
            text-align: left;
            border-top: 2px solid #000;
            padding-top: 2rem;
            margin-top: 2rem;
        }
        
        .detail-row {
            display: flex;
            justify-content: space-between;
            margin: 1rem 0;
            padding: 0.5rem 0;
            border-bottom: 1px solid #ddd;
        }
        
        .actions {
            display: flex;
            gap: 1rem;
            margin-top: 2rem;
            justify-content: center;
        }
        
        .btn {
            padding: 1rem 2rem;
            border: 3px solid #000;
            text-decoration: none;
            text-transform: uppercase;
            letter-spacing: 2px;
            font-weight: bold;
            transition: all 0.3s;
            display: inline-block;
        }
        
        .btn-primary {
            background: #000;
            color: #fff;
        }
        
        .btn-secondary {
            background: #fff;
            color: #000;
        }
        
        .btn:hover {
            transform: translateY(-2px);
            box-shadow: 5px 5px 0 #000;
        }
        .success-icon i {
            color: #4CAF50;
        }
    </style>
</head>
<body>
    <header class="header">
        <nav class="nav-checkout">
            <a href="../cliente.php" class="logo">RETRO SHOP</a>
        </nav>
    </header>

    <main class="confirmation-container">
        <div class="confirmation-card">
            <div class="success-icon"><i class="fa-solid fa-circle-check"></i></div>
            
            <h1>¡PEDIDO CONFIRMADO!</h1>
            <p class="order-number">Orden #<?php echo str_pad($pedido['id'], 6, '0', STR_PAD_LEFT); ?></p>
            
            <p style="margin: 2rem 0; font-size: 1.1rem;">
                Gracias por tu compra. Hemos recibido tu pedido y lo procesaremos pronto.
            </p>
            
            <div class="order-details">
                <h3 style="margin-bottom: 1rem;">DETALLES DEL PEDIDO</h3>
                
                <div class="detail-row">
                    <strong>Estado:</strong>
                    <span style="color: #ffc107; text-transform: uppercase;"><?php echo $pedido['estado']; ?></span>
                </div>
                
                <div class="detail-row">
                    <strong>Método de pago:</strong>
                    <span style="text-transform: capitalize;"><?php echo $pedido['metodo_pago']; ?></span>
                </div>
                
                <div class="detail-row">
                    <strong>Dirección de envío:</strong>
                    <span><?php echo htmlspecialchars($pedido['direccion_envio']); ?></span>
                </div>
                
                <div class="detail-row">
                    <strong>Teléfono:</strong>
                    <span><?php echo htmlspecialchars($pedido['telefono']); ?></span>
                </div>
                
                <div class="detail-row" style="font-size: 1.3rem; margin-top: 1.5rem; border-top: 2px solid #000; padding-top: 1rem;">
                    <strong>TOTAL:</strong>
                    <strong>$<?php echo number_format($pedido['total'], 2); ?></strong>
                </div>
                
                <div style="margin-top: 2rem; padding: 1rem; background: #f5f5f5; border: 2px solid #ddd;">
                    <strong>Productos:</strong>
                    <?php while($item = mysqli_fetch_assoc($query_items)): ?>
                    <p style="margin: 0.5rem 0;">
                        • <?php echo htmlspecialchars($item['nombre_producto']); ?>
                        <?php if($item['talla']): ?>
                            (Talla: <?php echo $item['talla']; ?>)
                        <?php endif; ?>
                        x<?php echo $item['cantidad']; ?>
                    </p>
                    <?php endwhile; ?>
                </div>
            </div>
            
            <div class="actions">
                <a href="../perfil.php" class="btn btn-secondary">Ver mis pedidos</a>
                <a href="../cliente.php" class="btn btn-primary">Seguir comprando</a>
            </div>
        </div>
    </main>
</body>
</html>