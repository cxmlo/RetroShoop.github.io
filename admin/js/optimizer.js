// ==================== CONFIGURACIONES POR TIPO DE IMAGEN ====================

const IMAGE_CONFIGS = {
    producto: {
        maxSizeKB: 500,
        maxWidth: 800,
        maxHeight: 800,
        quality: 0.85,
        allowedTypes: ['image/jpeg', 'image/jpg', 'image/png', 'image/webp']
    },
    usuario: {
        maxSizeKB: 300,
        maxWidth: 400,
        maxHeight: 400,
        quality: 0.80,
        allowedTypes: ['image/jpeg', 'image/jpg', 'image/png']
    },
    carousel: {
        maxSizeKB: 800,          // Mayor tamaño para carousel
        maxWidth: 1920,          // Ancho completo
        maxHeight: 600,          // Altura para banner
        quality: 0.90,           // Mejor calidad
        allowedTypes: ['image/jpeg', 'image/jpg', 'image/png', 'image/webp']
    }
};

// ==================== VALIDACIÓN Y OPTIMIZACIÓN ====================

/**
 * Valida y optimiza imagen según el tipo
 * @param {File} file - Archivo de imagen
 * @param {string} type - Tipo: 'producto', 'usuario', 'carousel'
 * @returns {Promise<Blob>} - Imagen optimizada
 */
async function validateAndOptimizeImage(file, type = 'producto') {
    const config = IMAGE_CONFIGS[type];
    
    if (!config) {
        throw new Error('Tipo de imagen no válido');
    }
    
    // 1. Validar tipo de archivo
    if (!config.allowedTypes.includes(file.type)) {
        throw new Error(`Formato no válido. Use: ${config.allowedTypes.join(', ')}`);
    }
    
    // 2. Validar tamaño inicial (máximo 10MB sin procesar)
    if (file.size > 20 * 1024 * 1024) {
        throw new Error('Imagen muy grande (máximo 20MB)');
    }
    
    // 3. Cargar imagen
    const img = await loadImage(file);
    
    // 4. Calcular nuevas dimensiones manteniendo aspecto
    const { width, height } = calculateDimensions(
        img.width, 
        img.height, 
        config.maxWidth, 
        config.maxHeight
    );
    
    // 5. Redimensionar y comprimir
    let optimizedBlob = await resizeAndCompress(img, width, height, config.quality);
    
    // 6. Verificar tamaño final
    if (optimizedBlob.size > config.maxSizeKB * 1024) {
        // Reducir calidad gradualmente hasta cumplir límite
        let quality = config.quality - 0.1;
        while (optimizedBlob.size > config.maxSizeKB * 1024 && quality > 0.5) {
            optimizedBlob = await resizeAndCompress(img, width, height, quality);
            quality -= 0.1;
        }
    }
    
    return optimizedBlob;
}

/**
 * Carga una imagen desde un File
 */
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

/**
 * Calcula dimensiones manteniendo proporción
 */
