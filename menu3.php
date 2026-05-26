<?php
session_start();

if (!isset($_SESSION['usuario'])) {
    header("Location: index.php");
    exit();
}
?>

<style>
@import url('https://fonts.googleapis.com/css2?family=Nunito:wght@400;600;700;800&display=swap');

:root {
    --beige: #E8D9B5;
    --gris: #A89F94;
    --rosado: #E8A9A9;
    --rosado-fuerte: #E94B7D;
    --durazno: #F4BE6A;
    --lila: #8F95D3;
    --lila-claro: #A6A8D7;
    --rosado-claro: #E3A5B8;
}

/* RESET */
* { box-sizing: border-box; margin: 0; padding: 0; }

/* LAYOUT */
body {
    display: flex;
    height: 100vh;
    font-family: 'Nunito', Arial, sans-serif;
    background: linear-gradient(135deg, var(--beige), var(--rosado-claro));
    overflow: hidden;
}

/* MENU */
.menu {
    width: 240px;
    min-width: 240px;
    background: var(--gris);
    display: flex;
    flex-direction: column;
    justify-content: space-between;
    padding: 20px 16px;
    overflow-y: auto;
}

/* INFO USUARIO */
.usuario {
    color: white;
    margin-bottom: 18px;
    font-weight: 700;
    text-align: center;
    background: rgba(255,255,255,0.12);
    border-radius: 12px;
    padding: 12px 8px;
    font-size: 0.92rem;
    line-height: 1.7;
}

/* OPCIONES */
.menu-opciones {
    display: flex;
    flex-direction: column;
    gap: 8px;
}

.menu-opciones a,
.menu-opciones li a {
    text-decoration: none;
    background: var(--lila);
    color: white;
    padding: 11px 14px;
    border-radius: 10px;
    text-align: center;
    font-weight: 700;
    font-size: 0.88rem;
    transition: background 0.2s, transform 0.15s;
    display: block;
    list-style: none;
}

.menu-opciones a:hover,
.menu-opciones li a:hover {
    background: var(--durazno);
    color: #3a3340;
    transform: scale(1.03);
}

/* Botón Manual - destaca un poco diferente */
.menu-opciones a.btn-manual {
    background: linear-gradient(135deg, #5a6dbf, var(--lila));
    border: 2px solid rgba(255,255,255,0.25);
}

.menu-opciones a.btn-manual:hover {
    background: var(--durazno);
    color: #3a3340;
    border-color: transparent;
}

/* Separador en el menú */
.menu-divider {
    border: none;
    border-top: 1px dashed rgba(255,255,255,0.3);
    margin: 6px 0;
}

/* Quita bullets de li */
.menu-opciones li {
    list-style: none;
}

/* LOGOUT */
.logout {
    background: var(--rosado-fuerte);
    padding: 11px;
    border-radius: 10px;
    text-align: center;
    color: white;
    text-decoration: none;
    font-weight: 700;
    font-size: 0.88rem;
    transition: background 0.2s, transform 0.15s;
    display: block;
    margin-top: 16px;
}

.logout:hover {
    background: #c73968;
    transform: scale(1.02);
}

/* CONTENIDO */
.contenido {
    flex: 1;
    padding: 16px;
    overflow: hidden;
}

/* iframe */
iframe {
    width: 100%;
    height: 100%;
    border: none;
    border-radius: 16px;
    background: linear-gradient(135deg, #6b656d, #57565c);
    box-shadow: 0px 6px 20px rgba(0,0,0,0.12);
}
</style>

<div class="menu">

    <!-- INFO USUARIO -->
    <div>
        <div class="usuario">
            👤 <?= htmlspecialchars($_SESSION['usuario']) ?><br>
            🔑 <?= htmlspecialchars($_SESSION['rol']) ?>
        </div>

        <div class="menu-opciones">

            <!-- ADMIN VE TODO -->
            <?php if ($_SESSION['rol'] == "admin") { ?>

                <a href="carreras3 - copia.php" target="contenido">🎓 Carreras</a>
                <a href="materias3.php" target="contenido">📚 Materias</a>
                <a href="maestre3.php" target="contenido">👨‍🏫 Maestros</a>
                <a href="grupos3.php" target="contenido">👥 Grupos</a>
                <a href="estudiante3.php" target="contenido">🎒 Estudiantes</a>
                <a href="inscripciones3.php" target="contenido">📝 Inscripciones</a>
                <a href="calificaciones3.php" target="contenido">⭐ Calificaciones</a>
                <li><a href="respaldonextgen.php" target="contenido">💾 Respaldos</a></li>

                <hr class="menu-divider">

                <a href="manual3.php" target="contenido" class="btn-manual">📖 Manual Académico</a>

            <?php } ?>

            <!-- MAESTRO SOLO VE ESTO -->
            <?php if ($_SESSION['rol'] == "maestro") { ?>

                <a href="materias3.php" target="contenido">📚 Materias</a>
                <a href="grupos3.php" target="contenido">👥 Grupos</a>
                <a href="calificaciones3.php" target="contenido">📝 Calificaciones</a>

                <hr class="menu-divider">

                <a href="manual3.php" target="contenido" class="btn-manual">📖 Manual Académico</a>

            <?php } ?>

        </div>
    </div>

    <!-- LOGOUT -->
    <a href="cerrar.php" class="logout">🔓 Cerrar sesión</a>

</div>

<div class="contenido">
    <iframe name="contenido"></iframe>
</div>
