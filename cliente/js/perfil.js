// ==================== PERFIL.JS ====================

document.addEventListener('DOMContentLoaded', function() {
    initNavigation();
    initPhotoUpload();
    initForms();
});

function initNavigation() {
    const navButtons = document.querySelectorAll('.perfil-nav-btn[data-section]');
    const sections = document.querySelectorAll('.perfil-section');

    navButtons.forEach(button => {
        button.addEventListener('click', function() {
            const targetSection = this.getAttribute('data-section');
            navButtons.forEach(btn => btn.classList.remove('active'));
            this.classList.add('active');
            sections.forEach(section => section.classList.remove('active'));
            document.getElementById(targetSection).classList.add('active');
        });
    });
}

function initPhotoUpload() {
    const photoInput = document.getElementById('photoInput');
    const avatarPreview = document.getElementById('avatarPreview');
    
    if (!photoInput || !avatarPreview) return;
    
    photoInput.addEventListener('change', async function(e) {
        const file = this.files[0];
        if (!file) return;
        
        if (!file.type.match('image.*')) {
            showNotification('Por favor selecciona una imagen válida', 'error');
            this.value = '';
            return;
        }
        
        if (file.size > 20 * 1024 * 1024) {
            showNotification('La imagen es muy grande (máximo 20MB)', 'error');
            this.value = '';
            return;
        }
        
        try {
            showNotification('Optimizando imagen...', 'info');
            
            const optimizedBlob = await preOptimizeImage(file);
            showNotification('Ajusta tu foto...', 'info');
            
            const croppedBlob = await window.profileCropper.open(optimizedBlob);
            const finalBlob = await finalOptimizeImage(croppedBlob);
            
            const sizeKB = finalBlob.size / 1024;
            if (sizeKB > 500) {
                showNotification('Imagen muy grande. Intenta con otra.', 'error');
                this.value = '';
                return;
            }
            
            await uploadPhoto(finalBlob, avatarPreview);
            
        } catch (error) {
            if (error !== 'Cancelled') {
                console.error('Error:', error);
                showNotification('Error al procesar la imagen', 'error');
            }
        }
        
        this.value = '';
    });
}

async function preOptimizeImage(file) {
    return new Promise((resolve) => {
        const img = new Image();
        const url = URL.createObjectURL(file);
        
        img.onload = () => {
            URL.revokeObjectURL(url);
            
            let width = img.width;
            let height = img.height;
            const maxSize = 1200;
            
            if (width > maxSize || height > maxSize) {
                if (width > height) {
                    height = (height / width) * maxSize;
                    width = maxSize;
                } else {
                    width = (width / height) * maxSize;
                    height = maxSize;
                }
            }
            
            const canvas = document.createElement('canvas');
            canvas.width = width;
            canvas.height = height;
            
            const ctx = canvas.getContext('2d');
            ctx.imageSmoothingEnabled = true;
            ctx.imageSmoothingQuality = 'high';
            ctx.drawImage(img, 0, 0, width, height);
            
            canvas.toBlob((blob) => resolve(blob), 'image/jpeg', 0.92);
        };
        
        img.src = url;
    });
}

async function finalOptimizeImage(blob) {
    return new Promise((resolve) => {
        const img = new Image();
        const url = URL.createObjectURL(blob);
        
        img.onload = () => {
            URL.revokeObjectURL(url);
            
            const canvas = document.createElement('canvas');
            canvas.width = 400;
            canvas.height = 400;
            
            const ctx = canvas.getContext('2d');
            ctx.drawImage(img, 0, 0, 400, 400);
            
            canvas.toBlob((finalBlob) => resolve(finalBlob), 'image/jpeg', 0.85);
        };
        
        img.src = url;
    });
}

