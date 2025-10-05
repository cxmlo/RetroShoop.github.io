<!DOCTYPE html>
<html lang="es">
<head>
    <?php include('../google.php'); ?>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Contacto - RETRO SHOP</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        html, body {
            width: 100%;
            height: 100%;
            margin: 0;
            padding: 0;
            overflow-x: hidden;
        }

        body {
            font-family: 'Courier New', monospace;
            background-color: #000;
            color: #fff;
            position: relative;
        }

        #topoCanvas {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            z-index: 0;
        }

        .overlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.3);
            z-index: 1;
            pointer-events: none;
        }

        .toggle-interaction {
            position: fixed;
            top: 20px;
            right: 20px;
            z-index: 100;
            padding: 0.8rem 1.5rem;
            background: rgba(0, 0, 0, 0.8);
            border: 2px solid rgba(255, 255, 255, 0.5);
            color: #fff;
            font-family: 'Courier New', monospace;
            font-size: 0.9rem;
            cursor: pointer;
            transition: all 0.3s;
            text-transform: uppercase;
            letter-spacing: 1px;
            backdrop-filter: blur(10px);
        }

        .toggle-interaction:hover {
            background: rgba(255, 255, 255, 0.2);
            border-color: #fff;
            transform: scale(1.05);
        }

        .toggle-interaction.active {
            background: rgba(255, 255, 255, 0.15);
            border-color: #fff;
            box-shadow: 0 0 20px rgba(255, 255, 255, 0.3);
        }

        .container {
            position: relative;
            z-index: 2;
            max-width: 1200px;
            margin: 0 auto;
            padding: 2rem 1rem;
            min-height: 100vh;
        }

        .back-btn {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.8rem 1.5rem;
            background: transparent;
            color: #fff;
            border: 2px solid #fff;
            text-decoration: none;
            font-family: 'Courier New', monospace;
            font-size: 0.9rem;
            text-transform: uppercase;
            letter-spacing: 1px;
            transition: all 0.3s;
            margin-bottom: 2rem;
        }

        .back-btn:hover {
            background: #fff;
            color: #000;
            transform: translateX(-5px);
        }

        .header {
            text-align: center;
            margin-bottom: 3rem;
            padding-bottom: 2rem;
            border-bottom: 3px solid #fff;
        }

        .header h1 {
            font-size: 3rem;
            letter-spacing: 8px;
            margin-bottom: 0.5rem;
            text-shadow: 0 0 10px rgba(255, 255, 255, 0.5);
            animation: glow 2s ease-in-out infinite alternate;
        }

        @keyframes glow {
            from {
                text-shadow: 0 0 10px #fff, 0 0 20px #fff;
            }
            to {
                text-shadow: 0 0 20px #fff, 0 0 30px #fff;
            }
        }

        .header p {
            color: rgba(255, 255, 255, 0.7);
            font-size: 1.1rem;
            letter-spacing: 2px;
        }

        .contact-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 2rem;
            margin-bottom: 3rem;
        }

        .contact-form {
            background: rgba(0, 0, 0, 0.85);
            border: 5px solid #fff;
            padding: 2.5rem;
            backdrop-filter: blur(10px);
            box-shadow: 0 0 50px rgba(255, 255, 255, 0.3);
        }

        .contact-form h2 {
            font-size: 1.8rem;
            letter-spacing: 3px;
            margin-bottom: 2rem;
            text-transform: uppercase;
        }

        .form-group {
            margin-bottom: 1.5rem;
        }

        .form-group label {
            display: block;
            margin-bottom: 0.5rem;
            font-weight: bold;
            text-transform: uppercase;
            letter-spacing: 2px;
            font-size: 0.85rem;
        }

        .form-group input,
        .form-group textarea {
            width: 100%;
            padding: 1rem;
            border: 3px solid #fff;
            background: rgba(0, 0, 0, 0.5);
            color: #fff;
            font-family: 'Courier New', monospace;
            font-size: 1rem;
            transition: all 0.3s;
        }

        .form-group input::placeholder,
        .form-group textarea::placeholder {
            color: rgba(255, 255, 255, 0.5);
        }

        .form-group input:focus,
        .form-group textarea:focus {
            outline: none;
            box-shadow: 0 0 15px rgba(255, 255, 255, 0.5);
            background: rgba(255, 255, 255, 0.1);
        }

        .form-group textarea {
            resize: vertical;
            min-height: 150px;
        }

        .btn-submit {
            width: 100%;
            padding: 1.2rem;
            background: transparent;
            color: #fff;
            border: 3px solid #fff;
            font-family: 'Courier New', monospace;
            font-size: 1.2rem;
            font-weight: bold;
            letter-spacing: 2px;
            text-transform: uppercase;
            cursor: pointer;
            transition: all 0.3s;
            position: relative;
            overflow: hidden;
        }

        .btn-submit::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: rgba(255, 255, 255, 0.2);
            transition: left 0.3s;
        }

        .btn-submit:hover::before {
            left: 100%;
        }

        .btn-submit:hover {
            background: #fff;
            color: #000;
            transform: translateY(-3px);
            box-shadow: 0 10px 20px rgba(255, 255, 255, 0.3);
        }

        .btn-submit:disabled {
            opacity: 0.5;
            cursor: not-allowed;
        }

        .contact-info {
            background: rgba(0, 0, 0, 0.85);
            border: 5px solid #fff;
            padding: 2.5rem;
            backdrop-filter: blur(10px);
            box-shadow: 0 0 50px rgba(255, 255, 255, 0.3);
        }

        .contact-info h2 {
            font-size: 1.8rem;
            letter-spacing: 3px;
            margin-bottom: 2rem;
            text-transform: uppercase;
        }

        .info-item {
            display: flex;
            align-items: flex-start;
            gap: 1rem;
            margin-bottom: 2rem;
            padding-bottom: 2rem;
            border-bottom: 2px solid rgba(255, 255, 255, 0.2);
        }

        .info-item:last-child {
            border-bottom: none;
            margin-bottom: 0;
            padding-bottom: 0;
        }

        .info-item i {
            font-size: 1.5rem;
            margin-top: 0.3rem;
            min-width: 30px;
        }

        .info-content h3 {
            font-size: 1.1rem;
            letter-spacing: 2px;
            margin-bottom: 0.5rem;
            text-transform: uppercase;
        }

        .info-content p {
            color: rgba(255, 255, 255, 0.8);
            line-height: 1.6;
        }

        .info-content a {
            color: #fff;
            text-decoration: none;
            transition: all 0.3s;
        }

        .info-content a:hover {
            text-shadow: 0 0 10px rgba(255, 255, 255, 0.8);
        }

        .social-links {
            display: flex;
            gap: 1rem;
            margin-top: 1rem;
        }

        .social-links a {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 45px;
            height: 45px;
            border: 2px solid #fff;
            color: #fff;
            font-size: 1.3rem;
            transition: all 0.3s;
        }

        .social-links a:hover {
            background: #fff;
            color: #000;
            transform: scale(1.1);
        }

        .notification {
            position: fixed;
            top: 80px;
            right: 20px;
            padding: 1rem 1.5rem;
            background: rgba(0, 0, 0, 0.9);
            border: 2px solid #fff;
            color: #fff;
            font-family: 'Courier New', monospace;
            z-index: 10000;
            animation: slideInRight 0.3s ease;
        }

        .notification.success {
            border-color: #4ade80;
        }

        .notification.error {
            border-color: #f87171;
        }

        @keyframes slideInRight {
            from {
                opacity: 0;
                transform: translateX(400px);
            }
            to {
                opacity: 1;
                transform: translateX(0);
            }
        }

        @media (max-width: 768px) {
            .toggle-interaction {
                top: 10px;
                right: 10px;
                padding: 0.6rem 1rem;
                font-size: 0.75rem;
            }

            .header h1 {
                font-size: 2rem;
                letter-spacing: 4px;
            }

            .contact-grid {
                grid-template-columns: 1fr;
            }

            .contact-form,
            .contact-info {
                padding: 2rem 1.5rem;
                border-width: 3px;
            }

            .social-links {
                flex-wrap: wrap;
            }
        }
    </style>
