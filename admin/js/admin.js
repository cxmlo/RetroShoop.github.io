// ==================== NAVEGACIÓN ====================
document.addEventListener('DOMContentLoaded', function() {
    initNavigation();
    initBurgerMenu();
    initModals();
    initForms();
});

function initNavigation() {
    const savedSection = sessionStorage.getItem('activeSection');
    if(savedSection) {
        activateSection(savedSection);
        sessionStorage.removeItem('activeSection');
    }
    
    document.querySelectorAll('.nav-link').forEach(link => {
        link.addEventListener('click', function(e) {
            e.preventDefault();
            const targetSection = this.getAttribute('data-section');
            activateSection(targetSection);
            
            if(window.innerWidth <= 1024) {
                closeSidebar();
            }
        });
    });
}

function activateSection(sectionId) {
    document.querySelectorAll('.nav-link').forEach(l => l.classList.remove('active'));
    document.querySelectorAll('.section').forEach(s => s.classList.remove('active'));
    
    const targetLink = document.querySelector(`[data-section="${sectionId}"]`);
    const targetSection = document.getElementById(sectionId);
    
    if(targetLink && targetSection) {
        targetLink.classList.add('active');
        targetSection.classList.add('active');
        
        if(sectionId === 'pedidos') loadPedidos();
        else if(sectionId === 'carritos') loadCarritos();
    }
}

function initBurgerMenu() {
    const burgerMenu = document.getElementById('burgerMenu');
    const sidebar = document.getElementById('sidebar');
    
    if(!burgerMenu || !sidebar) return;
    
    burgerMenu.addEventListener('click', () => {
        burgerMenu.classList.toggle('active');
        sidebar.classList.toggle('active');
    });
    
    document.addEventListener('click', (e) => {
        if(window.innerWidth <= 1024) {
            if(!sidebar.contains(e.target) && !burgerMenu.contains(e.target)) {
                closeSidebar();
            }
        }
    });
}

function closeSidebar() {
    const burgerMenu = document.getElementById('burgerMenu');
    const sidebar = document.getElementById('sidebar');
    if(burgerMenu && sidebar) {
        burgerMenu.classList.remove('active');
        sidebar.classList.remove('active');
    }
}

function initModals() {
    document.addEventListener('keydown', (e) => {
        if(e.key === 'Escape') {
            closeProductModal();
            closeUserModal();
            closeCarouselModal();
        }
    });
    
    document.querySelectorAll('.modal').forEach(modal => {
        modal.addEventListener('click', (e) => {
            if(e.target === modal) {
                modal.classList.remove('active');
            }
        });
    });
}

// ==================== PRODUCTOS ====================
function openProductModal() {
    resetForm('productForm');
    document.getElementById('modalTitle').textContent = 'NUEVO PRODUCTO';
    document.getElementById('productAction').value = 'save';
    document.getElementById('currentImage').textContent = '';
    document.getElementById('productImage').required = true;
    document.getElementById('productModal').classList.add('active');
}

function closeProductModal() {
    document.getElementById('productModal').classList.remove('active');
}

function editProduct(product) {
    document.getElementById('modalTitle').textContent = 'EDITAR PRODUCTO';
    document.getElementById('productId').value = product.id;
    document.getElementById('productName').value = product.nombre;
    document.getElementById('productCategory').value = product.categoria;
    document.getElementById('productPrice').value = product.precio;
    document.getElementById('productStock').value = product.stock || 10;
    document.getElementById('productDescription').value = product.descripcion || '';
    document.getElementById('productAction').value = 'update';
    document.getElementById('currentImage').textContent = '(Actual: ' + product.imagen + ')';
    document.getElementById('productImage').required = false;
    document.getElementById('productModal').classList.add('active');
}

function deleteProduct(id) {
    if(!confirm('¿Estás seguro de eliminar este producto?')) return;
    
    sendRequest('productos.php', {action: 'delete', id: id})
        .then(data => {
            if(data.success) {
                showNotification(data.message, 'success');
                removeTableRow('#productos tbody tr', id);
            } else {
                showNotification(data.message || 'Error al eliminar', 'error');
            }
        });
}

// ==================== USUARIOS ====================
function openUserModal() {
    resetForm('userForm');
    document.getElementById('userModalTitle').textContent = 'NUEVO USUARIO';
    document.getElementById('userAction').value = 'save';
    document.getElementById('userPassword').required = true;
    document.getElementById('passwordLabel').textContent = '*';
    document.getElementById('passwordHint').textContent = 'Mínimo 6 caracteres';
    document.getElementById('userModal').classList.add('active');
}

