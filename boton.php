<!-- 
=====================================================
COMPONENTE: ICONO INVITADO SUPERIOR
Estilo: Icono simple superior izquierda con texto
Uso: Incluir en cualquier página con <?php include 'guest_button.php'; ?>
=====================================================
-->

<!-- Font Awesome CDN (si no lo tienes ya incluido) -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">

<style>
    /* Contenedor del icono */
    #guest_icon_button {
        position: fixed;
        top: 20px;
        left: 20px;
        z-index: 9999;
        background: #000;
        color: #fff;
        border: 2px solid #fff;
        padding: 12px 20px;
        cursor: pointer;
        display: flex;
        align-items: center;
        gap: 10px;
        transition: all 0.3s ease;
        box-shadow: 0 0 20px rgba(0, 0, 0, 0.5);
        font-family: 'Courier New', monospace;
        font-size: 11px;
        letter-spacing: 1px;
        text-transform: uppercase;
    }

    #guest_icon_button:hover {
        background: #fff;
        color: #000;
        transform: translateY(-2px);
        box-shadow: 0 0 30px rgba(0, 0, 0, 0.7);
    }

    #guest_icon_button i {
        font-size: 16px;
    }

    /* Badge de número */
    #guest_icon_button .badge {
        position: absolute;
        top: -8px;
        right: -8px;
        background: #fff;
        color: #000;
        width: 20px;
        height: 20px;
        border: 2px solid #000;
        font-size: 11px;
        font-weight: bold;
        display: flex;
        align-items: center;
        justify-content: center;
        font-family: 'Courier New', monospace;
        animation: pulse-badge 2s infinite;
    }

    @keyframes pulse-badge {
        0%, 100% {
            transform: scale(1);
        }
        50% {
            transform: scale(1.2);
        }
    }

    /* Ocultar badge cuando se ha visto */
    #guest_icon_button.seen .badge {
        display: none;
    }

    /* Overlay oscuro de fondo */
    #guest_notification_overlay {
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: rgba(0, 0, 0, 0.8);
        z-index: 9997;
        opacity: 0;
        pointer-events: none;
        transition: opacity 0.4s ease;
    }

    #guest_notification_overlay.show {
        opacity: 1;
        pointer-events: all;
    }

    /* Contenedor de la notificación centrada */
    #guest_notification_popup {
        position: fixed;
        top: 50%;
        left: 50%;
        transform: translate(-50%, -50%) scale(0.8);
        width: 90%;
        max-width: 450px;
        background: #fff;
        border: 3px solid #000;
        padding: 0;
        z-index: 9998;
        box-shadow: 0 0 50px rgba(0, 0, 0, 0.8);
        opacity: 0;
        pointer-events: none;
        transition: all 0.4s cubic-bezier(0.68, -0.55, 0.265, 1.55);
    }

    #guest_notification_popup.show {
        transform: translate(-50%, -50%) scale(1);
        opacity: 1;
        pointer-events: all;
    }

    /* Header de la notificación */
    .notification-header {
        background: #000;
        color: #fff;
        padding: 15px 20px;
        display: flex;
        justify-content: space-between;
        align-items: center;
        border-bottom: 3px solid #000;
    }

    .notification-header h3 {
        font-family: 'Courier New', monospace;
        font-size: 13px;
        letter-spacing: 2px;
        text-transform: uppercase;
        margin: 0;
        display: flex;
        align-items: center;
        gap: 10px;
    }

    .notification-close {
        background: transparent;
        border: 1px solid #fff;
        color: #fff;
        width: 28px;
        height: 28px;
        cursor: pointer;
        font-size: 18px;
        transition: all 0.3s ease;
        padding: 0;
        line-height: 1;
    }

    .notification-close:hover {
        background: #fff;
        color: #000;
        transform: rotate(90deg);
    }

    /* Contenido de la notificación */
    .notification-content {
        padding: 30px;
        text-align: center;
    }

    /* Botón de la notificación - Estilo Double Frame */
    .btn-notification-guest {
        width: 100%;
        padding: 18px 30px;
        background: transparent;
        border: 2px solid #444;
        color: #888;
        cursor: pointer;
        font-family: 'Courier New', monospace;
        font-size: 13px;
        letter-spacing: 2px;
        text-transform: uppercase;
        position: relative;
        transition: all 0.3s ease;
        text-decoration: none;
        display: block;
        text-align: center;
    }

    .btn-notification-guest::before {
        content: '';
        position: absolute;
        inset: 5px;
        border: 1px solid #444;
        transition: all 0.3s ease;
        pointer-events: none;
    }

    .btn-notification-guest:hover {
        border-color: #000;
        color: #000;
        background: #f5f5f5;
        box-shadow: 0 0 15px rgba(0, 0, 0, 0.2);
    }

    .btn-notification-guest:hover::before {
        border-color: #000;
        inset: 3px;
    }

    /* Animación inicial del icono */
    @keyframes bounce-in {
        0% {
            transform: scale(0);
            opacity: 0;
        }
        50% {
            transform: scale(1.1);
        }
        100% {
            transform: scale(1);
            opacity: 1;
        }
    }

    #guest_icon_button.initial {
        animation: bounce-in 0.8s cubic-bezier(0.68, -0.55, 0.265, 1.55);
    }

    /* RESPONSIVE */
    @media (max-width: 768px) {
        #guest_icon_button {
            padding: 10px 16px;
            font-size: 10px;
            gap: 8px;
        }

        #guest_icon_button i {
            font-size: 14px;
        }

        #guest_notification_popup {
            max-width: 380px;
        }

        .notification-content {
            padding: 25px;
        }

        .btn-notification-guest {
            padding: 16px 25px;
            font-size: 12px;
        }
    }

    @media (max-width: 480px) {
        #guest_icon_button {
            top: 10px;
            left: 10px;
            padding: 8px 12px;
            font-size: 9px;
            gap: 6px;
        }

        #guest_icon_button i {
            font-size: 13px;
        }

        #guest_icon_button .badge {
            width: 18px;
            height: 18px;
            font-size: 10px;
            top: -6px;
            right: -6px;
        }

        #guest_notification_popup {
            width: 95%;
            max-width: 320px;
        }

        .notification-header h3 {
            font-size: 11px;
            letter-spacing: 1px;
        }

        .notification-close {
            width: 25px;
            height: 25px;
            font-size: 16px;
        }

        .notification-content {
            padding: 20px;
        }

        .btn-notification-guest {
            padding: 14px 20px;
            font-size: 11px;
            letter-spacing: 1px;
        }

        .btn-notification-guest::before {
            inset: 4px;
        }

        .btn-notification-guest:hover::before {
            inset: 2px;
        }
    }

    @media (max-width: 360px) {
        #guest_icon_button {
            font-size: 8px;
            padding: 7px 10px;
        }
    }
