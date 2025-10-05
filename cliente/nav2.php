<?php
// Obtener contador del carrito
$cart_count = 0;
if(isset($_SESSION['usuario_id'])) {
    $usuario_id = $_SESSION['usuario_id'];
    $cart_query = mysqli_query($conexion, "SELECT SUM(cantidad) as total FROM carrito WHERE usuario_id = $usuario_id");
    if($cart_data = mysqli_fetch_assoc($cart_query)) {
        $cart_count = $cart_data['total'] ?? 0;
    }
}
?>

<div class="container">
    <div class="header-content-simple">
        <a href="cliente.php" class="logo">RetroVibes</a>
        
        <div class="nav-actions-simple">
            <a href="cliente.php" class="btn-back">
                <i class="fas fa-arrow-left"></i>
                <span>Volver</span>
            </a>
            
            
        </div>
    </div>
</div>

<style>
/* Container */
.container {
    max-width: 1200px;
    margin: 0 auto;
    padding: 0 2rem;
}

.header-content-simple {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 1rem 0;
}

/* Logo */
.logo {
    font-family: 'Courier New', monospace;
    font-size: 2rem;
    font-weight: bold;
    text-transform: uppercase;
    letter-spacing: 3px;
    color: #000;
    text-decoration: none;
    transition: all 0.3s;
}

.logo:hover {
    transform: scale(1.05);
}

/* Acciones de navegación */
.nav-actions-simple {
    display: flex;
    align-items: center;
    gap: 1.5rem;
}

/* Botón Volver */
.btn-back {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    padding: 0.7rem 1.5rem;
    background: transparent;
    border: 2px solid #000;
    color: #000;
    text-decoration: none;
    transition: all 0.3s;
    text-transform: uppercase;
    letter-spacing: 1px;
    font-size: 0.9rem;
    font-family: 'Courier New', monospace;
    font-weight: bold;
}

.btn-back:hover {
    background: #000;
    color: #fff;
    transform: translateX(-3px);
}

.btn-back i {
    font-size: 1rem;
}

/* Carrito */
.cart-btn-simple {
    position: relative;
    display: flex;
    align-items: center;
    justify-content: center;
    width: 50px;
    height: 50px;
    background: transparent;
    border: 2px solid #000;
    color: #000;
    text-decoration: none;
    transition: all 0.3s;
    font-size: 1.3rem;
}

.cart-btn-simple:hover {
    background: #000;
    color: #fff;
    transform: scale(1.1);
}

.cart-count-badge {
    position: absolute;
    top: -8px;
    right: -8px;
    background: #000;
    color: #fff;
    font-size: 0.7rem;
    min-width: 20px;
    height: 20px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-family: 'Courier New', monospace;
    font-weight: bold;
    padding: 0 5px;
}

.cart-btn-simple:hover .cart-count-badge {
    background: #fff;
    color: #000;
}

/* Responsive */
@media (max-width: 768px) {
    .container {
        padding: 0 1rem;
    }

    .header-content-simple {
        padding: 0.8rem 0;
    }

    .logo {
        font-size: 1.5rem;
        letter-spacing: 2px;
    }

    .btn-back span {
        display: none;
    }

    .btn-back {
        padding: 0.7rem;
        width: 45px;
        height: 45px;
        justify-content: center;
    }

    .cart-btn-simple {
        width: 45px;
        height: 45px;
        font-size: 1.2rem;
    }

    .nav-actions-simple {
        gap: 1rem;
    }

    .cart-count-badge {
        top: -6px;
        right: -6px;
        min-width: 18px;
        height: 18px;
        font-size: 0.65rem;
    }
}

@media (max-width: 480px) {
    .logo {
        font-size: 1.3rem;
        letter-spacing: 1px;
    }

    .btn-back,
    .cart-btn-simple {
        width: 40px;
        height: 40px;
    }

    .nav-actions-simple {
        gap: 0.8rem;
    }
}
</style>
