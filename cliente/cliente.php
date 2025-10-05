<?php
session_start();
include '../conexion.php';

if(isset($_SESSION['correo'])) {
    if ($_SESSION["nivelusuario"] == 2) {
        $user = $_SESSION['correo'];
        $codigo = $_SESSION["nivelusuario"];

        // IMPORTANTE: Guardar el ID del usuario en la sesión
        $consulta = mysqli_query($conexion, "SELECT id, foto FROM usuario WHERE nivelusuario = $codigo AND correo = '$user'");                  
        if($filas = mysqli_fetch_array($consulta)){
            $foto = $filas['foto'];
            $_SESSION['usuario_id'] = $filas['id']; // LÍNEA AGREGADA
        }
    }
}
$slides_query = mysqli_query($conexion, "SELECT * FROM carousel_slides WHERE activo = 1 ORDER BY orden ASC");


$resultado = mysqli_query($conexion, "SELECT * FROM productos ORDER BY RAND() LIMIT 3");
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <?php include('../google.php'); ?>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>RetroVibes</title>
    <link rel="shortcut icon" href="../img/gato.gif" />
    
    <!-- Font Awesome CDN -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    
    <link rel="stylesheet" href="css/style.css">
    <style>
        /* Contenedor del botón Mostrar Más */
        .show-more-container {
            text-align: center;
            padding: 3rem 0;
            margin-top: 2rem;
        }

        .btn-show-more {
            display: inline-block;
            padding: 1rem 3rem;
            background: transparent;
            border: 3px solid #000;
            color: #000;
            text-decoration: none;
            text-transform: uppercase;
            letter-spacing: 2px;
            font-size: 1.1rem;
            font-family: 'Courier New', monospace;
            font-weight: bold;
            transition: all 0.3s;
            position: relative;
            overflow: hidden;
        }

        .btn-show-more::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: #000;
            transition: left 0.4s ease;
            z-index: -1;
        }

        .btn-show-more:hover::before {
            left: 0;
        }

        .btn-show-more:hover {
            color: #fff;
            transform: translateY(-3px);
            box-shadow: 5px 5px 0 rgba(0, 0, 0, 0.3);
        }

        .btn-show-more i {
            margin-left: 0.5rem;
            transition: transform 0.3s;
        }

        .btn-show-more:hover i {
            transform: translateX(5px);
        }

        /* Responsive */
        @media (max-width: 768px) {
            .show-more-container {
                padding: 2rem 0;
                margin-top: 1rem;
            }

            .btn-show-more {
                padding: 0.8rem 2rem;
                font-size: 1rem;
                letter-spacing: 1px;
            }
        }
    </style>
</head>
<body>
    <!-- Header -->
    <header class="header">
        <?php include('nav.php') ?>
    </header>

    <!-- Hero Section con Carousel -->
    <section class="hero" id="home">
        <div class="carousel">
            <div class="carousel-container" id="carouselContainer">
                <?php 
                $first = true;
                while($slide = mysqli_fetch_assoc($slides_query)): 
                ?>
                <div class="carousel-slide <?php echo $first ? 'active' : ''; ?>" 
                     style="background-image: linear-gradient(rgba(0,0,0,0.5), rgba(0,0,0,0.5)), url('../img/carousel/<?php echo htmlspecialchars($slide['imagen']); ?>'); background-size: cover; background-position: center;">
                    <div class="carousel-content">
                        <h2><?php echo htmlspecialchars($slide['titulo']); ?></h2>
                        <p><?php echo htmlspecialchars($slide['descripcion']); ?></p>
                    </div>
                </div>
                <?php 
                $first = false;
                endwhile; 
                ?>
            </div>
            <button class="carousel-btn prev" id="prevBtn">
                <i class="fas fa-chevron-left"></i>
            </button>
            <button class="carousel-btn next" id="nextBtn">
                <i class="fas fa-chevron-right"></i>
            </button>
            <div class="carousel-dots" id="carouselDots"></div>
        </div>
    </section>

    <!-- Products Section -->
    <section class="products" id="products">
        <?php include('product.php') ?>
        
        <!-- Botón Mostrar Más -->
        <div class="show-more-container">
            <a href="productos.php" class="btn-show-more">
                Ver Todos los Productos <i class="fas fa-arrow-right"></i>
            </a>
        </div>
    </section>

    <!-- Footer -->
    <footer class="footer">
        <?php include('footer.php') ?>
    </footer>

    <!-- Product Details Modal -->
    <div class="modal" id="productModal">
        <div class="modal-content product-modal-layout">
            <button class="close-btn-corner" id="closeProductModal">
                <i class="fas fa-times"></i>
            </button>
            
            <div class="product-modal-left">
                <div class="product-modal-image-container">
                    <div class="product-modal-image" id="productModalImg"></div>
                </div>
            </div>
            
            <div class="product-modal-right">
                <h2 class="product-modal-name" id="productModalName" style="text-transform: uppercase;"></h2>
                <p class="product-modal-price"><span id="productModalPrice">0.00</span></p>
                <p class="product-modal-description" id="productModalDescription"></p>
                
                <div class="product-modal-options">
                    <div class="option-group">
                        <label class="option-label">
                            <i class="fas fa-ruler"></i> TALLA:
                        </label>
                        <div class="size-options" id="sizeOptions">
                            <button class="size-btn" data-size="S">S</button>
                            <button class="size-btn" data-size="M">M</button>
                            <button class="size-btn" data-size="L">L</button>
                            <button class="size-btn" data-size="XL">XL</button>
                        </div>
                    </div>
                    
                    <div class="option-group">
                        <label class="option-label">
                            <i class="fas fa-hashtag"></i> CANTIDAD:
                        </label>
                        <div class="quantity-selector">
                            <button class="qty-btn" id="decreaseQty">
                                <i class="fas fa-minus"></i>
                            </button>
                            <input type="number" class="qty-input" id="modalQuantity" value="1" min="1" readonly>
                            <button class="qty-btn" id="increaseQty">
                                <i class="fas fa-plus"></i>
                            </button>
                        </div>
                    </div>
                </div>
                
                <button class="btn btn-primary btn-add-modal" id="addToCartFromModal">
                    <i class="fas fa-shopping-cart"></i> AÑADIR AL CARRITO
                </button>
            </div>
        </div>
    </div>

    <!-- Cart Modal -->
    <div class="modal" id="cartModal">
        <div class="modal-content">
            <div class="modal-header">
                <h2><i class="fas fa-shopping-bag"></i> TU CARRITO</h2>
                <button class="close-btn" id="closeCart">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <div class="modal-body" id="cartItems">
                <p class="empty-cart">
                    <i class="fas fa-cart-arrow-down" style="font-size: 3rem; display: block; margin-bottom: 1rem;"></i>
                    Tu carrito está vacío
                </p>
            </div>
            <div class="modal-footer">
                <div class="cart-total">
                    <strong><i class="fas fa-dollar-sign"></i> TOTAL:</strong>
                    <span id="cartTotal">$0.00</span>
                </div>
                <button class="btn btn-primary" id="checkoutBtn">
                    <i class="fas fa-credit-card"></i> FINALIZAR COMPRA
                </button>
            </div>
        </div>
    </div>

    <!-- Notification Container -->
    <div id="notificationContainer"></div>

    <script src="js/carousel.js"></script>
    <script src="js/cart.js"></script>
</body>
</html>