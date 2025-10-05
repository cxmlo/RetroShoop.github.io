// ==================== CONFIGURACIÓN ====================

const IMAGE_CONFIGS = {
    producto: {
        maxSizeKB: 500,
        maxWidth: 800,
        maxHeight: 800,
        quality: 0.85,
        allowedTypes: ['image/jpeg', 'image/jpg', 'image/png', 'image/webp'],
        allowRemoveBg: true
    },
    usuario: {
        maxSizeKB: 300,
        maxWidth: 400,
        maxHeight: 400,
        quality: 0.80,
        allowedTypes: ['image/jpeg', 'image/jpg', 'image/png'],
        allowRemoveBg: false
    },
    carousel: {
        maxSizeKB: 800,
        maxWidth: 1920,
        maxHeight: 600,
        quality: 0.90,
        allowedTypes: ['image/jpeg', 'image/jpg', 'image/png', 'image/webp'],
        allowRemoveBg: false
    }
};

const REMOVEBG_API_KEY = 'Ktri9L8X24kmBtX4G6bTN9oA';

// Estado global para productos
let optimizedProductImage = null;

// ==================== FUNCIONES DE OPTIMIZACIÓN ====================

async function validateAndOptimizeImage(file, type = 'producto', keepTransparency = false) {
    const config = IMAGE_CONFIGS[type];
    
    if (!config) {
        throw new Error('Tipo de imagen no válido');
    }
    
    if (!config.allowedTypes.includes(file.type)) {
        throw new Error(`Formato no válido`);
    }
    
    const maxInputSize = 20 * 1024 * 1024;
    if (file.size > maxInputSize) {
        const sizeMB = (file.size / (1024 * 1024)).toFixed(1);
        throw new Error(`Imagen muy grande (${sizeMB}MB). Máximo: 20MB`);
    }
    
    const img = await loadImage(file);
    
    const { width, height } = calculateDimensions(
        img.width, 
        img.height, 
        config.maxWidth, 
        config.maxHeight
    );
    
    // Si keepTransparency es true, usar PNG, sino JPEG
    const outputFormat = keepTransparency ? 'image/png' : 'image/jpeg';
    let optimizedBlob = await resizeAndCompress(img, width, height, config.quality, outputFormat);
    
    if (optimizedBlob.size > config.maxSizeKB * 1024) {
        let quality = config.quality - 0.1;
        while (optimizedBlob.size > config.maxSizeKB * 1024 && quality > 0.5) {
            optimizedBlob = await resizeAndCompress(img, width, height, quality, outputFormat);
            quality -= 0.1;
        }
    }
    
    return optimizedBlob;
}

function loadImage(file) {
    return new Promise((resolve, reject) => {
        const img = new Image();
        const url = URL.createObjectURL(file);
        
        img.onload = () => {
            URL.revokeObjectURL(url);
            resolve(img);
        };
        
        img.onerror = () => {
            URL.revokeObjectURL(url);
            reject(new Error('Error al cargar la imagen'));
        };
        
        img.src = url;
    });
}

function calculateDimensions(srcWidth, srcHeight, maxWidth, maxHeight) {
    let width = srcWidth;
    let height = srcHeight;
    
    if (width <= maxWidth && height <= maxHeight) {
        return { width, height };
    }
    
    const aspectRatio = width / height;
    
    if (width > height) {
        width = maxWidth;
        height = width / aspectRatio;
        
        if (height > maxHeight) {
            height = maxHeight;
            width = height * aspectRatio;
        }
    } else {
        height = maxHeight;
        width = height * aspectRatio;
        
        if (width > maxWidth) {
            width = maxWidth;
            height = width / aspectRatio;
        }
    }
    
    return {
        width: Math.round(width),
        height: Math.round(height)
    };
}

function resizeAndCompress(img, width, height, quality, outputFormat = 'image/jpeg') {
    return new Promise((resolve) => {
        const canvas = document.createElement('canvas');
        canvas.width = width;
        canvas.height = height;
        
        const ctx = canvas.getContext('2d');
        ctx.imageSmoothingEnabled = true;
        ctx.imageSmoothingQuality = 'high';
        
        // Si es PNG, limpiar canvas para mantener transparencia
        if (outputFormat === 'image/png') {
            ctx.clearRect(0, 0, width, height);
        }
        
        ctx.drawImage(img, 0, 0, width, height);
        
        canvas.toBlob(
            (blob) => resolve(blob),
            outputFormat,
            quality
        );
    });
}

