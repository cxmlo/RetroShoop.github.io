<?php
session_start();
include '../../conexion.php';

$usuario_id = $_SESSION['usuario_id'];

// Obtener datos del usuario
$query_user = mysqli_query($conexion, "SELECT * FROM usuario WHERE id = $usuario_id");
$usuario = mysqli_fetch_assoc($query_user);

// Obtener items del carrito
$query_cart = mysqli_query($conexion, "
    SELECT c.id, c.cantidad, c.talla, 
           p.id as producto_id, p.nombre, p.precio, p.imagen
    FROM carrito c
    INNER JOIN productos p ON c.producto_id = p.id
    WHERE c.usuario_id = $usuario_id
    ORDER BY c.fecha_agregado DESC
");

$cart_items = [];
$subtotal = 0;

while($item = mysqli_fetch_assoc($query_cart)) {
    $cart_items[] = $item;
    $subtotal += ($item['precio'] * $item['cantidad']);
}

// Si el carrito está vacío, redirigir
if(empty($cart_items)) {
    header('location: ../cliente.php');
    exit;
}

$envio = 0;
$total = $subtotal + $envio;
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <?php include('../../google.php'); ?>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Finalizar Compra | RetroVibes</title>
    <link rel="shortcut icon" href="../../img/gato.gif" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="stylesheet" href="../css/style.css">
    <link rel="stylesheet" href="checkout.css">
</head>
<body>
    <header class="header">
        <nav class="nav-checkout">
            <a href="../cliente.php" class="logo">RETRO SHOP</a>
            <a href="../cliente.php" class="btn-back">
                <i class="fas fa-arrow-left"></i> Volver a la tienda
            </a>
        </nav>
    </header>

    <main class="checkout-container">
        <div class="checkout-content">
            <!-- Formulario de envío -->
            <div class="checkout-form-section">
                <h1><i class="fas fa-shopping-cart"></i> FINALIZAR COMPRA</h1>
                
                <form id="checkoutForm" method="POST" action="procesar_pedido.php">
                    <div class="form-section">
                        <h2><i class="fas fa-shipping-fast"></i> INFORMACIÓN DE ENVÍO</h2>
                        
                        <div class="form-group">
                            <label><i class="fas fa-user"></i> Nombre Completo *</label>
                            <input type="text" 
                                   name="nombre" 
                                   value="<?php echo htmlspecialchars($usuario['nombre']); ?>" 
                                   required>
                        </div>

                        <div class="form-group">
                            <label><i class="fas fa-phone"></i> Teléfono *</label>
                            <input type="tel" 
                                   name="telefono" 
                                   placeholder="Ej: 300 123 4567" 
                                   required>
                        </div>

                        <div class="form-group">
                            <label><i class="fas fa-map-marker-alt"></i> Dirección Completa *</label>
                            <textarea name="direccion" 
                                      rows="3" 
                                      placeholder="Calle, número, barrio, ciudad..." 
                                      required></textarea>
                        </div>

                        <div class="form-group">
                            <label><i class="fas fa-sticky-note"></i> Notas adicionales (opcional)</label>
                            <textarea name="notas" 
                                      rows="2" 
                                      placeholder="Referencias de tu dirección, indicaciones especiales..."></textarea>
                        </div>
                    </div>

                    <div class="form-section">
                        <h2><i class="fas fa-credit-card"></i> MÉTODO DE PAGO</h2>
                        
                        <div class="payment-methods">
                            <label class="payment-option">
                                <input type="radio" name="metodo_pago" value="efectivo" checked>
                                <div class="payment-card">
                                    <i class="fas fa-money-bill-wave payment-icon"></i>
                                    <span>Efectivo contra entrega</span>
                                </div>
                            </label>

                            <label class="payment-option">
                                <input type="radio" name="metodo_pago" value="transferencia">
                                <div class="payment-card">
                                    <i class="fas fa-exchange-alt payment-icon"></i>
                                    <span>Transferencia bancaria</span>
                                </div>
                            </label>

                            <label class="payment-option">
                                <input type="radio" name="metodo_pago" value="tarjeta">
                                <div class="payment-card">
                                    <i class="fas fa-credit-card payment-icon"></i>
                                    <span>Tarjeta débito/crédito</span>
                                </div>
                            </label>
                        </div>
                    </div>

                    <button type="submit" class="btn btn-checkout">
                        <i class="fas fa-check-circle"></i> CONFIRMAR PEDIDO
                    </button>
                </form>
            </div>

            <!-- Resumen del pedido -->
            <div class="checkout-summary">
                <h2><i class="fas fa-receipt"></i> RESUMEN DEL PEDIDO</h2>
                
                <div class="summary-items">
                    <?php foreach($cart_items as $item): ?>
                    <div class="summary-item">
                        <img src="../../img/producto/<?php echo htmlspecialchars($item['imagen']); ?>" 
                             alt="<?php echo htmlspecialchars($item['nombre']); ?>">
                        <div class="item-details">
                            <h4><?php echo htmlspecialchars($item['nombre']); ?></h4>
                            <?php if($item['talla']): ?>
                            <p class="item-size"><i class="fas fa-ruler"></i> Talla: <?php echo htmlspecialchars($item['talla']); ?></p>
                            <?php endif; ?>
                            <p class="item-quantity"><i class="fas fa-hashtag"></i> Cantidad: <?php echo $item['cantidad']; ?></p>
                        </div>
                        <div class="item-price">
                            <i class="fas fa-tag"></i> $<?php echo number_format($item['precio'] * $item['cantidad'], 2); ?>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>

                <div class="summary-totals">
                    <div class="total-row">
                        <span>Subtotal:</span>
                        <span>$<?php echo number_format($subtotal, 2); ?></span>
                    </div>
                    <div class="total-row">
                        <span>Envío:</span>
                        <span><?php echo $envio == 0 ? 'GRATIS' : '$' . number_format($envio, 2); ?></span>
                    </div>
                    <div class="total-row total-final">
                        <span><i class="fas fa-dollar-sign"></i> TOTAL:</span>
                        <span>$<?php echo number_format($total, 2); ?></span>
                    </div>
                </div>

                <div class="security-badges">
                    <p><i class="fas fa-lock"></i> Compra 100% segura</p>
                    <p><i class="fas fa-shipping-fast"></i> Envío rápido</p>
                    <p><i class="fas fa-undo-alt"></i> Devoluciones gratis</p>
                </div>
            </div>
        </div>
    </main>

    <div id="notificationContainer"></div>

    <script src="checkout.js"></script>
</body>
</html>