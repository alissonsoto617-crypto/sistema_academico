<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manual del Sistema</title>
    <link rel="stylesheet" href="implementaciones.css">
    <style>
        body { padding: 20px; }

        .manual-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            flex-wrap: wrap;
            gap: 12px;
            margin-bottom: 20px;
        }

        .header-actions {
            display: flex;
            gap: 10px;
            align-items: center;
        }

        .btn-descarga, .btn-fullscreen {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 10px 22px;
            color: white;
            border-radius: 8px;
            font-family: 'Nunito', 'Segoe UI', sans-serif;
            font-size: 0.95rem;
            font-weight: 700;
            text-decoration: none;
            border: none;
            cursor: pointer;
            transition: all 0.15s;
        }

        .btn-descarga {
            background: linear-gradient(135deg, #4caf87, #339966);
            box-shadow: 0 4px 12px rgba(76, 175, 135, 0.35);
        }

        .btn-fullscreen {
            background: linear-gradient(135deg, #8F95D3, #7178c4);
            box-shadow: 0 4px 12px rgba(113, 120, 196, 0.35);
        }

        .btn-descarga:hover, .btn-fullscreen:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 18px rgba(0,0,0,0.3);
        }

        .pdf-wrapper {
            position: relative;
            background: #2d2d3a;
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 8px 32px rgba(60, 50, 80, 0.25);
        }

        .pdf-wrapper embed,
        .pdf-wrapper iframe {
            display: block;
            width: 100%;
            height: 680px;
            border: none;
            border-radius: 12px;
        }

        .pdf-wrapper.fullscreen-mode {
            position: fixed;
            top: 0; left: 0;
            width: 100vw;
            height: 100vh;
            border-radius: 0;
            z-index: 9999;
            display: flex;
            flex-direction: column;
        }

        .pdf-wrapper.fullscreen-mode embed,
        .pdf-wrapper.fullscreen-mode iframe {
            flex: 1;
            height: 100%;
            border-radius: 0;
        }

        .fs-topbar {
            display: none;
            align-items: center;
            justify-content: space-between;
            padding: 10px 20px;
            background: #1e1e2a;
            color: white;
        }

        .pdf-wrapper.fullscreen-mode .fs-topbar {
            display: flex;
        }

        #fs-overlay {
            display: none;
            position: fixed;
            inset: 0;
            background: rgba(0,0,0,0.7);
            z-index: 9998;
        }
    </style>
</head>
<body>

<div class="container">

    <div class="manual-header">
        <h2>📖 Manual del Sistema Estudiantil</h2>
        <div class="header-actions">
            <button class="btn-fullscreen" onclick="abrirFullscreen()">
                <strong>⛶ Expandir</strong>
            </button>
            <a class="btn-descarga" href="Manualestudiantil.pdf" download>
                ⬇️ Descargar Manual
            </a>
        </div>
    </div>

    <div class="pdf-wrapper" id="pdfWrapper">

        <div class="fs-topbar">
            <span>📖 Manual del Sistema Estudiantil</span>
            <button class="btn-descarga" onclick="cerrarFullscreen()" style="background:#e74c3c;">
                Cerrar Pantalla Completa
            </button>
        </div>

        <embed
            id="pdfEmbed"
            src="Manualestudiantil.pdf#toolbar=1&navpanes=1&scrollbar=1&view=FitH"
            type="application/pdf"
            onerror="mostrarFallback()"
        >
    </div>

</div>

<div id="fs-overlay"></div>

<script>
    const wrapper = document.getElementById('pdfWrapper');
    const overlay = document.getElementById('fs-overlay');

    function abrirFullscreen() {
        wrapper.classList.add('fullscreen-mode');
        overlay.style.display = 'block';
        document.body.style.overflow = 'hidden';
    }

    function cerrarFullscreen() {
        wrapper.classList.remove('fullscreen-mode');
        overlay.style.display = 'none';
        document.body.style.overflow = '';
    }

    // Cerrar con tecla ESC
    document.addEventListener('keydown', e => {
        if (e.key === "Escape") cerrarFullscreen();
    });

    overlay.addEventListener('click', cerrarFullscreen);

    function mostrarFallback() {
        alert("No se pudo cargar el PDF. Asegúrate de que el archivo esté en la misma carpeta.");
    }
</script>

</body>
</html>