// ==================== REMOVE.BG API ====================

async function removeBackground(file) {
    console.log('Enviando a remove.bg API...');
    
    const formData = new FormData();
    formData.append('image_file', file);
    formData.append('size', 'auto');
    
    try {
        const response = await fetch('https://api.remove.bg/v1.0/removebg', {
            method: 'POST',
            headers: {
                'X-Api-Key': REMOVEBG_API_KEY
            },
            body: formData
        });
        
        console.log('Respuesta remove.bg:', response.status);
        
        if (!response.ok) {
            const errorText = await response.text();
            console.error('Error remove.bg:', errorText);
            
            let errorMsg = 'Error desconocido';
            try {
                const error = JSON.parse(errorText);
                errorMsg = error.errors?.[0]?.title || error.errors?.[0]?.detail || errorText;
            } catch(e) {
                errorMsg = errorText;
            }
            
            throw new Error(errorMsg);
        }
        
        const blob = await response.blob();
        console.log('Fondo removido exitosamente:', (blob.size / 1024).toFixed(1) + 'KB');
        return blob;
        
    } catch (error) {
        console.error('Exception remove.bg:', error);
        throw error;
    }
}

// ==================== MANEJO DE PRODUCTOS (CON BOTÓN REMOVE.BG) ====================

async function handleProductImage(event) {
    const input = event.target;
    const file = input.files[0];
    
    if (!file) return;
    
    try {
        showNotification('Optimizando imagen...', 'info');
        
        // PASO 1: Optimizar primero (JPEG)
        const optimizedBlob = await validateAndOptimizeImage(file, 'producto', false);
        
        const optimizedFile = new File(
            [optimizedBlob], 
            file.name, 
            { type: 'image/jpeg' }
        );
        
        // Guardar en variable global
        optimizedProductImage = optimizedFile;
        
        // Reemplazar archivo en input
        const dataTransfer = new DataTransfer();
        dataTransfer.items.add(optimizedFile);
        input.files = dataTransfer.files;
        
        // Mostrar preview
        previewImage(optimizedFile, 'productImagePreview');
        
        const sizeKB = (optimizedBlob.size / 1024).toFixed(1);
        showNotification(
            `Imagen optimizada: ${sizeKB}KB. Ahora puedes remover el fondo si deseas.`, 
            'success'
        );
        
        // Mostrar botón de remove.bg
        showRemoveBgButton();
        
    } catch (error) {
        showNotification(error.message, 'error');
        input.value = '';
    }
}

async function executeRemoveBackground() {
    if (!optimizedProductImage) {
        showNotification('Primero selecciona una imagen', 'error');
        return;
    }
    
    const input = document.getElementById('productImage');
    const removeBgBtn = document.getElementById('removeBgBtn');
    const submitBtn = document.querySelector('#productForm button[type="submit"]');
    
    try {
        // Bloquear botones
        if (removeBgBtn) {
            removeBgBtn.disabled = true;
            removeBgBtn.textContent = 'Procesando...';
        }
        if (submitBtn) {
            submitBtn.disabled = true;
            submitBtn.textContent = 'Esperando procesamiento...';
        }
        
        showNotification('Removiendo fondo...', 'info');
        
        // PASO 2: Remover fondo de la imagen ya optimizada
        const noBgBlob = await removeBackground(optimizedProductImage);
        
        // Crear archivo PNG para mantener transparencia
        const noBgFile = new File(
            [noBgBlob], 
            optimizedProductImage.name.replace(/\.[^.]+$/, '.png'), 
            { type: 'image/png' }
        );
        
        // Actualizar variable global
        optimizedProductImage = noBgFile;
        
        // Reemplazar en input
        const dataTransfer = new DataTransfer();
        dataTransfer.items.add(noBgFile);
        input.files = dataTransfer.files;
        
        // Mostrar preview
        previewImage(noBgFile, 'productImagePreview');
        
        const sizeKB = (noBgBlob.size / 1024).toFixed(1);
        showNotification(
            `Fondo removido: ${sizeKB}KB (PNG con transparencia)`, 
            'success'
        );
        
        // Ocultar botón de remover fondo (ya se usó)
        if (removeBgBtn) removeBgBtn.style.display = 'none';
        
    } catch (error) {
        showNotification(`Error: ${error.message}`, 'error');
        
        // Restaurar botón de remover fondo en caso de error
        if (removeBgBtn) {
            removeBgBtn.disabled = false;
            removeBgBtn.textContent = '🎨 Remover Fondo (consume 1 crédito API)';
        }
    } finally {
        // Desbloquear botón de guardar
        if (submitBtn) {
            submitBtn.disabled = false;
            submitBtn.textContent = 'Guardar Producto';
        }
    }
}