async function uploadPhoto(blob, avatarImg) {
    const formData = new FormData();
    formData.append('action', 'upload_photo');
    formData.append('foto', blob, 'profile.jpg');
    
    try {
        const response = await fetch('procesar_perfil.php', {
            method: 'POST',
            body: formData
        });
        
        const data = await response.json();
        
        if (data.success) {
            const reader = new FileReader();
            reader.onload = (e) => {
                avatarImg.src = e.target.result;
                const navAvatar = document.querySelector('.user-avatar');
                if (navAvatar) navAvatar.src = e.target.result;
            };
            reader.readAsDataURL(blob);
            
            const sizeKB = (blob.size / 1024).toFixed(1);
            showNotification('Foto actualizada: ' + sizeKB + 'KB', 'success');
        } else {
            showNotification(data.message || 'Error al subir la foto', 'error');
        }
    } catch (error) {
        console.error('Error:', error);
        showNotification('Error de conexión', 'error');
    }
}

function initForms() {
    const datosForm = document.getElementById('datosForm');
    if (datosForm) {
        datosForm.addEventListener('submit', async function(e) {
            e.preventDefault();
            
            const formData = new FormData(this);
            
            try {
                const response = await fetch('procesar_perfil.php', {
                    method: 'POST',
                    body: formData
                });
                
                const data = await response.json();
                
                if (data.success) {
                    showNotification('Datos actualizados correctamente', 'success');
                } else {
                    showNotification(data.message || 'Error', 'error');
                }
            } catch (error) {
                console.error('Error:', error);
                showNotification('Error al procesar', 'error');
            }
        });
    }
    
    const passwordForm = document.getElementById('passwordForm');
    if (passwordForm) {
        passwordForm.addEventListener('submit', async function(e) {
            e.preventDefault();
            
            const newPassword = document.getElementById('newPassword').value;
            const confirmPassword = document.getElementById('confirmPassword').value;
            
            if (newPassword !== confirmPassword) {
                showNotification('Las contraseñas no coinciden', 'error');
                return;
            }
            
            if (newPassword.length < 6) {
                showNotification('La contraseña debe tener al menos 6 caracteres', 'error');
                return;
            }
            
            const formData = new FormData(this);
            
            try {
                const response = await fetch('procesar_perfil.php', {
                    method: 'POST',
                    body: formData
                });
                
                const data = await response.json();
                
                if (data.success) {
                    showNotification('Contraseña actualizada', 'success');
                    this.reset();
                } else {
                    showNotification(data.message || 'Error', 'error');
                }
            } catch (error) {
                console.error('Error:', error);
                showNotification('Error', 'error');
            }
        });
    }
}

function showNotification(message, type = 'info') {
    let container = document.getElementById('notificationContainer');
    
    if (!container) {
        container = document.createElement('div');
        container.id = 'notificationContainer';
        document.body.appendChild(container);
    }
    
    const icons = { success: '✓', error: '✕', info: 'ℹ', warning: '⚠' };
    
    const notification = document.createElement('div');
    notification.className = 'notification notification-' + type;
    notification.innerHTML = '<span class="notification-icon">' + icons[type] + '</span><span class="notification-message">' + message + '</span>';
    
    container.appendChild(notification);
    
    setTimeout(() => notification.classList.add('show'), 10);
    setTimeout(() => {
        notification.classList.remove('show');
        setTimeout(() => notification.remove(), 300);
    }, 4000);
}

if (!document.querySelector('#notification-styles')) {
    const style = document.createElement('style');
    style.id = 'notification-styles';
    style.textContent = '#notificationContainer{position:fixed;top:100px;right:20px;z-index:10000;display:flex;flex-direction:column;gap:10px}.notification{display:flex;align-items:center;gap:12px;padding:15px 20px;border:3px solid #000;background:#fff;font-family:"Courier New",monospace;font-weight:bold;text-transform:uppercase;letter-spacing:1px;font-size:.9rem;opacity:0;transform:translateX(400px);transition:all .3s;min-width:300px;box-shadow:4px 4px 0 #000}.notification.show{opacity:1;transform:translateX(0)}.notification-icon{font-size:1.5rem}.notification-success{background:#4CAF50;color:#fff;border-color:#2e7d32}.notification-error{background:#f44336;color:#fff;border-color:#c62828}.notification-info{background:#2196F3;color:#fff;border-color:#1565c0}.notification-warning{background:#ffc107;color:#000;border-color:#f57f17}@media (max-width:768px){#notificationContainer{left:10px;right:10px;top:10px}.notification{min-width:auto;font-size:.8rem}}';
    document.head.appendChild(style);
}

console.log('perfil.js cargado');