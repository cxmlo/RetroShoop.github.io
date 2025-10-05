<div class="container">
            <h2 class="section-title">NUESTROS PRODUCTOS</h2>
            <div class="products-grid">
                <?php while ($producto = mysqli_fetch_assoc($resultado)) { ?> 
                <div class="product-card">
                    <div class="product-image" style="cursor: pointer;">
                        <div class="product-badge"><?php echo htmlspecialchars($producto['categoria']); ?></div>
                        <div class="product-img-placeholder">
                            <img src="../img/producto/<?php echo htmlspecialchars($producto['imagen']); ?>" 
                                 alt="<?php echo htmlspecialchars($producto['nombre']); ?>"
                                 style="width: 100%; height: 250px; object-fit: cover;">
                        </div>
                    </div>
                    <div class="product-info">
                        <h3 class="product-title" style="text-transform: uppercase;">
                            <?php echo htmlspecialchars($producto['nombre']); ?>
                        </h3>
                        <p class="product-price">$<?php echo number_format($producto['precio'], 2); ?></p>
                        
                        <button class="btn btn-secondary view-product" 
                                data-id="<?php echo $producto['id']; ?>"
                                data-name="<?php echo htmlspecialchars($producto['nombre']); ?>"
                                data-price="<?php echo $producto['precio']; ?>"
                                data-image="<?php echo htmlspecialchars($producto['imagen']); ?>"
                                data-description="<?php echo htmlspecialchars($producto['descripcion']); ?>">
                            VER DETALLES
                        </button>
                        
                        <button class="btn btn-primary add-to-cart" 
                                data-id="<?php echo $producto['id']; ?>" 
                                data-name="<?php echo htmlspecialchars($producto['nombre']); ?>" 
                                data-price="<?php echo $producto['precio']; ?>" 
                                data-image="<?php echo htmlspecialchars($producto['imagen']); ?>">
                            AGREGAR AL CARRITO
                        </button>
                    </div>
                </div>
                <?php } ?>
            </div>
        </div>