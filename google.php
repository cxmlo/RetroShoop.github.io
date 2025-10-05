<!-- Font Awesome CDN -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">

<!-- Google Translate Widget Script -->
<script type="text/javascript">
    function googleTranslateElementInit() {
        new google.translate.TranslateElement({
            pageLanguage: 'es',
            includedLanguages: 'en,fr,pt,de,it,zh-CN,ja,ko,ru,ar',
            layout: google.translate.TranslateElement.InlineLayout.SIMPLE,
            autoDisplay: false
        }, 'google_translate_element');
    }
</script>
<script type="text/javascript" src="//translate.google.com/translate_a/element.js?cb=googleTranslateElementInit"></script>

<style>
    /* Contenedor flotante del widget */
    #google_translate_float {
        position: fixed;
        bottom: 20px;
        right: 20px;
        z-index: 9999;
        transition: all 0.3s;
    }

    /* Contenedor del widget colapsable */
    #google_translate_container {
        background: #fff;
        border: 2px solid #000;
        padding: 0.5rem;
        box-shadow: 0 0 20px rgba(0, 0, 0, 0.3);
        transition: all 0.3s;
        margin-bottom: 0.5rem;
        max-height: 500px;
        overflow: hidden;
    }

    #google_translate_container.hidden {
        max-height: 0;
        padding: 0;
        opacity: 0;
        margin-bottom: 0;
    }

    #google_translate_container:not(.hidden):hover {
        box-shadow: 0 0 30px rgba(0, 0, 0, 0.5);
    }

    /* Botón de toggle */
    #translate_toggle_btn {
        background: #000;
        color: #fff;
        border: 2px solid #000;
        padding: 0.6rem;
        cursor: pointer;
        font-family: 'Courier New', monospace;
        font-weight: bold;
        text-transform: uppercase;
        letter-spacing: 1px;
        font-size: 0.75rem;
        transition: all 0.3s;
        box-shadow: 0 0 20px rgba(0, 0, 0, 0.3);
        display: flex;
        align-items: center;
        gap: 0.5rem;
        width: 100%;
        justify-content: center;
    }

    #translate_toggle_btn:hover {
        background: #fff;
        color: #000;
        box-shadow: 0 0 30px rgba(0, 0, 0, 0.5);
        transform: translateY(-2px);
    }

    #translate_toggle_btn i {
        font-size: 1rem;
        transition: transform 0.3s;
    }

    #translate_toggle_btn.collapsed i {
        transform: rotate(180deg);
    }

    /* Ocultar elementos innecesarios de Google Translate */
    .goog-te-banner-frame {
        display: none !important;
    }

    body {
        top: 0 !important;
    }

    .goog-te-gadget {
        font-family: 'Courier New', monospace !important;
        color: #000 !important;
    }

    .goog-te-gadget-simple {
        background-color: #fff !important;
        border: 2px solid #000 !important;
        padding: 5px 8px !important;
        font-size: 0.75rem !important;
        font-weight: bold !important;
        letter-spacing: 1px !important;
        transition: all 0.3s !important;
        cursor: pointer !important;
    }

    .goog-te-gadget-simple:hover {
        background-color: #00000086 !important;
        color: #fff !important;
        box-shadow: 0 0 15px rgba(0, 0, 0, 0.3) !important;
    }

    .goog-te-gadget-simple .goog-te-menu-value span {
        color: inherit !important;
        font-family: 'Courier New', monospace !important;
    }

    .goog-te-gadget-icon {
        display: none !important;
    }

    /* Estilo del menú desplegable */
    .goog-te-menu-frame {
        border: 3px solid #000 !important;
        box-shadow: 0 0 30px rgba(0, 0, 0, 0.5) !important;
    }

    /* Responsive */
    @media (max-width: 768px) {
        #google_translate_float {
            bottom: 10px;
            right: 10px;
        }

        #google_translate_container {
            padding: 0.4rem;
        }

        .goog-te-gadget-simple {
            padding: 4px 6px !important;
            font-size: 0.7rem !important;
        }

        #translate_toggle_btn {
            padding: 0.5rem;
            font-size: 0.7rem;
        }
    }

    /* Etiqueta */
    .translate-label {
        font-family: 'Courier New', monospace;
        font-size: 0.65rem;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        margin-bottom: 0.2rem;
        font-weight: bold;
        display: block;
    }

    .translate-label i {
        margin-right: 0.2rem;
        font-size: 0.7rem;
    }
</style>

<!-- Widget de Google Translate Flotante -->
<div id="google_translate_float">
    <div id="google_translate_container">
        <span class="translate-label"><i class="fas fa-globe"></i> Idioma</span>
        <div id="google_translate_element"></div>
    </div>
    <button id="translate_toggle_btn" class="collapsed">
        <i class="fas fa-chevron-up"></i>
        <span>Translate</span>
    </button>
</div>

<script>
    // Toggle del widget de traducción
    document.addEventListener('DOMContentLoaded', function() {
        const toggleBtn = document.getElementById('translate_toggle_btn');
        const container = document.getElementById('google_translate_container');
        
        // Inicialmente oculto
        container.classList.add('hidden');
        
        toggleBtn.addEventListener('click', function() {
            container.classList.toggle('hidden');
            toggleBtn.classList.toggle('collapsed');
            
            // Cambiar texto del botón
            const btnText = toggleBtn.querySelector('span');
            if (container.classList.contains('hidden')) {
                btnText.textContent = 'Translate';
            } else {
                btnText.textContent = 'Close';
            }
        });
    });
</script>

<script>
(function() {
    function checkTranslation() {
        const htmlLang = document.documentElement.lang;
        const header = document.querySelector('.header');
        const nav = document.querySelector('.nav');
        const isMobile = window.innerWidth <= 768;
        
        if (!header) return;
        
        if (htmlLang !== 'es') {
            const offset = isMobile ? '35px' : '45px';
            const navTop = isMobile ? '108px' : '118px';
            
            header.style.marginTop = offset;
            if (nav) nav.style.top = navTop;
        } else {
            header.style.marginTop = '0';
            if (nav) nav.style.top = '73px';
        }
    }
    
    const observer = new MutationObserver(checkTranslation);
    observer.observe(document.documentElement, {
        attributes: true,
        attributeFilter: ['lang', 'class']
    });
    observer.observe(document.body, {
        attributes: true,
        attributeFilter: ['class']
    });
    
    setInterval(checkTranslation, 1000);
    window.addEventListener('load', checkTranslation);
    window.addEventListener('resize', checkTranslation);
})();
</script>