function calculateDimensions(srcWidth, srcHeight, maxWidth, maxHeight) {
    let width = srcWidth;
    let height = srcHeight;
    
    // Si la imagen es más pequeña que los límites, mantener tamaño original
    if (width <= maxWidth && height <= maxHeight) {
        return { width, height };
    }
    
    // Calcular escala manteniendo aspecto
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

/**
 * Redimensiona y comprime usando Canvas
 */
function resizeAndCompress(img, width, height, quality) {
    return new Promise((resolve) => {
        const canvas = document.createElement('canvas');
        canvas.width = width;
        canvas.height = height;
        
        const ctx = canvas.getContext('2d');
        
        // Mejorar calidad de redimensionamiento
        ctx.imageSmoothingEnabled = true;
        ctx.imageSmoothingQuality = 'high';
        
        // Dibujar imagen redimensionada
        ctx.drawImage(img, 0, 0, width, height);
        
        // Convertir a Blob (JPEG para mejor compresión)
        canvas.toBlob(
            (blob) => resolve(blob),
            'image/jpeg',
            quality
        );
    });
}

/**
 * Preview de imagen antes de subir
 */
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

// ==================== INTEGRACIÓN CON FORMULARIOS ====================

/**
 * Inicializar validación en inputs de archivos
 */
function initImageValidation() {
    // Para PRODUCTOS
    const productImageInput = document.getElementById('productImage');
    if (productImageInput) {
        productImageInput.addEventListener('change', async (e) => {
            await handleImageInput(e, 'producto', 'productImagePreview');
        });
    }
    
    // Para USUARIOS
    const userPhotoInput = document.getElementById('userPhoto');
    if (userPhotoInput) {
        userPhotoInput.addEventListener('change', async (e) => {
            await handleImageInput(e, 'usuario', 'userPhotoPreview');
        });
    }
    
    // Para CAROUSEL
    const slideImageInput = document.getElementById('slideImagen');
    if (slideImageInput) {
        slideImageInput.addEventListener('change', async (e) => {
            await handleImageInput(e, 'carousel', 'slideImagePreview');
        });
    }
}

/**
 * Maneja la selección de imagen con configuración específica
 */
async function handleImageInput(event, imageType, previewId) {
    const input = event.target;
    const file = input.files[0];
    
    if (!file) return;
    
    const config = IMAGE_CONFIGS[imageType];
    
    try {
        // Mostrar loading
        showNotification('Procesando imagen...', 'info');
        
        // Validar y optimizar con configuración específica
        const optimizedBlob = await validateAndOptimizeImage(file, imageType);
        
        // Crear nuevo File desde Blob
        const optimizedFile = new File(
            [optimizedBlob], 
            file.name, 
            { type: 'image/jpeg' }
        );
        
        // Reemplazar archivo en input
        const dataTransfer = new DataTransfer();
        dataTransfer.items.add(optimizedFile);
        input.files = dataTransfer.files;
        
        // Mostrar preview si existe el elemento
        if (document.getElementById(previewId)) {
            previewImage(optimizedFile, previewId);
        }
        
        // Mostrar info con detalles de configuración
        const sizeKB = (optimizedBlob.size / 1024).toFixed(1);
        const maxKB = config.maxSizeKB;
        const dimensions = `${config.maxWidth}x${config.maxHeight}`;
        
        showNotification(
            `✅ Imagen ${imageType} optimizada: ${sizeKB}KB de ${maxKB}KB (${dimensions}px)`, 
            'success'
        );
        
    } catch (error) {
        showNotification(error.message, 'error');
        input.value = ''; // Limpiar input
    }
}

// ==================== INICIALIZAR AL CARGAR ====================

// Añadir al evento DOMContentLoaded existente
document.addEventListener('DOMContentLoaded', function() {
    initImageValidation();
    
    // Crear elementos de preview si no existen
    createPreviewElements();
    
    // El resto de tus inicializaciones existentes...
    // initNavigation();
    // initBurgerMenu();
    // initModals();
    // initForms();
});

/**
 * Crear elementos de preview dinámicamente
 */
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

// ==================== UTILIDADES ADICIONALES ====================

/**
 * Obtener información de imagen
 */
async function getImageInfo(file) {
    const img = await loadImage(file);
    return {
        width: img.width,
        height: img.height,
        sizeKB: (file.size / 1024).toFixed(2),
        type: file.type
    };
}

/**
 * Validar dimensiones mínimas para carousel
 */
async function validateCarouselDimensions(file) {
    const img = await loadImage(file);
    const minWidth = 1200;
    const minHeight = 400;
    
    if (img.width < minWidth || img.height < minHeight) {
        throw new Error(`Imagen muy pequeña para carousel. Mínimo: ${minWidth}x${minHeight}px`);
    }
    
    return true;
}

console.log('✅ Sistema de optimización de imágenes cargado');
console.log('📦 Configuraciones:', IMAGE_CONFIGS);