</head>
<body>
    <canvas id="topoCanvas"></canvas>
    
    <button class="toggle-interaction active" id="toggleBtn">
        Interacción: ON
    </button>
    
    <div class="overlay"></div>

    <div class="container">
        <a href="cliente.php" class="back-btn">
            <i class="fas fa-arrow-left"></i>
            Volver al Inicio
        </a>

        <div class="header">
            <h1>CONTÁCTANOS</h1>
            <p>Estamos aquí para ayudarte</p>
        </div>

        <div class="contact-grid">
            <div class="contact-form">
                <h2>Envíanos un Mensaje</h2>
                <form id="contactForm">
                    <div class="form-group">
                        <label for="name">
                            <i class="fas fa-user"></i> Nombre
                        </label>
                        <input 
                            type="text" 
                            id="name" 
                            name="name" 
                            placeholder="Tu nombre completo"
                            required
                        >
                    </div>

                    <div class="form-group">
                        <label for="email">
                            <i class="fas fa-envelope"></i> Correo Electrónico
                        </label>
                        <input 
                            type="email" 
                            id="email" 
                            name="email" 
                            placeholder="tu@email.com"
                            required
                        >
                    </div>

                    <div class="form-group">
                        <label for="subject">
                            <i class="fas fa-tag"></i> Asunto
                        </label>
                        <input 
                            type="text" 
                            id="subject" 
                            name="subject" 
                            placeholder="¿En qué podemos ayudarte?"
                            required
                        >
                    </div>

                    <div class="form-group">
                        <label for="message">
                            <i class="fas fa-comment"></i> Mensaje
                        </label>
                        <textarea 
                            id="message" 
                            name="message" 
                            placeholder="Escribe tu mensaje aquí..."
                            required
                        ></textarea>
                    </div>

                    <button type="submit" class="btn-submit" id="submitBtn">
                        <i class="fas fa-paper-plane"></i> ENVIAR MENSAJE
                    </button>
                </form>
            </div>

            <div class="contact-info">
                <h2>Información de Contacto</h2>

                <div class="info-item">
                    <i class="fas fa-map-marker-alt"></i>
                    <div class="info-content">
                        <h3>Dirección</h3>
                        <p>Calle Retro #123<br>Bucaramanga, Santander<br>Colombia</p>
                    </div>
                </div>

                <div class="info-item">
                    <i class="fas fa-phone"></i>
                    <div class="info-content">
                        <h3>Teléfono</h3>
                        <p><a href="tel:+573001234567">+57 300 123 4567</a></p>
                    </div>
                </div>

                <div class="info-item">
                    <i class="fas fa-envelope"></i>
                    <div class="info-content">
                        <h3>Email</h3>
                        <p><a href="mailto:info@retroshop.com">info@retroshop.com</a></p>
                    </div>
                </div>

                <div class="info-item">
                    <i class="fas fa-clock"></i>
                    <div class="info-content">
                        <h3>Horario de Atención</h3>
                        <p>Lunes a Viernes: 9:00 AM - 6:00 PM<br>Sábados: 10:00 AM - 4:00 PM<br>Domingos: Cerrado</p>
                    </div>
                </div>

                <div class="info-item">
                    <i class="fas fa-share-alt"></i>
                    <div class="info-content">
                        <h3>Síguenos</h3>
                        <div class="social-links">
                            <a href="#" title="Facebook"><i class="fab fa-facebook-f"></i></a>
                            <a href="#" title="Instagram"><i class="fab fa-instagram"></i></a>
                            <a href="#" title="Twitter"><i class="fab fa-twitter"></i></a>
                            <a href="#" title="TikTok"><i class="fab fa-tiktok"></i></a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- EmailJS SDK -->
    <script src="https://cdn.jsdelivr.net/npm/@emailjs/browser@3/dist/email.min.js"></script>
    
    <script>
        // CONFIGURAR EMAILJS
        // 1. Regístrate en https://www.emailjs.com/
        // 2. Obtén tu Public Key
        // 3. Crea un servicio de email
        // 4. Crea una plantilla
        // 5. Reemplaza estos valores:
        
        const EMAILJS_PUBLIC_KEY = 'u9OWUT4r8Ied-BXzY';
        const EMAILJS_SERVICE_ID = 'service_273haqd';
        const EMAILJS_TEMPLATE_ID = 'template_3iy4xwb';

        emailjs.init(EMAILJS_PUBLIC_KEY);

        // Formulario de contacto
        const form = document.getElementById('contactForm');
        const submitBtn = document.getElementById('submitBtn');

        form.addEventListener('submit', async (e) => {
            e.preventDefault();

            const formData = {
                name: document.getElementById('name').value,
                email: document.getElementById('email').value,
                subject: document.getElementById('subject').value,
                message: document.getElementById('message').value
            };

            submitBtn.disabled = true;
            submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> ENVIANDO...';

            try {
                await emailjs.send(EMAILJS_SERVICE_ID, EMAILJS_TEMPLATE_ID, formData);
                
                showNotification('Mensaje enviado correctamente', 'success');
                form.reset();
            } catch (error) {
                console.error('Error:', error);
                showNotification('Error al enviar el mensaje. Inténtalo de nuevo.', 'error');
            } finally {
                submitBtn.disabled = false;
                submitBtn.innerHTML = '<i class="fas fa-paper-plane"></i> ENVIAR MENSAJE';
            }
        });

        function showNotification(message, type) {
            const notification = document.createElement('div');
            notification.className = `notification ${type}`;
            notification.textContent = message;
            document.body.appendChild(notification);

            setTimeout(() => {
                notification.remove();
            }, 4000);
        }

        // Fondo topográfico (mismo código que antes)
        const canvas = document.getElementById('topoCanvas');
        const ctx = canvas.getContext('2d');
        let width, height, gridSize = 20, cols, rows, time = 0;
        let mouseX = -1000, mouseY = -1000, mouseInfluence = [];
        let interactionEnabled = true, lastFrame = 0;
        const targetFPS = 30, frameDelay = 1000 / targetFPS;

        function noise(x, y, z = 0) {
            const X = Math.floor(x) & 255, Y = Math.floor(y) & 255, Z = Math.floor(z) & 255;
            x -= Math.floor(x); y -= Math.floor(y); z -= Math.floor(z);
            const u = fade(x), v = fade(y), w = fade(z);
            const A = p[X] + Y, AA = p[A] + Z, AB = p[A + 1] + Z;
            const B = p[X + 1] + Y, BA = p[B] + Z, BB = p[B + 1] + Z;
            return lerp(w, lerp(v, lerp(u, grad(p[AA], x, y, z), grad(p[BA], x - 1, y, z)),
                lerp(u, grad(p[AB], x, y - 1, z), grad(p[BB], x - 1, y - 1, z))),
                lerp(v, lerp(u, grad(p[AA + 1], x, y, z - 1), grad(p[BA + 1], x - 1, y, z - 1)),
                lerp(u, grad(p[AB + 1], x, y - 1, z - 1), grad(p[BB + 1], x - 1, y - 1, z - 1))));
        }

        function fade(t) { return t * t * t * (t * (t * 6 - 15) + 10); }
        function lerp(t, a, b) { return a + t * (b - a); }
        function grad(hash, x, y, z) {
            const h = hash & 15, u = h < 8 ? x : y;
            const v = h < 4 ? y : h === 12 || h === 14 ? x : z;
            return ((h & 1) === 0 ? u : -u) + ((h & 2) === 0 ? v : -v);
        }

        const p = new Array(512);
        const permutation = [151,160,137,91,90,15,131,13,201,95,96,53,194,233,7,225,140,36,103,30,69,142,8,99,37,240,21,10,23,190,6,148,247,120,234,75,0,26,197,62,94,252,219,203,117,35,11,32,57,177,33,88,237,149,56,87,174,20,125,136,171,168,68,175,74,165,71,134,139,48,27,166,77,146,158,231,83,111,229,122,60,211,133,230,220,105,92,41,55,46,245,40,244,102,143,54,65,25,63,161,1,216,80,73,209,76,132,187,208,89,18,169,200,196,135,130,116,188,159,86,164,100,109,198,173,186,3,64,52,217,226,250,124,123,5,202,38,147,118,126,255,82,85,212,207,206,59,227,47,16,58,17,182,189,28,42,223,183,170,213,119,248,152,2,44,154,163,70,221,153,101,155,167,43,172,9,129,22,39,253,19,98,108,110,79,113,224,232,178,185,112,104,218,246,97,228,251,34,242,193,238,210,144,12,191,179,162,241,81,51,145,235,249,14,239,107,49,192,214,31,181,199,106,157,184,84,204,176,115,121,50,45,127,4,150,254,138,236,205,93,222,114,67,29,24,72,243,141,128,195,78,66,215,61,156,180];
        for (let i = 0; i < 256; i++) p[256 + i] = p[i] = permutation[i];

        function resize() {
            width = window.innerWidth; height = window.innerHeight;
            canvas.width = width; canvas.height = height;
            cols = Math.ceil(width / gridSize) + 1; rows = Math.ceil(height / gridSize) + 1;
            mouseInfluence = Array(rows).fill(0).map(() => Array(cols).fill(0));
        }

        function getHeight(x, y, t) {
            let value = 0, amplitude = 1, frequency = 0.01;
            for (let i = 0; i < 2; i++) {
                value += noise(x * frequency, y * frequency, t) * amplitude;
                amplitude *= 0.5; frequency *= 2;
            }
            const gridX = Math.floor(x / gridSize), gridY = Math.floor(y / gridSize);
            if (gridY >= 0 && gridY < rows && gridX >= 0 && gridX < cols) value += mouseInfluence[gridY][gridX];
            return value * 100;
        }

        function updateMouseInfluence() {
            for (let i = 0; i < rows; i++) {
                for (let j = 0; j < cols; j++) {
                    mouseInfluence[i][j] *= 0.96;
                    if (mouseInfluence[i][j] < 0.001) mouseInfluence[i][j] = 0;
                }
            }
            if (!interactionEnabled) return;
            const gridX = Math.floor(mouseX / gridSize), gridY = Math.floor(mouseY / gridSize), radius = 8;
            for (let i = -radius; i <= radius; i++) {
                for (let j = -radius; j <= radius; j++) {
                    const y = gridY + i, x = gridX + j;
                    if (y >= 0 && y < rows && x >= 0 && x < cols) {
                        const dist = Math.sqrt(i * i + j * j);
                        if (dist <= radius) {
                            const influence = (1 - dist / radius) * 0.5;
                            mouseInfluence[y][x] += influence;
                            mouseInfluence[y][x] = Math.min(mouseInfluence[y][x], 2);
                        }
                    }
                }
            }
        }

        function drawContourLines() {
            ctx.clearRect(0, 0, width, height);
            const levels = 12, step = 200 / levels;
            for (let level = 0; level < levels; level++) {
                const threshold = -100 + level * step, isThickLine = level % 3 === 0;
                ctx.strokeStyle = isThickLine ? 'rgba(237, 237, 237, 0.6)' : 'rgba(237, 237, 237, 0.3)';
                ctx.lineWidth = isThickLine ? 2 : 1;
                ctx.beginPath();
                for (let y = 0; y < rows - 1; y++) {
                    for (let x = 0; x < cols - 1; x++) {
                        const x0 = x * gridSize, y0 = y * gridSize;
                        const v1 = getHeight(x0, y0, time), v2 = getHeight(x0 + gridSize, y0, time);
                        const v3 = getHeight(x0 + gridSize, y0 + gridSize, time), v4 = getHeight(x0, y0 + gridSize, time);
                        let cellType = 0;
                        if (v1 > threshold) cellType |= 8;
                        if (v2 > threshold) cellType |= 4;
                        if (v3 > threshold) cellType |= 2;
                        if (v4 > threshold) cellType |= 1;
                        if (cellType === 0 || cellType === 15) continue;
                        const interp = (v1, v2, x1, y1, x2, y2) => {
                            const t = (threshold - v1) / (v2 - v1);
                            return { x: x1 + t * (x2 - x1), y: y1 + t * (y2 - y1) };
                        };
                        const top = interp(v1, v2, x0, y0, x0 + gridSize, y0);
                        const right = interp(v2, v3, x0 + gridSize, y0, x0 + gridSize, y0 + gridSize);
                        const bottom = interp(v4, v3, x0, y0 + gridSize, x0 + gridSize, y0 + gridSize);
                        const left = interp(v1, v4, x0, y0, x0, y0 + gridSize);
                        switch (cellType) {
                            case 1: case 14: ctx.moveTo(left.x, left.y); ctx.lineTo(bottom.x, bottom.y); break;
                            case 2: case 13: ctx.moveTo(bottom.x, bottom.y); ctx.lineTo(right.x, right.y); break;
                            case 3: case 12: ctx.moveTo(left.x, left.y); ctx.lineTo(right.x, right.y); break;
                            case 4: case 11: ctx.moveTo(top.x, top.y); ctx.lineTo(right.x, right.y); break;
                            case 5: ctx.moveTo(left.x, left.y); ctx.lineTo(top.x, top.y); ctx.moveTo(bottom.x, bottom.y); ctx.lineTo(right.x, right.y); break;
                            case 6: case 9: ctx.moveTo(top.x, top.y); ctx.lineTo(bottom.x, bottom.y); break;
                            case 7: case 8: ctx.moveTo(left.x, left.y); ctx.lineTo(top.x, top.y); break;
                            case 10: ctx.moveTo(top.x, top.y); ctx.lineTo(right.x, right.y); ctx.moveTo(left.x, left.y); ctx.lineTo(bottom.x, bottom.y); break;
                        }
                    }
                }
                ctx.stroke();
            }
        }

        function animate(timestamp) {
            if (timestamp - lastFrame < frameDelay) {
                requestAnimationFrame(animate);
                return;
            }
            lastFrame = timestamp;
            time += 0.002;
            updateMouseInfluence();
            drawContourLines();
            requestAnimationFrame(animate);
        }

        document.getElementById('toggleBtn').addEventListener('click', function() {
            interactionEnabled = !interactionEnabled;
            this.classList.toggle('active');
            this.textContent = interactionEnabled ? 'Interacción: ON' : 'Interacción: OFF';
            if (!interactionEnabled) { mouseX = -1000; mouseY = -1000; }
        });

        canvas.addEventListener('mousemove', (e) => {
            if (interactionEnabled) { mouseX = e.clientX; mouseY = e.clientY; }
        });
        canvas.addEventListener('mouseleave', () => { mouseX = -1000; mouseY = -1000; });
        window.addEventListener('resize', resize);

        resize();
        requestAnimationFrame(animate);
    </script>
</body>
</html>