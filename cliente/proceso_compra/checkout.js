// ==================== CHECKOUT FUNCTIONALITY ====================

document.addEventListener('DOMContentLoaded', function() {
    const checkoutForm = document.getElementById('checkoutForm');
    
    if(checkoutForm) {
        checkoutForm.addEventListener('submit', function(e) {
            e.preventDefault();
            
            // Validar formulario
            if(!validateForm()) {
                return;
            }
            
            // Mostrar notificación de procesamiento
            showNotification('Procesando tu pedido...', 'info');
            
            // Confirmar pedido con un setTimeout para mostrar la notificación
            setTimeout(() => {
                if(confirm('¿Confirmar pedido? Una vez confirmado no podrás modificarlo.')) {
                    showNotification('Enviando pedido...', 'success');
                    setTimeout(() => {
                        this.submit();
                    }, 500);
                }
            }, 500);
        });
    }
});

function validateForm() {
    const nombre = document.querySelector('[name="nombre"]').value.trim();
    const telefono = document.querySelector('[name="telefono"]').value.trim();
    const direccion = document.querySelector('[name="direccion"]').value.trim();
    const metodoPago = document.querySelector('[name="metodo_pago"]:checked');
    
    if(!nombre || nombre.length < 3) {
        showNotification('Por favor ingresa un nombre válido', 'error');
        return false;
    }
    
    if(!telefono || telefono.length < 7) {
        showNotification('Por favor ingresa un teléfono válido', 'error');
        return false;
    }
    
    if(!direccion || direccion.length < 10) {
        showNotification('Por favor ingresa una dirección completa', 'error');
        return false;
    }
    
    if(!metodoPago) {
        showNotification('Por favor selecciona un método de pago', 'error');
        return false;
    }
    
    return true;
}

function showNotification(message, type = 'success') {
    const container = document.getElementById('notificationContainer');
    if(!container) return;
    
    const notification = document.createElement('div');
    notification.className = `notification ${type}`;
    
    const icons = {
        'success': '✓',
        'error': '✕',
        'info': 'ℹ'
    };
    
    notification.innerHTML = `<strong>${icons[type] || 'ℹ'}</strong> ${message}`;
    
    container.appendChild(notification);
    
    setTimeout(() => {
        notification.classList.add('hide');
        setTimeout(() => notification.remove(), 300);
    }, 3000);
}