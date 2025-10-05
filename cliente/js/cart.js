// ==================== SHOPPING CART FUNCTIONALITY ====================

class ShoppingCart {
    constructor() {
        this.cart = [];
        this.selectedSize = null;
        this.modalQuantity = 1;
        this.currentProduct = null;
        this.isLoggedIn = false;
        
        // LÍMITES DE CANTIDAD
        this.MAX_CANTIDAD_POR_PRODUCTO = 10;
        this.MAX_ITEMS_TOTAL_CARRITO = 50;
        
        // DOM Elements
        this.elements = {
            cartModal: document.getElementById('cartModal'),
            cartBtn: document.getElementById('cartBtn'),
            closeCartBtn: document.getElementById('closeCart'),
            cartItemsContainer: document.getElementById('cartItems'),
            cartCountElement: document.getElementById('cartCount'),
            cartTotalElement: document.getElementById('cartTotal'),
            checkoutBtn: document.getElementById('checkoutBtn'),
            productModal: document.getElementById('productModal'),
            closeProductModalBtn: document.getElementById('closeProductModal'),
            addToCartFromModalBtn: document.getElementById('addToCartFromModal'),
            burgerMenu: document.getElementById('burgerMenu'),
            nav: document.getElementById('nav'),
            notificationContainer: document.getElementById('notificationContainer'),
            productModalName: document.getElementById('productModalName'),
            productModalPrice: document.getElementById('productModalPrice'),
            productModalImg: document.getElementById('productModalImg'),
            productModalDescription: document.getElementById('productModalDescription'),
            sizeOptions: document.getElementById('sizeOptions'),
            modalQuantity: document.getElementById('modalQuantity'),
            increaseQty: document.getElementById('increaseQty'),
            decreaseQty: document.getElementById('decreaseQty')
        };
        
        this.init();
    }

    async init() {
        await this.checkSession();
        
        this.bindCartEvents();
        this.bindProductModalEvents();
        this.bindMenuEvents();
        this.bindProductButtons();
        this.randomizeProducts(); // Aleatorizar productos al cargar
        
        if(this.isLoggedIn) {
            console.log('Usuario logueado, cargando carrito...');
            await this.loadCartFromDB();
        } else {
            console.log('Usuario NO logueado, no se carga el carrito');
        }
    }

    // ========== ALEATORIZAR PRODUCTOS ==========
    
    randomizeProducts() {
        // Solo aleatorizar en cliente.php
        const currentPage = window.location.pathname;
        if (!currentPage.includes('cliente.php')) {
            console.log('Aleatorización desactivada - Solo funciona en cliente.php');
            return;
        }
        
        const productGrid = document.querySelector('.products-grid');
        if (!productGrid) {
            console.log('No se encontró .products-grid');
            return;
        }

        const products = Array.from(productGrid.children);
        
        if (products.length === 0) {
            console.log('No hay productos para aleatorizar');
            return;
        }
        
        // Algoritmo Fisher-Yates Shuffle
        for (let i = products.length - 1; i > 0; i--) {
            const j = Math.floor(Math.random() * (i + 1));
            [products[i], products[j]] = [products[j], products[i]];
        }
        
        // Limpiar y reagregar en orden aleatorio
        productGrid.innerHTML = '';
        products.forEach(product => {
            productGrid.appendChild(product);
        });
        
        console.log(`${products.length} productos aleatorizados en cliente.php`);
    }

    // ========== VERIFICAR SESIÓN ==========
    
    async checkSession() {
        try {
            console.log('Verificando sesión...');
            const response = await fetch('api/verificar_sesion.php');
            const data = await response.json();
            console.log('Respuesta de sesión:', data);
            this.isLoggedIn = data.loggedin;
            console.log('Usuario logueado:', this.isLoggedIn);
        } catch(error) {
            console.error('Error verificando sesión:', error);
            this.isLoggedIn = false;
        }
    }

    // ========== OBTENER TOTAL DE ITEMS ==========
    
    getTotalItems() {
        return this.cart.reduce((total, item) => total + item.quantity, 0);
    }

    // ========== CARGAR CARRITO DESDE BASE DE DATOS ==========
    
