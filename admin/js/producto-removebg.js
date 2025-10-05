// Agregar al final de admin.js o crear archivo separado

let processedImageBlobModal = null;

// Inicializar funcionalidad de remoción de fondo
document.addEventListener('DOMContentLoaded', function() {
    const productImage = document.getElementById('productImage');
    
    if(productImage) {
        productImage.addEventListener('change', async function(e) {
            const file = e.target.files[0];
            if(!file) return;
            
            // Validar imagen
            if(!file.type.startsWith('image/')) {
                showNotification('Selecciona una imagen válida', 'error');
                return;
            }
            
            // Validar tamaño (10MB)
            if(file.size > 20 * 1024 * 1024) {
                showNotification('La imagen debe ser menor a 20MB', 'error');
                return;
            }
            
            // Mostrar confirmación
            if(!confirm('¿Deseas remover el fondo de esta imagen con Remove.bg?')) {
                return;
            }
            
            // Mostrar loading
            showNotification('Procesando imagen...', 'info');
            
            try {
                await processImageWithRemoveBg(file);
                showNotification('Fondo removido exitosamente', 'success');
            } catch(error) {
                console.error('Error:', error);
                showNotification('Error al procesar la imagen: ' + error.message, 'error');
            }
        });
    }
});

async function processImageWithRemoveBg(file) {
    const formData = new FormData();
    formData.append('image_file', file);
    
    const response = await fetch('procesar_removebg.php', {
        method: 'POST',
        body: formData
    });
    
    if(!response.ok) {
        const errorText = await response.text();
        throw new Error(errorText || 'Error en la API');
    }
    
    // Guardar blob procesado
    processedImageBlobModal = await response.blob();
    
    // Opcional: Mostrar preview
    const previewUrl = URL.createObjectURL(processedImageBlobModal);
    console.log('Imagen procesada:', previewUrl);
}

// Modificar el submit del formulario para usar la imagen procesada
const productFormOriginal = document.getElementById('productForm');
if(productFormOriginal) {
    const originalSubmit = productFormOriginal.onsubmit;
    
    productFormOriginal.onsubmit = async function(e) {
        e.preventDefault();
        
        const action = document.getElementById('productAction').value;
        const formData = new FormData(this);
        
        // Si hay imagen procesada, usarla
        if(processedImageBlobModal && action === 'save') {
            formData.delete('imagen');
            formData.append('imagen', processedImageBlobModal, 'producto_sin_fondo.png');
        }
        
        // Enviar formulario
        fetch('productos.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.text())
        .then(text => {
            try {
                const data = JSON.parse(text);
                if(data.success) {
                    showNotification(data.message, 'success');
                    closeProductModal();
                    processedImageBlobModal = null;
                    sessionStorage.setItem('activeSection', 'productos');
                    setTimeout(() => location.reload(), 1000);
                } else {
                    showNotification(data.message || 'Error al guardar', 'error');
                }
            } catch(e) {
                console.error('JSON Parse Error:', e);
                showNotification('Error en la respuesta del servidor', 'error');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showNotification('Error de conexión', 'error');
        });
    };
}

// Reset al cerrar modal
const originalClose = window.closeProductModal;
window.closeProductModal = function() {
    processedImageBlobModal = null;
    if(originalClose) {
        originalClose();
    } else {
        document.getElementById('productModal').classList.remove('active');
    }
};

console.log('Remove.bg integration loaded');