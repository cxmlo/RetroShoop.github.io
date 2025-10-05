<?php
session_start();
include '../conexion.php';

if(isset($_SESSION['correo'])) {
    if ($_SESSION["nivelusuario"] == 2) {
        $user = $_SESSION['correo'];
        $codigo = $_SESSION["nivelusuario"];

        $consulta = mysqli_query($conexion, "SELECT id, foto FROM usuario WHERE nivelusuario = $codigo AND correo = '$user'");                  
        if($filas = mysqli_fetch_array($consulta)){
            $foto = $filas['foto'];
            $_SESSION['usuario_id'] = $filas['id'];
        }
    }
}

// ==================== BÚSQUEDA Y FILTROS ====================
$search = $_GET['search'] ?? '';
$categoria = $_GET['categoria'] ?? '';
$precio_min = $_GET['precio_min'] ?? '';
$precio_max = $_GET['precio_max'] ?? '';

// Construir query con filtros
$query = "SELECT * FROM productos WHERE 1=1";

if(!empty($search)) {
    $search_clean = mysqli_real_escape_string($conexion, $search);
    $query .= " AND (nombre LIKE '%$search_clean%' OR descripcion LIKE '%$search_clean%')";
}

if(!empty($categoria)) {
    $categoria_clean = mysqli_real_escape_string($conexion, $categoria);
    $query .= " AND categoria = '$categoria_clean'";
}

if(!empty($precio_min)) {
    $precio_min_clean = floatval($precio_min);
    $query .= " AND precio >= $precio_min_clean";
}

if(!empty($precio_max)) {
    $precio_max_clean = floatval($precio_max);
    $query .= " AND precio <= $precio_max_clean";
}

$query .= " ORDER BY id DESC";
$resultado = mysqli_query($conexion, $query);

// Obtener categorías únicas
$categorias_query = mysqli_query($conexion, "SELECT DISTINCT categoria FROM productos WHERE categoria IS NOT NULL AND categoria != ''");
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <?php include('../google.php'); ?>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Productos - RetroVibes</title>
    <link rel="shortcut icon" href="../img/gato.gif" />
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
</head>
<body>
    <header class="header">
        <?php include('nav.php') ?>
    </header>

    <!-- NUEVA SECCIÓN: Búsqueda y Filtros -->
    <section class="search-filter-section">
        <div class="container">
            <h2 class="section-title">BUSCAR PRODUCTOS</h2>
            
            <form method="GET" action="productos.php" class="search-filter-form">
                <!-- Buscador -->
                <div class="search-box">
                    <input type="text" 
                           name="search" 
                           placeholder="Buscar productos..." 
                           value="<?php echo htmlspecialchars($search); ?>"
                           class="search-input">
                    <button type="submit" class="search-btn">
                        <i class="fa-solid fa-magnifying-glass"></i> BUSCAR
                    </button>
                </div>
                
                <!-- Filtros -->
                <div class="filters-container">
                    <div class="filter-group">
                        <label><i class="fa-solid fa-tag"></i> Categoría</label>
                        <select name="categoria" class="filter-select">
                            <option value="">Todas</option>
                            <?php 
                            mysqli_data_seek($categorias_query, 0);
                            while($cat = mysqli_fetch_assoc($categorias_query)): 
                            ?>
                                <option value="<?php echo htmlspecialchars($cat['categoria']); ?>" 
                                        <?php echo ($categoria == $cat['categoria']) ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars(ucfirst($cat['categoria'])); ?>
                                </option>
                            <?php endwhile; ?>
                        </select>
                    </div>
                    
                    <div class="filter-group">
                        <label><i class="fa-solid fa-dollar-sign"></i> Precio Min</label>
                        <input type="number" 
                               name="precio_min" 
                               placeholder="0" 
                               value="<?php echo htmlspecialchars($precio_min); ?>"
                               class="filter-input"
                               step="0.01">
                    </div>
                    
                    <div class="filter-group">
                        <label><i class="fa-solid fa-dollar-sign"></i> Precio Max</label>
                        <input type="number" 
                               name="precio_max" 
                               placeholder="999999" 
                               value="<?php echo htmlspecialchars($precio_max); ?>"
                               class="filter-input"
                               step="0.01">
                    </div>
                    
                    <button type="submit" class="btn-filter">
                        <i class="fa-solid fa-filter"></i> FILTRAR
                    </button>
                    
                    <a href="productos.php" class="btn-clear">
                        <i class="fa-solid fa-xmark"></i> LIMPIAR
                    </a>
                </div>
            </form>
            
            <!-- Resultados -->
            <div class="search-results-info">
                <?php 
                $total_resultados = mysqli_num_rows($resultado);
                if(!empty($search) || !empty($categoria) || !empty($precio_min) || !empty($precio_max)) {
                    echo "<p>Se encontraron <strong>$total_resultados</strong> productos</p>";
                }
                ?>
            </div>
        </div>
    </section>

    <!-- Products Section -->
    <section class="products" id="products">
        <?php include('product.php') ?>
    </section>

    <footer class="footer">
        <?php include('footer.php') ?>
    </footer>

    <!-- Product Details Modal -->
    <div class="modal" id="productModal">
        <div class="modal-content product-modal-layout">
            <button class="close-btn-corner" id="closeProductModal">×</button>
            
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
                        <label class="option-label">TALLA:</label>
                        <div class="size-options" id="sizeOptions">
                            <button class="size-btn" data-size="S">S</button>
                            <button class="size-btn" data-size="M">M</button>
                            <button class="size-btn" data-size="L">L</button>
                            <button class="size-btn" data-size="XL">XL</button>
                        </div>
                    </div>
                    
                    <div class="option-group">
                        <label class="option-label">CANTIDAD:</label>
                        <div class="quantity-selector">
                            <button class="qty-btn" id="decreaseQty">-</button>
                            <input type="number" class="qty-input" id="modalQuantity" value="1" min="1" readonly>
                            <button class="qty-btn" id="increaseQty">+</button>
                        </div>
                    </div>
                </div>
                
                <button class="btn btn-primary btn-add-modal" id="addToCartFromModal">
                    🛒 AÑADIR AL CARRITO
                </button>
            </div>
        </div>
    </div>

    <!-- Cart Modal -->
    <div class="modal" id="cartModal">
        <div class="modal-content">
            <div class="modal-header">
                <h2>TU CARRITO</h2>
                <button class="close-btn" id="closeCart">&times;</button>
            </div>
            <div class="modal-body" id="cartItems">
                <p class="empty-cart">Tu carrito está vacío</p>
            </div>
            <div class="modal-footer">
                <div class="cart-total">
                    <strong>TOTAL:</strong>
                    <span id="cartTotal">$0.00</span>
                </div>
                <button class="btn btn-primary" id="checkoutBtn">FINALIZAR COMPRA</button>
            </div>
        </div>
    </div>

    <div id="notificationContainer"></div>
    <script src="js/cart.js"></script>
</body>
</html>