function closeUserModal() {
    document.getElementById('userModal').classList.remove('active');
}

function editUser(user) {
    document.getElementById('userModalTitle').textContent = 'EDITAR USUARIO';
    document.getElementById('userId').value = user.id;
    document.getElementById('userName').value = user.nombre || '';
    document.getElementById('userEmail').value = user.correo;
    document.getElementById('userPassword').value = '';
    document.getElementById('userAction').value = 'update';
    document.getElementById('userPassword').required = false;
    document.getElementById('passwordLabel').textContent = '';
    document.getElementById('passwordHint').textContent = 'Dejar vacío para no cambiar';
    document.getElementById('userModal').classList.add('active');
}

function deleteUser(id) {
    if(!confirm('¿Estás seguro de eliminar este usuario?')) return;
    
    sendRequest('usuarios.php', {action: 'delete', id: id})
        .then(data => {
            if(data.success) {
                showNotification(data.message, 'success');
                removeTableRow('#usuarios tbody tr', id);
            } else {
                showNotification(data.message || 'Error al eliminar', 'error');
            }
        });
}

function viewUserOrders(userId) {
    fetch(`usuarios.php?action=getOrders&usuario_id=${userId}`)
        .then(response => response.json())
        .then(data => {
            if(data.success && data.data.length > 0) {
                let ordersHtml = 'Pedidos del usuario:\n\n';
                data.data.forEach(order => {
                    ordersHtml += `• ${order.nombre}`;
                    if(order.talla) ordersHtml += ` (Talla: ${order.talla})`;
                    ordersHtml += ` - Cantidad: ${order.cantidad} - Precio: ${order.precio}\n`;
                });
                alert(ordersHtml);
            } else {
                showNotification('Este usuario no tiene pedidos', 'info');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showNotification('Error al cargar pedidos', 'error');
        });
}

// ==================== CAROUSEL ====================
function openCarouselModal() {
    resetForm('carouselForm');
    document.getElementById('carouselModalTitle').textContent = 'NUEVO SLIDE';
    document.getElementById('slideAction').value = 'save';
    document.getElementById('currentSlideImage').textContent = '';
    document.getElementById('slideImagen').required = true;
    document.getElementById('slideActivo').checked = true;
    document.getElementById('carouselModal').classList.add('active');
}

function closeCarouselModal() {
    document.getElementById('carouselModal').classList.remove('active');
}

function editSlide(slide) {
    document.getElementById('carouselModalTitle').textContent = 'EDITAR SLIDE';
    document.getElementById('slideId').value = slide.id;
    document.getElementById('slideTitulo').value = slide.titulo;
    document.getElementById('slideDescripcion').value = slide.descripcion;
    document.getElementById('slideOrden').value = slide.orden;
    document.getElementById('slideActivo').checked = slide.activo == 1;
    document.getElementById('slideAction').value = 'update';
    document.getElementById('currentSlideImage').textContent = '(Actual: ' + slide.imagen + ')';
    document.getElementById('slideImagen').required = false;
    document.getElementById('carouselModal').classList.add('active');
}

function deleteSlide(id) {
    if(!confirm('¿Eliminar este slide del carousel?')) return;
    
    sendRequest('carousel.php', {action: 'delete', id: id})
        .then(data => {
            if(data.success) {
                showNotification(data.message, 'success');
                sessionStorage.setItem('activeSection', 'carousel');
                setTimeout(() => location.reload(), 1000);
            } else {
                showNotification(data.message || 'Error al eliminar', 'error');
            }
        });
}

// ==================== PEDIDOS ====================
function loadPedidos() {
    fetch('pedidos.php?action=list')
        .then(response => response.json())
        .then(data => {
            if(data.success) {
                displayPedidosReales(data.data);
            } else {
                document.getElementById('pedidosContainer').innerHTML = 
                    '<p class="no-data">No hay pedidos registrados</p>';
            }
        })
        .catch(error => {
            console.error('Error:', error);
            document.getElementById('pedidosContainer').innerHTML = 
                '<p class="no-data">Error de conexión</p>';
        });
}

function displayPedidosReales(pedidos) {
    const container = document.getElementById('pedidosContainer');
    
    if(!pedidos || pedidos.length === 0) {
        container.innerHTML = '<p class="no-data">No hay pedidos registrados</p>';
        return;
    }
    
    const estadoClasses = {
        'pendiente': 'estado-pendiente',
        'procesando': 'estado-procesando',
        'enviado': 'estado-enviado',
        'entregado': 'estado-entregado',
        'cancelado': 'estado-cancelado'
    };
    
    const estadoIcons = {
        'pendiente': '⏳',
        'procesando': '📦',
        'enviado': '🚚',
        'entregado': '✅',
        'cancelado': '❌'
    };
    
    let html = pedidos.map(pedido => `
        <div class="pedido-card-real">
            <div class="pedido-header-real">
                <div>
                    <h3>Pedido #${String(pedido.id).padStart(6, '0')}</h3>
                    <p class="pedido-cliente">${pedido.usuario_nombre}</p>
                    <p class="pedido-email">${pedido.correo}</p>
                </div>
                <span class="badge ${estadoClasses[pedido.estado]}">
                    ${estadoIcons[pedido.estado]} ${pedido.estado.toUpperCase()}
                </span>
            </div>
            <div class="pedido-info-grid">
                <div class="info-item">
                    <strong>Fecha:</strong>
                    <span>${new Date(pedido.fecha_pedido).toLocaleDateString('es-ES')}</span>
                </div>
                <div class="info-item">
                    <strong>Pago:</strong>
                    <span>${pedido.metodo_pago}</span>
                </div>
                <div class="info-item">
                    <strong>Teléfono:</strong>
                    <span>${pedido.telefono}</span>
                </div>
                <div class="info-item full-width">
                    <strong>Dirección:</strong>
                    <span>${pedido.direccion_envio}</span>
                </div>
            </div>
            <div class="pedido-items-real">
                <strong>Productos:</strong>
                ${pedido.items.map(item => `
                    <div class="item-line">
                        • ${item.nombre_producto} 
                        ${item.talla ? `(${item.talla})` : ''} 
                        x${item.cantidad} - $${parseFloat(item.subtotal).toFixed(2)}
                    </div>
                `).join('')}
            </div>
            <div class="pedido-actions">
                <div class="pedido-total-real">
                    <strong>TOTAL: $${parseFloat(pedido.total).toFixed(2)}</strong>
                </div>
                <div class="action-buttons-pedido">
                    <select class="select-estado" data-pedido-id="${pedido.id}" 
                            ${pedido.estado === 'entregado' || pedido.estado === 'cancelado' ? 'disabled' : ''}>
                        <option value="pendiente" ${pedido.estado === 'pendiente' ? 'selected' : ''}>⏳ Pendiente</option>
                        <option value="procesando" ${pedido.estado === 'procesando' ? 'selected' : ''}>📦 Procesando</option>
                        <option value="enviado" ${pedido.estado === 'enviado' ? 'selected' : ''}>🚚 Enviado</option>
                        <option value="entregado" ${pedido.estado === 'entregado' ? 'selected' : ''}>✅ Entregado</option>
                        <option value="cancelado" ${pedido.estado === 'cancelado' ? 'selected' : ''}>❌ Cancelado</option>
                    </select>
                    <button class="btn-icon btn-delete" onclick="deletePedidoReal(${pedido.id})">🗑️</button>
                </div>
            </div>
        </div>
    `).join('');
    
    container.innerHTML = html;
    
    document.querySelectorAll('.select-estado').forEach(select => {
        select.addEventListener('change', function() {
            updateEstadoPedido(this.dataset.pedidoId, this.value);
        });
    });
}

function updateEstadoPedido(pedidoId, nuevoEstado) {
    if(!confirm(`¿Cambiar el estado del pedido a "${nuevoEstado}"?`)) {
        loadPedidos();
        return;
    }
    
    sendRequest('pedidos.php', {action: 'updateStatus', pedido_id: pedidoId, estado: nuevoEstado})
        .then(data => {
            showNotification(data.message, data.success ? 'success' : 'error');
            loadPedidos();
        });
}

function deletePedidoReal(pedidoId) {
    if(!confirm('¿Estás seguro de eliminar este pedido?')) return;
    
    sendRequest('pedidos.php', {action: 'delete', pedido_id: pedidoId})
        .then(data => {
            showNotification(data.message, data.success ? 'success' : 'error');
            if(data.success) loadPedidos();
        });
}

// ==================== CARRITOS ====================
function loadCarritos() {
    fetch('carritos.php?action=list')
        .then(response => response.json())
        .then(data => {
            if(data.success) displayCarritos(data.data);
            else document.getElementById('carritosContainer').innerHTML = '<p class="no-data">No hay carritos activos</p>';
        })
        .catch(() => {
            document.getElementById('carritosContainer').innerHTML = '<p class="no-data">Error de conexión</p>';
        });
}

function displayCarritos(carritos) {
    const container = document.getElementById('carritosContainer');
    
    if(!carritos || carritos.length === 0) {
        container.innerHTML = '<p class="no-data">No hay carritos activos</p>';
        return;
    }
    
    container.innerHTML = carritos.map(carrito => `
        <div class="pedido-card-real">
            <div class="pedido-header-real">
                <div>
                    <h3>🛒 Carrito Activo</h3>
                    <p class="pedido-cliente">${carrito.usuario}</p>
                    <p class="pedido-email">${carrito.correo}</p>
                </div>
                <span class="badge estado-pendiente">Activo</span>
            </div>
            <div class="pedido-items-real">
                <strong>Productos en carrito:</strong>
                ${carrito.items.map(item => `
                    <div class="item-line">
                        • ${item.nombre} ${item.talla ? `(${item.talla})` : ''} 
                        x${item.cantidad} - $${parseFloat(item.subtotal).toFixed(2)}
                    </div>
                `).join('')}
            </div>
            <div class="pedido-actions">
                <div class="pedido-total-real">
                    <strong>TOTAL: $${parseFloat(carrito.total).toFixed(2)}</strong>
                </div>
                <button class="btn btn-secondary" onclick="clearCarrito(${carrito.usuario_id})">
                    🗑️ Vaciar Carrito
                </button>
            </div>
        </div>
    `).join('');
}

function clearCarrito(usuarioId) {
    if(!confirm('¿Vaciar el carrito de este usuario?')) return;
    
    sendRequest('carritos.php', {action: 'clear', usuario_id: usuarioId})
        .then(data => {
            showNotification(data.message, data.success ? 'success' : 'error');
            if(data.success) loadCarritos();
        });
}

// ==================== FORMULARIOS ====================
function initForms() {
    const forms = [
        {id: 'productForm', endpoint: 'productos.php', section: 'productos', closeModal: closeProductModal},
        {id: 'userForm', endpoint: 'usuarios.php', section: 'usuarios', closeModal: closeUserModal, validate: validateUser},
        {id: 'carouselForm', endpoint: 'carousel.php', section: 'carousel', closeModal: closeCarouselModal}
    ];
    
    forms.forEach(({id, endpoint, section, closeModal, validate}) => {
        const form = document.getElementById(id);
        if(!form) return;
        
        form.addEventListener('submit', async (e) => {
            e.preventDefault();
            
            if(validate && !validate(new FormData(form))) return;
            
            const formData = new FormData(form);
            
            try {
                const response = await fetch(endpoint, {method: 'POST', body: formData});
                const text = await response.text();
                const data = JSON.parse(text);
                
                if(data.success) {
                    showNotification(data.message, 'success');
                    closeModal();
                    sessionStorage.setItem('activeSection', section);
                    setTimeout(() => location.reload(), 1000);
                } else {
                    showNotification(data.message || 'Error al guardar', 'error');
                }
            } catch(error) {
                console.error('Error:', error);
                showNotification('Error de conexión', 'error');
            }
        });
    });
}

function validateUser(formData) {
    const action = formData.get('action');
    const password = formData.get('password');
    
    if(action === 'save' && (!password || password.length < 6)) {
        showNotification('La contraseña debe tener al menos 6 caracteres', 'error');
        return false;
    }
    return true;
}

// ==================== UTILIDADES ====================
function sendRequest(url, data) {
    const formData = new FormData();
    Object.keys(data).forEach(key => formData.append(key, data[key]));
    
    return fetch(url, {method: 'POST', body: formData})
        .then(response => response.text())
        .then(text => {
            console.log('Response:', text);
            return JSON.parse(text);
        })
        .catch(error => {
            console.error('Error:', error);
            showNotification('Error de conexión', 'error');
            return {success: false};
        });
}

function removeTableRow(selector, id) {
    document.querySelectorAll(selector).forEach(row => {
        if(row.cells[0].textContent == id) {
            row.style.opacity = '0';
            setTimeout(() => row.remove(), 300);
        }
    });
}

function resetForm(formId) {
    document.getElementById(formId).reset();
}

function showNotification(message, type = 'info') {
    const container = document.getElementById('notificationContainer');
    if(!container) {
        alert(message);
        return;
    }
    
    const notification = document.createElement('div');
    notification.className = `notification notification-${type}`;
    notification.textContent = message;
    container.appendChild(notification);
    
    setTimeout(() => {
        notification.classList.add('hide');
        setTimeout(() => notification.remove(), 300);
    }, 3000);
}

console.log('Admin.js loaded successfully');