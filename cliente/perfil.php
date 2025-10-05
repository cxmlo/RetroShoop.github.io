<?php
session_start();
include '../conexion.php';

$user_foto = 'default.jpg';
$user_nombre = 'Usuario';

if(isset($_SESSION['correo']) && isset($_SESSION['usuario_id'])) {
    $usuario_id = $_SESSION['usuario_id'];
    $query = mysqli_query($conexion, "SELECT nombre, foto FROM usuario WHERE id = $usuario_id");
    if($datos = mysqli_fetch_assoc($query)) {
        $user_foto = $datos['foto'] ?? 'default.jpg';
        $user_nombre = $datos['nombre'] ?? 'Usuario';
    }
}

// Verificar que sea cliente
if(!isset($_SESSION['correo']) || $_SESSION["nivelusuario"] != 2) {
    header('location: ../error.php');
    exit;
}

$usuario_id = $_SESSION['usuario_id'];

// Obtener datos del usuario
$query = mysqli_query($conexion, "SELECT * FROM usuario WHERE id = $usuario_id");
$usuario = mysqli_fetch_assoc($query);

// Obtener pedidos del usuario
$pedidos_query = mysqli_query($conexion, "
    SELECT * FROM pedidos 
    WHERE usuario_id = $usuario_id 
    ORDER BY fecha_pedido DESC
");
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <?php include('../google.php'); ?>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Perfil - RetroVibes</title>
    <link rel="shortcut icon" href="../img/gato.gif" />
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="css/perfil.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <!-- Cropper.js CSS -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.6.1/cropper.min.css">
    <style>
        /* Estilos para el botón de perfil */
        .nav-actions {
            display: flex;
            align-items: center;
            gap: 1rem;
        }

        /* Dropdown de usuario */
        .user-dropdown {
            position: relative;
        }

        .user-profile-btn {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.5rem 1rem;
            background: transparent;
            border: 2px solid #000;
            color: #000;
            transition: all 0.3s;
            text-transform: uppercase;
            letter-spacing: 1px;
            font-size: 0.9rem;
            font-family: 'Courier New', monospace;
            cursor: pointer;
        }

        .user-profile-btn:hover {
            background: #000;
            color: #fff;
        }

        .user-avatar {
            width: 35px;
            height: 35px;
            border-radius: 50%;
            object-fit: cover;
            border: 2px solid #000;
        }

        .user-name {
            font-weight: bold;
        }

        .dropdown-arrow {
            font-size: 0.7rem;
            transition: transform 0.3s;
        }

        .user-dropdown:hover .dropdown-arrow {
            transform: rotate(180deg);
        }

        /* Menu dropdown */
        .user-dropdown-menu {
            position: absolute;
            top: calc(100% + 10px);
            right: 0;
            background: #fff;
            border: 3px solid #000;
            min-width: 200px;
            opacity: 0;
            visibility: hidden;
            transform: translateY(-10px);
            transition: all 0.3s;
            z-index: 1000;
            box-shadow: 5px 5px 0 rgba(0, 0, 0, 0.3);
        }

        .user-dropdown:hover .user-dropdown-menu {
            opacity: 1;
            visibility: visible;
            transform: translateY(0);
        }

        .dropdown-item {
            display: flex;
            align-items: center;
            gap: 0.8rem;
            padding: 1rem 1.5rem;
            text-decoration: none;
            color: #000;
            font-family: 'Courier New', monospace;
            font-weight: bold;
            text-transform: uppercase;
            letter-spacing: 1px;
            font-size: 0.85rem;
            border-bottom: 2px solid #eee;
            transition: all 0.3s;
        }

        .dropdown-item:last-child {
            border-bottom: none;
        }

        .dropdown-item:hover {
            background: #000;
            color: #fff;
        }

        .dropdown-item.logout:hover {
            background: #ff4444;
        }

        .dropdown-item span {
            font-size: 1.2rem;
        }

        /* Separador para móvil */
        .mobile-only {
            display: none;
        }

        .desktop-only {
            display: block;
        }

        /* Estilos responsive */
        @media (max-width: 768px) {
            .user-name {
                display: none;
            }
            
            .dropdown-arrow {
                display: none;
            }
            
            .user-profile-btn {
                padding: 0.5rem;
                border: none;
            }
            
            .user-dropdown-menu {
                display: none;
            }
            
            .desktop-only {
                display: none;
            }
            
            .mobile-only {
                display: block;
                border-top: 1px solid #eee;
            }
            
            .mobile-only a {
                padding: 1rem;
                display: flex;
                align-items: center;
                gap: 0.5rem;
            }
            
            .logout-link {
                color: #ff4444 !important;
                font-weight: bold;
            }
        }
    </style>
</head>
<body>
    <!-- Header -->
    <header class="header">
        <?php include('nav2.php') ?>
    </header>

    <!-- Perfil Container -->
    <main class="perfil-container">
        <div class="perfil-sidebar">
            <div class="perfil-avatar-section">
                <img src="../img/usuario/<?php echo htmlspecialchars($usuario['foto'] ?? 'default.jpg'); ?>" 
                     alt="Avatar" 
                     class="perfil-avatar"
                     id="avatarPreview"
                     onerror="this.src='../img/usuario/default.jpg'">
                <button class="btn-change-photo" onclick="document.getElementById('photoInput').click()">
                    <i class="fas fa-camera"></i> Cambiar Foto
                </button>
                <input type="file" id="photoInput" accept="image/*" style="display: none;">
            </div>

            <nav class="perfil-nav">
                <button class="perfil-nav-btn active" data-section="datos">
                    <i class="fas fa-user"></i> Mis Datos
                </button>
                <button class="perfil-nav-btn" data-section="pedidos">
                    <i class="fas fa-shopping-bag"></i> Mis Pedidos
                </button>
                <button class="perfil-nav-btn" data-section="seguridad">
                    <i class="fas fa-lock"></i> Seguridad
                </button>
                <a href="../logout.php" class="perfil-nav-btn btn-logout">
                    <i class="fas fa-sign-out-alt"></i> Cerrar Sesión
                </a>
            </nav>
        </div>

        <div class="perfil-content">
            <!-- Sección Mis Datos -->
            <section class="perfil-section active" id="datos">
                <div class="section-header">
                    <h1><i class="fas fa-user-circle"></i> MIS DATOS PERSONALES</h1>
                    <p>Actualiza tu información personal</p>
                </div>

                <form id="datosForm" class="perfil-form">
                    <input type="hidden" name="action" value="update_datos">
                    
                    <div class="form-group">
                        <label><i class="fas fa-user"></i> Nombre Completo</label>
                        <input type="text" 
                               name="nombre" 
                               value="<?php echo htmlspecialchars($usuario['nombre'] ?? ''); ?>" 
                               required>
                    </div>

                    <div class="form-group">
                        <label><i class="fas fa-envelope"></i> Correo Electrónico</label>
                        <input type="email" 
                               name="correo" 
                               value="<?php echo htmlspecialchars($usuario['correo']); ?>" 
                               required>
                    </div>

                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i> Guardar Cambios
                    </button>
                </form>
            </section>

            <!-- Sección Mis Pedidos -->
            <section class="perfil-section" id="pedidos">
                <div class="section-header">
                    <h1><i class="fas fa-box-open"></i> MIS PEDIDOS</h1>
                    <p>Historial de compras realizadas</p>
                </div>

                <div class="pedidos-list">
                    <?php 
                    $tiene_pedidos = false;
                    while($pedido = mysqli_fetch_assoc($pedidos_query)): 
                        $tiene_pedidos = true;
                        
                        // Obtener items del pedido
                        $items_query = mysqli_query($conexion, "
                            SELECT * FROM pedidos_items 
                            WHERE pedido_id = {$pedido['id']}
                        ");
                        
                        $estado_clases = [
                            'pendiente' => 'estado-pendiente',
                            'procesando' => 'estado-procesando',
                            'enviado' => 'estado-enviado',
                            'entregado' => 'estado-entregado',
                            'cancelado' => 'estado-cancelado'
                        ];
                        
                        $estado_icons = [
                            'pendiente' => '<i class="fas fa-clock"></i>',
                            'procesando' => '<i class="fas fa-box"></i>',
                            'enviado' => '<i class="fas fa-shipping-fast"></i>',
                            'entregado' => '<i class="fas fa-check-circle"></i>',
                            'cancelado' => '<i class="fas fa-times-circle"></i>'
                        ];
                    ?>
                    <div class="pedido-card">
                        <div class="pedido-header">
                            <div>
                                <h3><i class="fas fa-receipt"></i> Pedido #<?php echo str_pad($pedido['id'], 6, '0', STR_PAD_LEFT); ?></h3>
                                <p class="pedido-fecha"><i class="far fa-calendar-alt"></i> <?php echo date('d/m/Y H:i', strtotime($pedido['fecha_pedido'])); ?></p>
                            </div>
                            <span class="badge <?php echo $estado_clases[$pedido['estado']]; ?>">
                                <?php echo $estado_icons[$pedido['estado']] . ' ' . strtoupper($pedido['estado']); ?>
                            </span>
                        </div>
                        
                        <div class="pedido-info">
                            <div class="info-row">
                                <strong><i class="fas fa-credit-card"></i> Método de pago:</strong>
                                <span><?php echo ucfirst($pedido['metodo_pago']); ?></span>
                            </div>
                            <div class="info-row">
                                <strong><i class="fas fa-map-marker-alt"></i> Dirección:</strong>
                                <span><?php echo htmlspecialchars($pedido['direccion_envio']); ?></span>
                            </div>
                            <div class="info-row">
                                <strong><i class="fas fa-phone"></i> Teléfono:</strong>
                                <span><?php echo htmlspecialchars($pedido['telefono']); ?></span>
                            </div>
                        </div>
                        
                        <div class="pedido-items">
                            <strong><i class="fas fa-shopping-cart"></i> Productos:</strong>
                            <?php while($item = mysqli_fetch_assoc($items_query)): ?>
                            <div class="pedido-item-line">
                                <span>
                                    • <?php echo htmlspecialchars($item['nombre_producto']); ?>
                                    <?php if($item['talla']): ?>
                                        (Talla: <?php echo $item['talla']; ?>)
                                    <?php endif; ?>
                                    x<?php echo $item['cantidad']; ?>
                                </span>
                                <span>$<?php echo number_format($item['subtotal'], 2); ?></span>
                            </div>
                            <?php endwhile; ?>
                        </div>
                        
                        <div class="pedido-total">
                            <strong><i class="fas fa-dollar-sign"></i> TOTAL:</strong>
                            <strong>$<?php echo number_format($pedido['total'], 2); ?></strong>
                        </div>
                    </div>
                    <?php endwhile; ?>

                    <?php if(!$tiene_pedidos): ?>
                    <div class="empty-state">
                        <i class="fas fa-shopping-cart" style="font-size: 4rem; margin-bottom: 1rem; opacity: 0.3;"></i>
                        <h3>No tienes pedidos aún</h3>
                        <p>Explora nuestra tienda y realiza tu primera compra</p>
                        <a href="productos.php#products" class="btn btn-primary">
                            <i class="fas fa-store"></i> Ver Productos
                        </a>
                    </div>
                    <?php endif; ?>
                </div>
            </section>

            <!-- Sección Seguridad -->
            <section class="perfil-section" id="seguridad">
                <div class="section-header">
                    <h1><i class="fas fa-shield-alt"></i> SEGURIDAD</h1>
                    <p>Cambia tu contraseña</p>
                </div>

                <form id="passwordForm" class="perfil-form">
                    <input type="hidden" name="action" value="change_password">
                    
                    <div class="form-group">
                        <label><i class="fas fa-key"></i> Contraseña Actual</label>
                        <input type="password" name="current_password" required>
                    </div>

                    <div class="form-group">
                        <label><i class="fas fa-lock"></i> Nueva Contraseña</label>
                        <input type="password" name="new_password" id="newPassword" required minlength="6">
                        <small>Mínimo 6 caracteres</small>
                    </div>

                    <div class="form-group">
                        <label><i class="fas fa-check-circle"></i> Confirmar Nueva Contraseña</label>
                        <input type="password" name="confirm_password" id="confirmPassword" required>
                    </div>

                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-shield-alt"></i> Cambiar Contraseña
                    </button>
                </form>
            </section>
        </div>
    </main>

    <!-- Footer -->
    <footer class="footer">
        <?php include('footer.php') ?>
    </footer>
    <!-- Modal de Crop Mejorado -->
    <div id="cropModal" class="crop-modal">
        <div class="crop-container">
            <div class="crop-header">
                <h2>Ajustar Foto de Perfil</h2>
            </div>
            
            <div class="crop-body">
                <div class="crop-image-wrapper">
                    <img id="cropImage" alt="Imagen a recortar">
                </div>

                <div class="crop-toolbar">
                    <button type="button" id="zoomIn" class="crop-tool-btn" title="Acercar">+</button>
                    <button type="button" id="zoomOut" class="crop-tool-btn" title="Alejar">-</button>
                    <button type="button" id="rotateLeft" class="crop-tool-btn" title="Rotar izquierda">↶</button>
                    <button type="button" id="rotateRight" class="crop-tool-btn" title="Rotar derecha">↷</button>
                </div>

               

                <div class="crop-buttons">
                    <button type="button" class="crop-btn crop-btn-primary" id="cropApply">✓ Aplicar</button>
                    <button type="button" class="crop-btn crop-btn-secondary" id="cropCancel">✕ Cancelar</button>
                </div>
            </div>
        </div>
    </div>
    <!-- Notification Container -->
    <div id="notificationContainer"></div>
    <script>
    document.addEventListener('click', function(e) {
        const dropdown = document.querySelector('.user-dropdown');
        if(dropdown && !dropdown.contains(e.target)) {
            // Click fuera del dropdown
        }
    });
    </script>
    <!-- Cropper.js Library -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.6.1/cropper.min.js"></script>
    <script src="js/photo-cropper.js"></script>
    <script src="js/perfil.js"></script>
</body>
</html>