    async loadCartFromDB() {
        if(!this.isLoggedIn) {
            console.log('No se carga carrito: usuario no logueado');
            return;
        }
        
        try {
            const response = await fetch('api/carrito.php?action=obtener');
            
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }
            
            const text = await response.text();
            const cleanText = text.trim().replace(/^\uFEFF/, '');
            
            let data;
            try {
                data = JSON.parse(cleanText);
            } catch(parseError) {
                console.error('Error parseando JSON:', parseError);
                throw new Error('Respuesta inválida del servidor');
            }
            
            if(data.success) {
                this.cart = data.items.map(item => ({
                    cartItemId: item.talla ? `${item.id}-${item.talla}` : item.id.toString(),
                    id: item.id.toString(),
                    dbId: item.carrito_id,
                    name: item.nombre,
                    price: parseFloat(item.precio),
                    image: `<img src="../img/producto/${item.imagen}" alt="${item.nombre}" style="width:100%; height:100%; object-fit:cover; border-radius: 8px;">`,
                    size: item.talla,
                    quantity: parseInt(item.cantidad)
                }));
                
                this.updateCart();
            }
        } catch(error) {
            console.error('Error cargando carrito:', error);
        }
    }

    // ========== EVENT BINDINGS ==========
    
    bindCartEvents() {
        this.elements.cartBtn.addEventListener('click', () => this.openCart());
        this.elements.closeCartBtn.addEventListener('click', () => this.closeCart());
        this.elements.cartModal.addEventListener('click', (e) => {
            if (e.target === this.elements.cartModal) this.closeCart();
        });
        this.elements.checkoutBtn.addEventListener('click', () => this.checkout());
    }

    bindProductModalEvents() {
        this.elements.closeProductModalBtn.addEventListener('click', () => this.closeProductModal());
        this.elements.productModal.addEventListener('click', (e) => {
            if (e.target === this.elements.productModal) this.closeProductModal();
        });
        this.elements.addToCartFromModalBtn.addEventListener('click', () => this.addFromModal());
        this.elements.increaseQty.addEventListener('click', () => this.changeModalQuantity(1));
        this.elements.decreaseQty.addEventListener('click', () => this.changeModalQuantity(-1));
        
        this.elements.modalQuantity.addEventListener('change', (e) => {
            const value = parseInt(e.target.value);
            if (value < 1) {
                this.modalQuantity = 1;
                this.elements.modalQuantity.value = 1;
            } else if (value > this.MAX_CANTIDAD_POR_PRODUCTO) {
                this.modalQuantity = this.MAX_CANTIDAD_POR_PRODUCTO;
                this.elements.modalQuantity.value = this.MAX_CANTIDAD_POR_PRODUCTO;
                this.showNotification(`Límite máximo: ${this.MAX_CANTIDAD_POR_PRODUCTO} unidades`);
            } else {
                this.modalQuantity = value;
            }
        });
        
        document.querySelectorAll('.size-btn').forEach(btn => {
            btn.addEventListener('click', (e) => this.selectSize(e.target));
        });
    }

    bindMenuEvents() {
        this.elements.burgerMenu.addEventListener('click', () => this.toggleMenu());
        
        document.querySelectorAll('.nav-list a').forEach(link => {
            link.addEventListener('click', () => {
                this.elements.nav.classList.remove('active');
                this.elements.burgerMenu.classList.remove('active');
            });
        });
    }

    bindProductButtons() {
        document.querySelectorAll('.add-to-cart').forEach(button => {
            button.addEventListener('click', (e) => {
                console.log('Click en agregar. Usuario logueado:', this.isLoggedIn);
                
                if(!this.isLoggedIn) {
                    console.log('Mostrando notificación retro');
                    this.showRetroNotification();
                    return;
                }
                
                const { id, name, price, image } = e.target.dataset;
                this.addToCart(id, name, parseFloat(price), image);
            });
        });

        document.querySelectorAll('.view-product').forEach(button => {
            button.addEventListener('click', (e) => {
                this.showProductDetails(e.target.dataset);
            });
        });

        document.querySelectorAll('.product-image').forEach(image => {
            image.addEventListener('click', (e) => {
                const productCard = e.currentTarget.closest('.product-card');
                const viewBtn = productCard.querySelector('.view-product');
                if(viewBtn) {
                    this.showProductDetails(viewBtn.dataset);
                }
            });
        });
    }

    // ========== MENU ==========
    
    toggleMenu() {
        this.elements.nav.classList.toggle('active');
        this.elements.burgerMenu.classList.toggle('active');
    }

    // ========== PRODUCT MODAL ==========
    
    showProductDetails(dataset) {
        const { id, name, price, image, description } = dataset;
        
        this.currentProduct = {
            id: id,
            name: name,
            price: parseFloat(price),
            image: image,
            description: description
        };
        
        this.selectedSize = null;
        this.modalQuantity = 1;

        this.elements.productModalName.textContent = name;
        this.elements.productModalPrice.textContent = `$${parseFloat(price).toFixed(2)}`;
        this.elements.productModalImg.innerHTML = `<img src="../img/producto/${image}" alt="${name}" style="width:100%; height:100%; object-fit:cover; border-radius: 12px;">`;
        this.elements.productModalDescription.textContent = description;
        this.elements.modalQuantity.value = 1;
        this.elements.modalQuantity.max = this.MAX_CANTIDAD_POR_PRODUCTO;

        document.querySelectorAll('.size-btn').forEach(btn => {
            btn.classList.remove('active');
        });

        this.openProductModal();
    }

    selectSize(button) {
        document.querySelectorAll('.size-btn').forEach(btn => {
            btn.classList.remove('active');
        });
        button.classList.add('active');
        this.selectedSize = button.dataset.size;
    }

    changeModalQuantity(change) {
        const newQuantity = this.modalQuantity + change;
        
        if (newQuantity < 1) {
            this.showNotification('La cantidad mínima es 1');
            return;
        }
        
        if (newQuantity > this.MAX_CANTIDAD_POR_PRODUCTO) {
            this.showNotification(`Límite máximo: ${this.MAX_CANTIDAD_POR_PRODUCTO} unidades`);
            return;
        }
        
        this.modalQuantity = newQuantity;
        this.elements.modalQuantity.value = this.modalQuantity;
    }

    async addFromModal() {
        if (!this.currentProduct) return;
        
        if(!this.isLoggedIn) {
            this.closeProductModal();
            this.showRetroNotification();
            return;
        }
        
        if (!this.selectedSize) {
            alert('Por favor selecciona una talla');
            return;
        }

        const quantity = parseInt(this.elements.modalQuantity.value);
        
        const totalActual = this.getTotalItems();
        if (totalActual + quantity > this.MAX_ITEMS_TOTAL_CARRITO) {
            this.showNotification(`Excede el límite total del carrito (${this.MAX_ITEMS_TOTAL_CARRITO} items)`);
            return;
        }
        
        if (quantity > this.MAX_CANTIDAD_POR_PRODUCTO) {
            this.showNotification(`Límite máximo: ${this.MAX_CANTIDAD_POR_PRODUCTO} unidades por producto`);
            return;
        }
        
        let cantidadAgregada = 0;
        for (let i = 0; i < quantity; i++) {
            const resultado = await this.addToCart(
                this.currentProduct.id,
                this.currentProduct.name,
                this.currentProduct.price,
                this.currentProduct.image,
                this.selectedSize,
                true
            );
            
            if (resultado) {
                cantidadAgregada++;
            } else {
                break;
            }
        }
        
        if (cantidadAgregada > 0) {
            const sizeName = this.selectedSize ? ` (${this.selectedSize})` : '';
            if (cantidadAgregada === 1) {
                this.showNotification(`${this.currentProduct.name}${sizeName} agregado al carrito`);
            } else {
                this.showNotification(`${cantidadAgregada} unidades de ${this.currentProduct.name}${sizeName} agregadas al carrito`);
            }
        }
        
        this.closeProductModal();
    }

    openProductModal() {
        this.elements.productModal.classList.add('active');
        document.body.style.overflow = 'hidden';
    }

    closeProductModal() {
        this.elements.productModal.classList.remove('active');
        document.body.style.overflow = 'auto';
        this.currentProduct = null;
        this.selectedSize = null;
        this.modalQuantity = 1;
    }

    // ========== CART OPERATIONS ==========
    
    async addToCart(id, name, price, image, size = null, silencioso = false) {
        try {
            const totalActual = this.getTotalItems();
            if (totalActual >= this.MAX_ITEMS_TOTAL_CARRITO) {
                if (!silencioso) {
                    this.showNotification(`Límite total del carrito alcanzado (${this.MAX_ITEMS_TOTAL_CARRITO} items)`);
                }
                return false;
            }
            
            const cartItemId = size ? `${id}-${size}` : id.toString();
            const itemExistente = this.cart.find(i => i.cartItemId === cartItemId);
            
            if (itemExistente && itemExistente.quantity >= this.MAX_CANTIDAD_POR_PRODUCTO) {
                if (!silencioso) {
                    this.showNotification(`Límite máximo: ${this.MAX_CANTIDAD_POR_PRODUCTO} unidades por producto`);
                }
                return false;
            }
            
            const formData = new FormData();
            formData.append('action', 'agregar');
            formData.append('producto_id', id);
            formData.append('cantidad', 1);
            if(size) formData.append('talla', size);
            
            const response = await fetch('api/carrito.php', {
                method: 'POST',
                body: formData
            });
            
            if (!response.ok) {
                throw new Error(`Error del servidor: ${response.status}`);
            }
            
            const text = await response.text();
            const cleanText = text.trim().replace(/^\uFEFF/, '');
            
            let data;
            try {
                data = JSON.parse(cleanText);
            } catch(parseError) {
                console.error('Error parseando respuesta:', cleanText);
                throw new Error('Respuesta inválida del servidor');
            }
            
            if(data.success) {
                await this.loadCartFromDB();
                
                if (!silencioso) {
                    const sizeName = size ? ` (${size})` : '';
                    this.showNotification(`${name}${sizeName} agregado al carrito`);
                }
                
                return true;
            } else {
                if (!silencioso) {
                    this.showNotification(data.message || 'Error al agregar el producto');
                }
                return false;
            }
        } catch(error) {
            console.error('Error agregando al carrito:', error);
            if (!silencioso) {
                this.showNotification('Error al agregar el producto');
            }
            return false;
        }
    }

    async removeFromCart(cartItemId) {
        const item = this.cart.find(i => i.cartItemId === cartItemId);
        if(!item || !item.dbId) return;
        
        try {
            const formData = new FormData();
            formData.append('action', 'eliminar');
            formData.append('carrito_id', item.dbId);
            
            const response = await fetch('api/carrito.php', {
                method: 'POST',
                body: formData
            });
            
            const data = await response.json();
            
            if(data.success) {
                this.cart = this.cart.filter(i => i.cartItemId !== cartItemId);
                this.updateCart();
                this.showNotification('Producto eliminado del carrito');
            }
        } catch(error) {
            console.error('Error eliminando del carrito:', error);
            this.showNotification('Error al eliminar el producto');
        }
    }

    async updateQuantity(cartItemId, change) {
        const item = this.cart.find(i => i.cartItemId === cartItemId);
        
        if (item) {
            const newQuantity = item.quantity + change;
            
            if (newQuantity <= 0) {
                await this.removeFromCart(cartItemId);
                return;
            }
            
            if (newQuantity > this.MAX_CANTIDAD_POR_PRODUCTO) {
                this.showNotification(`Límite máximo: ${this.MAX_CANTIDAD_POR_PRODUCTO} unidades por producto`);
                return;
            }
            
            const totalSinEsteItem = this.getTotalItems() - item.quantity;
            if (totalSinEsteItem + newQuantity > this.MAX_ITEMS_TOTAL_CARRITO) {
                this.showNotification(`Excede el límite total del carrito (${this.MAX_ITEMS_TOTAL_CARRITO} items)`);
                return;
            }
            
            try {
                const formData = new FormData();
                formData.append('action', 'actualizar');
                formData.append('carrito_id', item.dbId);
                formData.append('cantidad', newQuantity);
                
                const response = await fetch('api/carrito.php', {
                    method: 'POST',
                    body: formData
                });
                
                const data = await response.json();
                
                if(data.success) {
                    item.quantity = newQuantity;
                    this.updateCart();
                }
            } catch(error) {
                console.error('Error actualizando cantidad:', error);
                this.showNotification('Error al actualizar cantidad');
            }
        }
    }

    updateCart() {
        const totalItems = this.getTotalItems();
        this.elements.cartCountElement.textContent = totalItems;
        this.renderCartItems();
        this.updateTotal();
    }

    renderCartItems() {
        if (this.cart.length === 0) {
            this.elements.cartItemsContainer.innerHTML = `
                <p class="empty-cart">
                    <i class="fas fa-shopping-cart" style="font-size: 3rem; display: block; margin-bottom: 1rem; opacity: 0.5;"></i>
                    Tu carrito está vacío
                </p>
            `;
            return;
        }

        this.elements.cartItemsContainer.innerHTML = '';
        
        this.cart.forEach(item => {
            const cartItem = document.createElement('div');
            cartItem.classList.add('cart-item');
            
            const sizeName = item.size ? `<span style="color: #666; font-size: 0.9rem;"> - Talla: ${item.size}</span>` : '';
            
            const enLimite = item.quantity >= this.MAX_CANTIDAD_POR_PRODUCTO;
            const btnPlusDisabled = enLimite ? 'disabled style="opacity: 0.5; cursor: not-allowed;"' : '';
            
            cartItem.innerHTML = `
                <div class="cart-item-image">${item.image}</div>
                <div class="cart-item-info">
                    <h3>${item.name}${sizeName}</h3>
                    <p class="cart-item-price"><i class="fas fa-tag"></i> ${item.price.toFixed(2)} c/u</p>
                    ${enLimite ? '<small style="color: #ff6b6b;"><i class="fas fa-exclamation-triangle"></i> Límite máximo alcanzado</small>' : ''}
                </div>
                <div class="cart-item-controls">
                    <button class="quantity-btn minus fas fa-minus" data-id="${item.cartItemId}">
                       
                    </button>
                    <span class="cart-item-quantity">${item.quantity}</span>
                    <button class="quantity-btn plus fas fa-plus" data-id="${item.cartItemId}" ${btnPlusDisabled}>
                       
                    </button>
                    <button class="remove-btn fas fa-trash-alt" data-id="${item.cartItemId}">
                        
                    </button>
                </div>
            `;
            
            this.elements.cartItemsContainer.appendChild(cartItem);
        });

        this.bindCartItemEvents();
    }

    bindCartItemEvents() {
        document.querySelectorAll('.quantity-btn.plus').forEach(btn => {
            if (!btn.hasAttribute('disabled')) {
                btn.addEventListener('click', (e) => {
                    this.updateQuantity(e.target.dataset.id, 1);
                });
            }
        });

        document.querySelectorAll('.quantity-btn.minus').forEach(btn => {
            btn.addEventListener('click', (e) => {
                this.updateQuantity(e.target.dataset.id, -1);
            });
        });

        document.querySelectorAll('.remove-btn').forEach(btn => {
            btn.addEventListener('click', (e) => {
                this.removeFromCart(e.target.dataset.id);
            });
        });
    }

    updateTotal() {
        const total = this.cart.reduce((sum, item) => {
            return sum + (item.price * item.quantity);
        }, 0);
        
        this.elements.cartTotalElement.textContent = `$${total.toFixed(2)}`;
    }

    openCart() {
        this.elements.cartModal.classList.add('active');
        document.body.style.overflow = 'hidden';
    }

    closeCart() {
        this.elements.cartModal.classList.remove('active');
        document.body.style.overflow = 'auto';
    }

    async checkout() {
        if (this.cart.length === 0) {
            alert('Tu carrito está vacío');
            return;
        }

        window.location.href = 'proceso_compra/checkout.php';
    }

    // ========== NOTIFICACIÓN RETRO ==========
    
    showRetroNotification() {
        const overlay = document.createElement('div');
        overlay.className = 'retro-notification-overlay';
        overlay.innerHTML = `
            <div class="retro-notification-box">
                <div class="retro-notification-header">
                    <h2><i class=""></i>:v</h2>
                    <h3>crea una cuenta primero loca</h3>
                </div>
                <div class="retro-notification-content">
                    <div class="retro-icon"><i class="fas fa-user-slash" style="font-size: 4rem;"></i></div>
                    <p class="retro-message">
                        <i class="fas fa-info-circle"></i> Necesitas una cuenta para agregar productos al carrito
                    </p>
                    <div class="retro-buttons">
                        <a href="../login.php" class="retro-btn retro-btn-primary">
                            <i class="fas fa-sign-in-alt"></i> INICIAR SESIÓN
                        </a>
                        <a href="../register.php" class="retro-btn retro-btn-secondary">
                            <i class="fas fa-user-plus"></i> CREAR CUENTA
                        </a>
                        <button class="retro-btn retro-btn-close">
                            <i class="fas fa-times"></i> CERRAR
                        </button>
                    </div>
                </div>
            </div>
        `;
        
        document.body.appendChild(overlay);
        
        const closeBtn = overlay.querySelector('.retro-btn-close');
        closeBtn.addEventListener('click', () => overlay.remove());
        
        overlay.addEventListener('click', (e) => {
            if(e.target === overlay) overlay.remove();
        });
        
        document.addEventListener('keydown', function escHandler(e) {
            if(e.key === 'Escape') {
                overlay.remove();
                document.removeEventListener('keydown', escHandler);
            }
        });
    }

    // ========== NOTIFICATIONS ==========
    
    showNotification(message) {
        const notification = document.createElement('div');
        notification.className = 'notification';
        notification.textContent = message;
        
        this.elements.notificationContainer.appendChild(notification);
        
        setTimeout(() => {
            notification.classList.add('hide');
            setTimeout(() => {
                if (notification.parentNode) {
                    notification.parentNode.removeChild(notification);
                }
            }, 300);
        }, 3000);
    }
}

document.addEventListener('DOMContentLoaded', () => {
    new ShoppingCart();
});