</style>

<!-- Overlay de fondo -->
<div id="guest_notification_overlay"></div>

<!-- Icono superior con texto -->
<div id="guest_icon_button" class="initial">
    <i class="fas fa-user-circle"></i>
    <span>Iniciar sin cuenta</span>
    <span class="badge">1</span>
</div>

<!-- Popup de notificación centrado -->
<div id="guest_notification_popup">
    <div class="notification-header">
        <h3>
            <i class="fas fa-user"></i>
            [ ACCESO INVITADO ]
        </h3>
        <button class="notification-close" onclick="closeNotification()">×</button>
    </div>
    
    <div class="notification-content">
        <a href="cliente/cliente.php" class="btn-notification-guest">
            [ CONTINUAR COMO INVITADO ]
        </a>
    </div>
</div>

<script>
    (function() {
        const icon = document.getElementById('guest_icon_button');
        const popup = document.getElementById('guest_notification_popup');
        const overlay = document.getElementById('guest_notification_overlay');
        let autoHideTimer = null;

        // Verificar si ya se mostró antes en esta sesión
        const hasSeenNotification = sessionStorage.getItem('guestNotificationSeen');

        // Mostrar notificación INMEDIATAMENTE al cargar la página
        if (!hasSeenNotification) {
            // Pequeña pausa para que cargue todo (500ms)
            setTimeout(function() {
                showNotification();
                
                // Auto-ocultar después de 10 segundos
                autoHideTimer = setTimeout(function() {
                    hideNotification();
                }, 10000);
            }, 500);
        } else {
            // Si ya la vio, quitar el badge
            icon.classList.add('seen');
        }

        // Toggle al hacer click en el icono
        icon.addEventListener('click', function(e) {
            e.stopPropagation();
            
            if (popup.classList.contains('show')) {
                hideNotification();
            } else {
                showNotification();
                
                // Auto-ocultar después de 12 segundos cuando se abre manualmente
                if (autoHideTimer) clearTimeout(autoHideTimer);
                autoHideTimer = setTimeout(function() {
                    hideNotification();
                }, 12000);
            }
        });

        // Cerrar al hacer click en el overlay
        overlay.addEventListener('click', function() {
            hideNotification();
        });

        // Cerrar con tecla ESC
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape' && popup.classList.contains('show')) {
                hideNotification();
            }
        });

        // Función para mostrar notificación
        function showNotification() {
            popup.classList.add('show');
            overlay.classList.add('show');
            icon.classList.add('seen');
            sessionStorage.setItem('guestNotificationSeen', 'true');
        }

        // Función para ocultar notificación
        function hideNotification() {
            popup.classList.remove('show');
            overlay.classList.remove('show');
            if (autoHideTimer) clearTimeout(autoHideTimer);
        }

        // Función global para cerrar
        window.closeNotification = function() {
            hideNotification();
        };
    })();
</script>
