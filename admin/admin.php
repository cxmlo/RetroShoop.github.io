<?php
session_start();
include '../conexion.php';

// Verificar que sea administrador
if(!isset($_SESSION['correo']) || $_SESSION["nivelusuario"] != 1) {
    header('location: ../error.php');
    exit;
}

$user = $_SESSION['correo'];
$usuario_id = $_SESSION['usuario_id'] ?? null;

// Obtener estadísticas
$total_productos = mysqli_fetch_assoc(mysqli_query($conexion, "SELECT COUNT(*) as total FROM productos"))['total'];
$total_usuarios = mysqli_fetch_assoc(mysqli_query($conexion, "SELECT COUNT(*) as total FROM usuario WHERE nivelusuario = 2"))['total'];
$total_pedidos_realizados = mysqli_fetch_assoc(mysqli_query($conexion, "SELECT COUNT(*) as total FROM pedidos"))['total'];
$total_carritos = mysqli_fetch_assoc(mysqli_query($conexion, "SELECT COUNT(DISTINCT usuario_id) as total FROM carrito"))['total'];
// Obtener productos
$productos = mysqli_query($conexion, "SELECT * FROM productos ORDER BY id DESC");

// Obtener usuarios
$usuarios = mysqli_query($conexion, "SELECT * FROM usuario WHERE nivelusuario = 2 ORDER BY id DESC");
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Panel Admin - RETRO SHOP</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <!-- Sidebar -->
    <aside class="sidebar" id="sidebar">
        <?php include('nav.php') ?>
    </aside>

    <!-- Burger Menu -->
    <button class="burger-menu" id="burgerMenu">
        <span></span>
        <span></span>
        <span></span>
    </button>

    <!-- Main Content -->
    <main class="main-content">
        <!-- Dashboard Section -->
        <section id="dashboard" class="section active">
            <div class="section-header">
                <h1>DASHBOARD</h1>
                <p>Panel de control y estadísticas</p>
            </div>

            <!-- Stats Grid -->
            <div class="stats-grid">
                <div class="stat-card stat-productos">
                    <div class="stat-icon"></div>
                    <div class="stat-info">
                        <h3><?php echo $total_productos; ?></h3>
                        <p>Productos</p>
                    </div>
                </div>
                <div class="stat-card stat-usuarios">
                    <div class="stat-icon"></div>
                    <div class="stat-info">
                        <h3><?php echo $total_usuarios; ?></h3>
                        <p>Clientes</p>
                    </div>
                </div>
                <div class="stat-card stat-carritos">
                    <div class="stat-icon"></div>
                    <div class="stat-info">
                        <h3><?php echo $total_carritos; ?></h3>
                        <p>Carritos Activos</p>
                    </div>
                </div>
                <div class="stat-card stat-pedidos">
                    <div class="stat-icon"></div>
                    <div class="stat-info">
                        <h3><?php echo $total_pedidos_realizados; ?></h3>
                        <p>Pedidos</p>
                    </div>
                </div>
            </div>

            <!-- Ventas Totales -->
            <?php
            $ventas_query = mysqli_query($conexion, "SELECT SUM(total) as total_ventas FROM pedidos WHERE estado != 'cancelado'");
            $ventas_data = mysqli_fetch_assoc($ventas_query);
            $total_ventas = $ventas_data['total_ventas'] ?? 0;
            
            $ventas_mes_actual = mysqli_fetch_assoc(mysqli_query($conexion, 
                "SELECT SUM(total) as total FROM pedidos 
                WHERE MONTH(fecha_pedido) = MONTH(CURRENT_DATE()) 
                AND YEAR(fecha_pedido) = YEAR(CURRENT_DATE())
                AND estado != 'cancelado'"))['total'] ?? 0;
            ?>
            
            <div class="revenue-cards">
                <div class="revenue-card">
                    <h3>Ventas Totales</h3>
                    <p class="revenue-amount">$<?php echo number_format($total_ventas, 2); ?></p>
                </div>
                <div class="revenue-card">
                    <h3>Ventas Este Mes</h3>
                    <p class="revenue-amount">$<?php echo number_format($ventas_mes_actual, 2); ?></p>
                </div>
            </div>

            <!-- Gráficas -->
            <div class="charts-grid">
                <!-- Gráfica de Ventas -->
                <div class="chart-container">
                    <h3>Ventas por Mes</h3>
                    <canvas id="ventasChart"></canvas>
                </div>
                
                <!-- Gráfica de Productos Más Vendidos -->
                <div class="chart-container">
                    <h3>Productos Más Vendidos</h3>
                    <canvas id="productosChart"></canvas>
                </div>
            </div>

            <!-- Tabla de Últimos Pedidos -->
            <div class="recent-orders">
                <h3>Últimos Pedidos</h3>
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Cliente</th>
                            <th>Fecha</th>
                            <th>Total</th>
                            <th>Estado</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $ultimos_pedidos = mysqli_query($conexion, 
                            "SELECT p.*, u.nombre 
                            FROM pedidos p 
                            JOIN usuario u ON p.usuario_id = u.id 
                            ORDER BY p.fecha_pedido DESC LIMIT 5");
                        
                        while($pedido = mysqli_fetch_assoc($ultimos_pedidos)):
                            $estado_clases = [
                                'pendiente' => 'estado-pendiente',
                                'procesando' => 'estado-procesando',
                                'enviado' => 'estado-enviado',
                                'entregado' => 'estado-entregado',
                                'cancelado' => 'estado-cancelado'
                            ];
                        ?>
                        <tr>
                            <td>#<?php echo str_pad($pedido['id'], 6, '0', STR_PAD_LEFT); ?></td>
                            <td><?php echo htmlspecialchars($pedido['nombre']); ?></td>
                            <td><?php echo date('d/m/Y', strtotime($pedido['fecha_pedido'])); ?></td>
                            <td>$<?php echo number_format($pedido['total'], 2); ?></td>
                            <td>
                                <span class="badge <?php echo $estado_clases[$pedido['estado']]; ?>">
                                    <?php echo strtoupper($pedido['estado']); ?>
                                </span>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </section>

        <!-- Productos Section -->
        <section id="productos" class="section">
            <div class="section-header">
                <h1>GESTIÓN DE PRODUCTOS</h1>
                <button class="btn btn-primary" onclick="openProductModal()">
                    ➕ Nuevo Producto
                </button>
            </div>

            <div class="table-container">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Imagen</th>
                            <th>Nombre</th>
                            <th>Categoría</th>
                            <th>Precio</th>
                            <th>Stock</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while($prod = mysqli_fetch_assoc($productos)) { ?>
                        <tr>
                            <td><?php echo $prod['id']; ?></td>
                            <td>
                                <img src="../img/producto/<?php echo htmlspecialchars($prod['imagen']); ?>" 
                                     alt="<?php echo htmlspecialchars($prod['nombre']); ?>" 
                                     class="table-img"
                                     onerror="this.src='../img/producto/default.jpg'">
                            </td>
                            <td><?php echo htmlspecialchars($prod['nombre']); ?></td>
                            <td><span class="badge"><?php echo htmlspecialchars($prod['categoria']); ?></span></td>
                            <td>$<?php echo number_format($prod['precio'], 2); ?></td>
                            <td><?php echo $prod['stock'] ?? '10'; ?></td>
                            <td class="action-buttons">
                                <button class="btn-icon btn-edit" onclick='editProduct(<?php echo json_encode($prod); ?>)' title="Editar">
                                    ✏️
                                </button>
                                <button class="btn-icon btn-delete" onclick="deleteProduct(<?php echo $prod['id']; ?>)" title="Eliminar">
                                    🗑️
                                </button>
                            </td>
                        </tr>
                        <?php } ?>
                    </tbody>
                </table>
            </div>
        </section>

        <!-- Usuarios Section -->
        <section id="usuarios" class="section">
            <div class="section-header">
                <h1>GESTIÓN DE USUARIOS</h1>
                <button class="btn btn-primary" onclick="openUserModal()">
                    ➕ Nuevo Usuario
                </button>
            </div>

            <div class="table-container">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Foto</th>
                            <th>Nombre</th>
                            <th>Correo</th>
                            <th>Fecha-Registro</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while($usr = mysqli_fetch_assoc($usuarios)) { ?>
                        <tr>
                            <td><?php echo $usr['id']; ?></td>
                            <td>
                                <?php 
                                $foto = $usr['foto'] ?? 'default.jpg';
                                $foto_path = '../img/usuario/' . $foto;
                                ?>
                                <img src="<?php echo $foto_path; ?>" 
                                     alt="<?php echo htmlspecialchars($usr['nombre']); ?>" 
                                     class="table-img user-img"
                                     onerror="this.src='../img/usuario/default.jpg'">
                            </td>
                            <td><?php echo htmlspecialchars($usr['nombre'] ?? 'N/A'); ?></td>
                            <td><?php echo htmlspecialchars($usr['correo']); ?></td>
                            <td><?php echo htmlspecialchars($usr['FechaRegistro']); ?></td>
                            <td class="action-buttons">
                                <button class="btn-icon btn-view" onclick="viewUserOrders(<?php echo $usr['id']; ?>)" title="Ver pedidos">
                                    👁️
                                </button>
                                <button class="btn-icon btn-edit" onclick='editUser(<?php echo json_encode($usr); ?>)' title="Editar">
                                    ✏️
                                </button>
                                <button class="btn-icon btn-delete" onclick="deleteUser(<?php echo $usr['id']; ?>)" title="Eliminar">
                                    🗑️
                                </button>
                            </td>
                        </tr>
                        <?php } ?>
                    </tbody>
                </table>
            </div>
        </section>
        <!-- Carousel Section -->
        <section id="carousel" class="section">
            <div class="section-header">
                <h1>GESTIÓN DE CAROUSEL</h1>
                <button class="btn btn-primary" onclick="openCarouselModal()">
                    ➕ Nuevo Slide
                </button>
            </div>

            <div class="table-container">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Orden</th>
                            <th>Imagen</th>
                            <th>Título</th>
                            <th>Descripción</th>
                            <th>Estado</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php 
                        $slides = mysqli_query($conexion, "SELECT * FROM carousel_slides ORDER BY orden ASC");
                        while($slide = mysqli_fetch_assoc($slides)) { 
                        ?>
                        <tr>
                            <td><?php echo $slide['orden']; ?></td>
                            <td>
                                <img src="../img/carousel/<?php echo htmlspecialchars($slide['imagen']); ?>" 
                                    alt="<?php echo htmlspecialchars($slide['titulo']); ?>" 
                                    class="table-img"
                                    onerror="this.src='../img/carousel/default.jpg'">
                            </td>
                            <td><?php echo htmlspecialchars($slide['titulo']); ?></td>
                            <td><?php echo htmlspecialchars(substr($slide['descripcion'], 0, 50)) . '...'; ?></td>
                            <td>
                                <span class="badge <?php echo $slide['activo'] ? 'badge-active' : 'badge-inactive'; ?>">
                                    <?php echo $slide['activo'] ? 'Activo' : 'Inactivo'; ?>
                                </span>
                            </td>
                            <td class="action-buttons">
                                <button class="btn-icon btn-edit" onclick='editSlide(<?php echo json_encode($slide); ?>)' title="Editar">
                                    ✏️
                                </button>
                                <button class="btn-icon btn-delete" onclick="deleteSlide(<?php echo $slide['id']; ?>)" title="Eliminar">
                                    🗑️
                                </button>
                            </td>
                        </tr>
                        <?php } ?>
                    </tbody>
                </table>
            </div>
        </section>
                <!-- Carritos Activos Section -->
        <section id="carritos" class="section">
            <div class="section-header">
                <h1>CARRITOS ACTIVOS</h1>
                <p>Productos en carritos de usuarios</p>
            </div>

            <div id="carritosContainer" class="pedidos-grid">
                <!-- Se cargará dinámicamente -->
            </div>
        </section>

        <!-- Pedidos Section -->
        <section id="pedidos" class="section">
            <div class="section-header">
                <h1>PEDIDOS ACTIVOS</h1>
            </div>

            <div id="pedidosContainer" class="pedidos-grid">
                <!-- Se cargará dinámicamente -->
            </div>
        </section>
    </main>

    <!-- Modal Producto -->
    <div class="modal" id="productModal">
        <div class="modal-content">
            <div class="modal-header">
                <h2 id="modalTitle">NUEVO PRODUCTO</h2>
                <button class="close-btn" onclick="closeProductModal()">&times;</button>
            </div>
            <form id="productForm" enctype="multipart/form-data">
                <input type="hidden" id="productId" name="id">
                <input type="hidden" id="productAction" name="action" value="save">
                <div class="form-grid">
                    <div class="form-group">
                        <label>Nombre del Producto *</label>
                        <input type="text" name="nombre" id="productName" required>
                    </div>
                    <div class="form-group">
                        <label>Categoría *</label>
                        <input type="text" name="categoria" id="productCategory" required>
                    </div>
                    <div class="form-group">
                        <label>Precio *</label>
                        <input type="number" name="precio" id="productPrice" step="0.01" required>
                    </div>
                    <div class="form-group">
                        <label>Stock</label>
                        <input type="number" name="stock" id="productStock" value="10">
                    </div>
                    <div class="form-group full-width">
                        <label>Descripción</label>
                        <textarea name="descripcion" id="productDescription" rows="3"></textarea>
                    </div>
                    <div class="form-group full-width">
                        <label>Imagen del Producto <span id="currentImage"></span></label>
                        <input type="file" name="imagen" id="productImage" accept="image/*">
                        <small>Formatos permitidos: JPG, PNG, GIF</small>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" onclick="closeProductModal()">Cancelar</button>
                    <button type="submit" class="btn btn-primary">Guardar Producto</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Modal Usuario -->
    <div class="modal" id="userModal">
        <div class="modal-content">
            <div class="modal-header">
                <h2 id="userModalTitle">NUEVO USUARIO</h2>
                <button class="close-btn" onclick="closeUserModal()">&times;</button>
            </div>
            <form id="userForm" enctype="multipart/form-data">
                <input type="hidden" id="userId" name="id">
                <input type="hidden" id="userAction" name="action" value="save">
                <div class="form-grid">
                    <div class="form-group">
                        <label>Nombre *</label>
                        <input type="text" name="nombre" id="userName" required>
                    </div>
                    <div class="form-group">
                        <label>Correo *</label>
                        <input type="email" name="correo" id="userEmail" required>
                    </div>
                    <div class="form-group full-width">
                        <label>Contraseña <span id="passwordLabel">*</span></label>
                        <input type="password" name="password" id="userPassword" required>
                        <small id="passwordHint">Mínimo 6 caracteres</small>
                    </div>
                    <div class="form-group full-width">
                        <label>Foto de Perfil</label>
                        <input type="file" name="foto" id="userPhoto" accept="image/*">
                        <small>Formatos permitidos: JPG, PNG, GIF</small>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" onclick="closeUserModal()">Cancelar</button>
                    <button type="submit" class="btn btn-primary">Guardar Usuario</button>
                </div>
            </form>
        </div>
    </div>
    <!-- Modal Carousel -->
    <div class="modal" id="carouselModal">
        <div class="modal-content">
            <div class="modal-header">
                <h2 id="carouselModalTitle">NUEVO SLIDE</h2>
                <button class="close-btn" onclick="closeCarouselModal()">&times;</button>
            </div>
            <form id="carouselForm" enctype="multipart/form-data">
                <input type="hidden" id="slideId" name="id">
                <input type="hidden" id="slideAction" name="action" value="save">
                
                <div class="form-grid">
                    <div class="form-group">
                        <label>Título *</label>
                        <input type="text" name="titulo" id="slideTitulo" required>
                    </div>
                    
                    <div class="form-group">
                        <label>Orden</label>
                        <input type="number" name="orden" id="slideOrden" value="1" min="1">
                    </div>
                    
                    <div class="form-group full-width">
                        <label>Descripción *</label>
                        <textarea name="descripcion" id="slideDescripcion" rows="4" required></textarea>
                    </div>
                    
                    <div class="form-group full-width">
                        <label>Imagen <span id="currentSlideImage"></span></label>
                        <input type="file" name="imagen" id="slideImagen" accept="image/*">
                        <small>Tamaño recomendado: 1920x500px</small>
                    </div>
                    
                    <div class="form-group">
                        <label style="display: flex; align-items: center; gap: 0.5rem;">
                            <input type="checkbox" name="activo" id="slideActivo" checked>
                            Slide Activo
                        </label>
                    </div>
                </div>
                
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" onclick="closeCarouselModal()">Cancelar</button>
                    <button type="submit" class="btn btn-primary">Guardar Slide</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Notification Container -->
    <div id="notificationContainer"></div>
    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
    <script src="js/dashboard.js"></script>
    <script src="js/admin.js"></script>
    <!-- Al final del body, después de admin.js -->
    <script src="js/image-processor.js"></script>
     
</body>
</html>