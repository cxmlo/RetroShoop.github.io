// ==================== PHOTO CROPPER CON CROPPER.JS ====================

class ProfilePhotoCropper {
    constructor() {
        this.modal = document.getElementById('cropModal');
        this.image = document.getElementById('cropImage');
        this.cropper = null;
        
        this.initEvents();
    }

    initEvents() {
        const applyBtn = document.getElementById('cropApply');
        const cancelBtn = document.getElementById('cropCancel');
        const rotateLeftBtn = document.getElementById('rotateLeft');
        const rotateRightBtn = document.getElementById('rotateRight');
        const zoomInBtn = document.getElementById('zoomIn');
        const zoomOutBtn = document.getElementById('zoomOut');

        if (applyBtn) applyBtn.addEventListener('click', () => this.applyCrop());
        if (cancelBtn) cancelBtn.addEventListener('click', () => this.close());
        if (rotateLeftBtn) rotateLeftBtn.addEventListener('click', () => this.cropper && this.cropper.rotate(-90));
        if (rotateRightBtn) rotateRightBtn.addEventListener('click', () => this.cropper && this.cropper.rotate(90));
        if (zoomInBtn) zoomInBtn.addEventListener('click', () => this.cropper && this.cropper.zoom(0.1));
        if (zoomOutBtn) zoomOutBtn.addEventListener('click', () => this.cropper && this.cropper.zoom(-0.1));
    }

    open(fileOrBlob) {
        return new Promise((resolve, reject) => {
            if (fileOrBlob instanceof Blob) {
                const url = URL.createObjectURL(fileOrBlob);
                this.loadImage(url, resolve, reject);
            } else {
                const reader = new FileReader();
                reader.onload = (e) => {
                    this.loadImage(e.target.result, resolve, reject);
                };
                reader.readAsDataURL(fileOrBlob);
            }
        });
    }

    loadImage(src, resolve, reject) {
        this.image.src = src;
        this.modal.classList.add('active');
        
        if (this.cropper) {
            this.cropper.destroy();
        }
        
        this.cropper = new Cropper(this.image, {
            aspectRatio: 1,
            viewMode: 1,
            dragMode: 'move',
            autoCropArea: 0.8,
            restore: false,
            guides: false,
            center: true,
            highlight: false,
            cropBoxMovable: false,
            cropBoxResizable: false,
            toggleDragModeOnDblclick: false,
            responsive: true,
            background: false
        });
        
        this.resolve = resolve;
        this.reject = reject;
    }

    async applyCrop() {
        console.log('🔵 applyCrop ejecutándose');
        
        if (!this.cropper) {
            console.log('❌ No hay cropper');
            return;
        }
        
        console.log('🔵 Obteniendo canvas...');
        
        const canvas = this.cropper.getCroppedCanvas({
            width: 400,
            height: 400,
            imageSmoothingEnabled: true,
            imageSmoothingQuality: 'high'
        });
        
        console.log('🔵 Convirtiendo a blob...');
        
        const blob = await new Promise(resolve => {
            canvas.toBlob(resolve, 'image/jpeg', 0.85);
        });
        
        console.log('🔵 Blob creado:', blob.size);
        
        // Cerrar modal y destruir cropper
        if (this.cropper) {
            this.cropper.destroy();
            this.cropper = null;
        }
        this.modal.classList.remove('active');
        
        // Resolver promesa con el blob (NO rechazar)
        console.log('🔵 Resolviendo promesa');
        if (this.resolve) {
            this.resolve(blob);
            this.resolve = null;
            this.reject = null;
        }
    }

    close() {
        console.log('🔵 close() - Cancelando');
        
        if (this.cropper) {
            this.cropper.destroy();
            this.cropper = null;
        }
        this.modal.classList.remove('active');
        
        if (this.reject) {
            this.reject('Cancelled');
            this.resolve = null;
            this.reject = null;
        }
    }
}

window.profileCropper = new ProfilePhotoCropper();
console.log('Cropper.js cargado correctamente');