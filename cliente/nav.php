<?php
// Obtener datos del usuario si está logueado
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
?>
<div class="container">
    <div class="header-content">
        <h1 class="logo">RetroVibes</h1>
        
        <!-- Burger Menu Button -->
        <button class="burger-menu" id="burgerMenu" aria-label="Menu">
            <span></span>
            <span></span>
            <span></span>
        </button>
        
        <nav class="nav" id="nav">
            <ul class="nav-list">
                <li><a href="cliente.php">INICIO</a></li>
                <li><a href="productos.php">PRODUCTOS</a></li>
                <li><a href="nosotros.php">NOSOTROS</a></li>
                <li><a href="contacto.php">CONTACTO</a></li>
                
                <!-- Mostrar perfil y logout en móvil -->
                <?php if(isset($_SESSION['correo'])): ?>
                <li class="mobile-only">
                    <a href="perfil.php"> MI PERFIL</a>
                </li>
                <li class="mobile-only">
                    <a href="../logout.php" class="logout-link"> CERRAR SESIÓN</a>
                </li>
                <?php endif; ?>
            </ul>
        </nav>
        
        <div class="nav-actions">
            <button class="cart-btn" id="cartBtn">
                 <span class="cart-btn-text">CARRITO</span> (<span id="cartCount">0</span>)
            </button>
            
            <?php if(isset($_SESSION['correo'])): ?>
            <!-- Dropdown de perfil (solo desktop) -->
            <div class="user-dropdown desktop-only">
                <button class="user-profile-btn" id="userProfileBtn">
                    <img src="../img/usuario/<?php echo htmlspecialchars($user_foto); ?>" 
                         alt="Perfil" 
                         class="user-avatar"
                         onerror="this.src='../img/usuario/default.jpg'">
                    <span class="user-name"><?php echo htmlspecialchars($user_nombre); ?></span>
                    <span class="dropdown-arrow">▼</span>
                </button>
                
                <div class="user-dropdown-menu" id="userDropdownMenu">
                    <a href="perfil.php" class="dropdown-item">
                        <span></span> Mi Perfil
                    </a>
                    <a href="../logout.php" class="dropdown-item logout">
                        <span></span> Cerrar Sesión
                    </a>
                </div>
            </div>
            <?php endif; ?>
        </div>
    </div>
</div>

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
        border: flex;
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
                justify-content: center;
    }
    
    .logout-link {
        color: #ff4444 !important;
        font-weight: bold;
    }
}body {
            top: 0 !important;
            position: relative !important;
        }

        /* Ajustar header cuando el traductor está activo */
        body.translated-ltr .header,
        body.translated-rtl .header {
            margin-top: 45px;
            transition: margin-top 0.3s ease;
        }

        /* Ajustar el menú burger desplegable cuando hay traducción */
        body.translated-ltr .nav,
        body.translated-rtl .nav {
            top: 118px !important; /* 73px del header + 45px del traductor */
        }

        /* Responsive */
        @media (max-width: 768px) {
            body.translated-ltr .header,
            body.translated-rtl .header {
                margin-top: 35px;
            }
            
            body.translated-ltr .nav,
            body.translated-rtl .nav {
                top: 108px !important; /* 73px del header + 35px del traductor */
            }
        }
</style>

<script>
// Script adicional para cerrar el dropdown al hacer click fuera
document.addEventListener('click', function(e) {
    const dropdown = document.querySelector('.user-dropdown');
    if(dropdown && !dropdown.contains(e.target)) {
        // Click fuera del dropdown
    }
});
</script>