// ==================== MANEJO DE USUARIOS Y CAROUSEL ====================

async function handleImageInput(event, imageType, previewId) {
    const input = event.target;
    const file = input.files[0];
    
    if (!file) return;
    
    try {
        showNotification('Optimizando imagen...', 'info');
        
        const optimizedBlob = await validateAndOptimizeImage(file, imageType, false);
        
        const finalFile = new File(
            [optimizedBlob], 
            file.name, 
            { type: 'image/jpeg' }
        );
        
        const dataTransfer = new DataTransfer();
        dataTransfer.items.add(finalFile);
        input.files = dataTransfer.files;
        
        if (document.getElementById(previewId)) {
            previewImage(finalFile, previewId);
        }
        
        const sizeKB = (finalFile.size / 1024).toFixed(1);
        showNotification(
            `Imagen ${imageType} optimizada: ${sizeKB}KB`, 
            'success'
        );
        
    } catch (error) {
        showNotification(error.message, 'error');
        input.value = '';
    }
}

function previewImage(file, previewElementId) {
    const reader = new FileReader();
    
    reader.onload = (e) => {
        const preview = document.getElementById(previewElementId);
        if (preview) {
            preview.src = e.target.result;
            preview.style.display = 'block';
        }
    };
    
    reader.readAsDataURL(file);
}

// ==================== INTEGRACIÓN CON INPUTS ====================

function initImageValidation() {
    // PRODUCTOS (con botón remove.bg)
    const productImageInput = document.getElementById('productImage');
    if (productImageInput) {
        productImageInput.addEventListener('change', async (e) => {
            optimizedProductImage = null; // Reset
            hideRemoveBgButton();
            await handleProductImage(e);
        });
    }
    
    // USUARIOS (sin remove.bg)
    const userPhotoInput = document.getElementById('userPhoto');
    if (userPhotoInput) {
        userPhotoInput.addEventListener('change', async (e) => {
            await handleImageInput(e, 'usuario', 'userPhotoPreview');
        });
    }
    
    // CAROUSEL (sin remove.bg)
    const slideImageInput = document.getElementById('slideImagen');
    if (slideImageInput) {
        slideImageInput.addEventListener('change', async (e) => {
            await handleImageInput(e, 'carousel', 'slideImagePreview');
        });
    }
}

// ==================== UI: BOTÓN REMOVE.BG ====================

function showRemoveBgButton() {
    let btn = document.getElementById('removeBgBtn');
    if (!btn) {
        const productImageInput = document.getElementById('productImage');
        if (!productImageInput) return;
        
        btn = document.createElement('button');
        btn.id = 'removeBgBtn';
        btn.type = 'button';
        btn.className = 'btn btn-secondary';
        btn.style.cssText = 'margin-top: 10px; width: 100%;';
        btn.innerHTML = '🎨 Remover Fondo (consume 1 crédito API)';
        btn.onclick = executeRemoveBackground;
        
        productImageInput.parentElement.appendChild(btn);
    }
    btn.style.display = 'block';
}

function hideRemoveBgButton() {
    const btn = document.getElementById('removeBgBtn');
    if (btn) btn.style.display = 'none';
}

// ==================== INICIALIZACIÓN ====================

document.addEventListener('DOMContentLoaded', function() {
    initImageValidation();
    createPreviewElements();
});

function createPreviewElements() {
    const configs = [
        { inputId: 'productImage', previewId: 'productImagePreview' },
        { inputId: 'userPhoto', previewId: 'userPhotoPreview' },
        { inputId: 'slideImagen', previewId: 'slideImagePreview' }
    ];
    
    configs.forEach(({inputId, previewId}) => {
        const input = document.getElementById(inputId);
        if (input && !document.getElementById(previewId)) {
            const preview = document.createElement('img');
            preview.id = previewId;
            preview.style.cssText = 'display:none; max-width:200px; margin-top:10px; border-radius:8px; border:2px solid #ddd;';
            input.parentElement.appendChild(preview);
        }
    });
}

console.log('Sistema de procesamiento de imágenes cargado');
console.log('Flujo: 1) Optimizar → 2) [Opcional] Remover fondo');