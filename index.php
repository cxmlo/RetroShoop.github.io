<?php
session_start();

// Si ya está logueado, redirigir directamente
if(isset($_SESSION['correo'])) {
    if($_SESSION['nivelusuario'] == 1) {
        header('location: admin/admin.php');
    } else {
        header('location: cliente/cliente.php');
    }
    exit;
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <?php include('google.php'); ?>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bienvenido - RetroVibes</title>
    <link rel="shortcut icon" href="img/gato.gif" />
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
            overflow: hidden;
        }

        body {
            font-family: 'Courier New', monospace;
            background-color: #000;
            display: flex;
            justify-content: center;
            align-items: center;
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
            z-index: 10;
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
            background: rgba(118, 75, 162, 0.8);
            border-color: rgba(118, 75, 162, 1);
            box-shadow: 0 0 20px rgba(118, 75, 162, 0.5);
        }

        .container {
            position: relative;
            z-index: 2;
            text-align: center;
            color: white;
            background: rgba(0, 0, 0, 0.85);
            border: 5px solid #fff;
            padding: 4rem 3rem;
            max-width: 600px;
            margin: 1rem;
            box-shadow: 0 0 50px rgba(255, 255, 255, 0.3);
            animation: slideIn 1s ease;
            backdrop-filter: blur(10px);
            pointer-events: all;
        }

        @keyframes slideIn {
            from {
                opacity: 0;
                transform: translateY(-50px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .logo {
            font-size: 4rem;
            letter-spacing: 8px;
            margin-bottom: 1rem;
            text-shadow: 4px 4px 0 rgba(0, 0, 0, 0.5);
            animation: glow 2s ease-in-out infinite alternate;
        }

        @keyframes glow {
            from {
                text-shadow: 0 0 10px #fff, 0 0 20px #fff, 0 0 30px #667eea;
            }
            to {
                text-shadow: 0 0 20px #fff, 0 0 30px #764ba2, 0 0 40px #764ba2;
            }
        }

        .subtitle {
            font-size: 1.3rem;
            letter-spacing: 3px;
            margin-bottom: 3rem;
            opacity: 0.9;
        }

        .buttons {
            display: flex;
            gap: 1.5rem;
            justify-content: center;
            flex-wrap: wrap;
        }

        .btn {
            display: inline-block;
            padding: 1.2rem 2.5rem;
            font-size: 1.2rem;
            font-family: 'Courier New', monospace;
            font-weight: bold;
            text-transform: uppercase;
            letter-spacing: 2px;
            border: 3px solid #fff;
            cursor: pointer;
            transition: all 0.3s;
            text-decoration: none;
            position: relative;
            overflow: hidden;
        }

        .btn::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: rgba(255, 255, 255, 0.2);
            transition: left 0.3s;
        }

        .btn:hover::before {
            left: 100%;
        }

        .btn-login {
            background: transparent;
            color: #fff;
        }

        .btn-login:hover {
            background: #fff;
            color: #000;
            transform: translateY(-5px);
            box-shadow: 0 10px 20px rgba(255, 255, 255, 0.3);
        }

        .btn-register {
            background: #fff;
            color: #000;
        }

        .btn-register:hover {
            background: transparent;
            color: #fff;
            transform: translateY(-5px);
            box-shadow: 0 10px 20px rgba(255, 255, 255, 0.3);
        }

        @media (max-width: 768px) {
            .toggle-interaction {
                top: 10px;
                right: 10px;
                padding: 0.6rem 1rem;
                font-size: 0.75rem;
            }

            .container {
                padding: 3rem 2rem;
                margin: 1rem;
                max-width: 90%;
            }

            .logo {
                font-size: 2.5rem;
                letter-spacing: 4px;
            }

            .subtitle {
                font-size: 1rem;
                letter-spacing: 2px;
            }

            .buttons {
                flex-direction: column;
                gap: 1rem;
            }

            .btn {
                width: 100%;
                padding: 1rem 2rem;
                font-size: 1rem;
            }
        }

        @media (max-width: 480px) {
            .container {
                padding: 2rem 1.5rem;
                border-width: 3px;
            }

            .logo {
                font-size: 2rem;
                letter-spacing: 3px;
            }

            .subtitle {
                font-size: 0.9rem;
                margin-bottom: 2rem;
            }

            .btn {
                padding: 0.9rem 1.5rem;
                font-size: 0.9rem;
                letter-spacing: 1px;
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
        <h1 class="logo">RETRO SHOP</h1>
        <p class="subtitle">Tu tienda de moda vintage</p>
        
        <div class="buttons">
            <a href="login.php" class="btn btn-login">Iniciar Sesión</a>
            <a href="register.php" class="btn btn-register">Registrarse</a>
        </div>
    </div>

    <script>
        const canvas = document.getElementById('topoCanvas');
        const ctx = canvas.getContext('2d');

        let width, height;
        let gridSize = 20; // Optimizado
        let cols, rows;
        let time = 0;
        let mouseX = -1000;
        let mouseY = -1000;
        let mouseInfluence = [];
        let interactionEnabled = true;
        let lastFrame = 0;
        const targetFPS = 30;
        const frameDelay = 1000 / targetFPS;

        // Función de ruido Perlin simplificada
        function noise(x, y, z = 0) {
            const X = Math.floor(x) & 255;
            const Y = Math.floor(y) & 255;
            const Z = Math.floor(z) & 255;
            
            x -= Math.floor(x);
            y -= Math.floor(y);
            z -= Math.floor(z);
            
            const u = fade(x);
            const v = fade(y);
            const w = fade(z);
            
            const A = p[X] + Y;
            const AA = p[A] + Z;
            const AB = p[A + 1] + Z;
            const B = p[X + 1] + Y;
            const BA = p[B] + Z;
            const BB = p[B + 1] + Z;
            
            return lerp(w, 
                lerp(v, 
                    lerp(u, grad(p[AA], x, y, z), grad(p[BA], x - 1, y, z)),
                    lerp(u, grad(p[AB], x, y - 1, z), grad(p[BB], x - 1, y - 1, z))
                ),
                lerp(v, 
                    lerp(u, grad(p[AA + 1], x, y, z - 1), grad(p[BA + 1], x - 1, y, z - 1)),
                    lerp(u, grad(p[AB + 1], x, y - 1, z - 1), grad(p[BB + 1], x - 1, y - 1, z - 1))
                )
            );
        }

        function fade(t) {
            return t * t * t * (t * (t * 6 - 15) + 10);
        }

        function lerp(t, a, b) {
            return a + t * (b - a);
        }

        function grad(hash, x, y, z) {
            const h = hash & 15;
            const u = h < 8 ? x : y;
            const v = h < 4 ? y : h === 12 || h === 14 ? x : z;
            return ((h & 1) === 0 ? u : -u) + ((h & 2) === 0 ? v : -v);
        }

        // Tabla de permutación reducida (solo necesaria una vez)
        const p = new Array(512);
        const permutation = [151,160,137,91,90,15,131,13,201,95,96,53,194,233,7,225,140,36,103,30,69,142,8,99,37,240,21,10,23,190,6,148,247,120,234,75,0,26,197,62,94,252,219,203,117,35,11,32,57,177,33,88,237,149,56,87,174,20,125,136,171,168,68,175,74,165,71,134,139,48,27,166,77,146,158,231,83,111,229,122,60,211,133,230,220,105,92,41,55,46,245,40,244,102,143,54,65,25,63,161,1,216,80,73,209,76,132,187,208,89,18,169,200,196,135,130,116,188,159,86,164,100,109,198,173,186,3,64,52,217,226,250,124,123,5,202,38,147,118,126,255,82,85,212,207,206,59,227,47,16,58,17,182,189,28,42,223,183,170,213,119,248,152,2,44,154,163,70,221,153,101,155,167,43,172,9,129,22,39,253,19,98,108,110,79,113,224,232,178,185,112,104,218,246,97,228,251,34,242,193,238,210,144,12,191,179,162,241,81,51,145,235,249,14,239,107,49,192,214,31,181,199,106,157,184,84,204,176,115,121,50,45,127,4,150,254,138,236,205,93,222,114,67,29,24,72,243,141,128,195,78,66,215,61,156,180];
        for (let i = 0; i < 256; i++) {
            p[256 + i] = p[i] = permutation[i];
        }

        function resize() {
            width = window.innerWidth;
            height = window.innerHeight;
            canvas.width = width;
            canvas.height = height;
            cols = Math.ceil(width / gridSize) + 1;
            rows = Math.ceil(height / gridSize) + 1;
            
            // Inicializar array de influencia del mouse
            mouseInfluence = [];
            for (let i = 0; i < rows; i++) {
                mouseInfluence[i] = [];
                for (let j = 0; j < cols; j++) {
                    mouseInfluence[i][j] = 0;
                }
            }
        }

        function getHeight(x, y, t) {
            // Ruido base con 2 octavas (optimizado de 3 a 2)
            let value = 0;
            let amplitude = 1;
            let frequency = 0.01;
            
            for (let i = 0; i < 2; i++) {
                value += noise(x * frequency, y * frequency, t) * amplitude;
                amplitude *= 0.5;
                frequency *= 2;
            }
            
            // Añadir influencia del mouse
            const gridX = Math.floor(x / gridSize);
            const gridY = Math.floor(y / gridSize);
            if (gridY >= 0 && gridY < rows && gridX >= 0 && gridX < cols) {
                value += mouseInfluence[gridY][gridX];
            }
            
            return value * 100;
        }

        function updateMouseInfluence() {
            // Decay gradual GLOBAL
            for (let i = 0; i < rows; i++) {
                for (let j = 0; j < cols; j++) {
                    mouseInfluence[i][j] *= 0.96;
                    // Reset completo si es muy pequeño
                    if (mouseInfluence[i][j] < 0.001) {
                        mouseInfluence[i][j] = 0;
                    }
                }
            }
            
            // Solo añadir influencia si está habilitado
            if (!interactionEnabled) return;
            
            // Añadir nueva influencia del mouse
            const gridX = Math.floor(mouseX / gridSize);
            const gridY = Math.floor(mouseY / gridSize);
            const radius = 8;
            
            for (let i = -radius; i <= radius; i++) {
                for (let j = -radius; j <= radius; j++) {
                    const y = gridY + i;
                    const x = gridX + j;
                    
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
            
            // Dibujar líneas de contorno (reducido a 12 niveles)
            const levels = 12;
            const step = 200 / levels;
            
            for (let level = 0; level < levels; level++) {
                const threshold = -100 + level * step;
                const isThickLine = level % 3 === 0;
                
                ctx.strokeStyle = isThickLine ? 'rgba(237, 237, 237, 0.6)' : 'rgba(237, 237, 237, 0.3)';
                ctx.lineWidth = isThickLine ? 2 : 1;
                ctx.beginPath();
                
                for (let y = 0; y < rows - 1; y++) {
                    for (let x = 0; x < cols - 1; x++) {
                        const x0 = x * gridSize;
                        const y0 = y * gridSize;
                        
                        const v1 = getHeight(x0, y0, time);
                        const v2 = getHeight(x0 + gridSize, y0, time);
                        const v3 = getHeight(x0 + gridSize, y0 + gridSize, time);
                        const v4 = getHeight(x0, y0 + gridSize, time);
                        
                        // Marching squares algorithm
                        let cellType = 0;
                        if (v1 > threshold) cellType |= 8;
                        if (v2 > threshold) cellType |= 4;
                        if (v3 > threshold) cellType |= 2;
                        if (v4 > threshold) cellType |= 1;
                        
                        if (cellType === 0 || cellType === 15) continue;
                        
                        // Calcular puntos de intersección con interpolación lineal
                        const interp = (v1, v2, x1, y1, x2, y2) => {
                            const t = (threshold - v1) / (v2 - v1);
                            return {
                                x: x1 + t * (x2 - x1),
                                y: y1 + t * (y2 - y1)
                            };
                        };
                        
                        const top = interp(v1, v2, x0, y0, x0 + gridSize, y0);
                        const right = interp(v2, v3, x0 + gridSize, y0, x0 + gridSize, y0 + gridSize);
                        const bottom = interp(v4, v3, x0, y0 + gridSize, x0 + gridSize, y0 + gridSize);
                        const left = interp(v1, v4, x0, y0, x0, y0 + gridSize);
                        
                        // Dibujar según el tipo de celda
                        switch (cellType) {
                            case 1: case 14:
                                ctx.moveTo(left.x, left.y);
                                ctx.lineTo(bottom.x, bottom.y);
                                break;
                            case 2: case 13:
                                ctx.moveTo(bottom.x, bottom.y);
                                ctx.lineTo(right.x, right.y);
                                break;
                            case 3: case 12:
                                ctx.moveTo(left.x, left.y);
                                ctx.lineTo(right.x, right.y);
                                break;
                            case 4: case 11:
                                ctx.moveTo(top.x, top.y);
                                ctx.lineTo(right.x, right.y);
                                break;
                            case 5:
                                ctx.moveTo(left.x, left.y);
                                ctx.lineTo(top.x, top.y);
                                ctx.moveTo(bottom.x, bottom.y);
                                ctx.lineTo(right.x, right.y);
                                break;
                            case 6: case 9:
                                ctx.moveTo(top.x, top.y);
                                ctx.lineTo(bottom.x, bottom.y);
                                break;
                            case 7: case 8:
                                ctx.moveTo(left.x, left.y);
                                ctx.lineTo(top.x, top.y);
                                break;
                            case 10:
                                ctx.moveTo(top.x, top.y);
                                ctx.lineTo(right.x, right.y);
                                ctx.moveTo(left.x, left.y);
                                ctx.lineTo(bottom.x, bottom.y);
                                break;
                        }
                    }
                }
                ctx.stroke();
            }
        }

        function animate(timestamp) {
            // Control de FPS
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

        // Event listeners
        const toggleBtn = document.getElementById('toggleBtn');
        
        toggleBtn.addEventListener('click', () => {
            interactionEnabled = !interactionEnabled;
            toggleBtn.classList.toggle('active');
            toggleBtn.textContent = interactionEnabled ? 'Interacción: ON' : 'Interacción: OFF';
            
            // Si se desactiva, resetear posición del mouse
            if (!interactionEnabled) {
                mouseX = -1000;
                mouseY = -1000;
            }
        });

        canvas.addEventListener('mousemove', (e) => {
            if (interactionEnabled) {
                mouseX = e.clientX;
                mouseY = e.clientY;
            }
        });

        canvas.addEventListener('mouseleave', () => {
            mouseX = -1000;
            mouseY = -1000;
        });

        window.addEventListener('resize', resize);

        // Iniciar
        resize();
        requestAnimationFrame(animate);
    </script>
</body>
</html>