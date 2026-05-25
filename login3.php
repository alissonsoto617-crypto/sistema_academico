<?php
session_start();

$mensaje = "";

if (isset($_POST['login'])) {

    $usuario = $_POST['usuario'];
    $password = $_POST['password'];

    // ADMIN
    if ($usuario == "admin" && $password == "1234") {

        $_SESSION['usuario'] = $usuario;
        $_SESSION['rol'] = "admin";

        header("Location: menu3.php");
        exit();
    }

    // MAESTRO
    else if ($usuario == "maestro" && $password == "1234") {

        $_SESSION['usuario'] = $usuario;
        $_SESSION['rol'] = "maestro";

        header("Location: menu3.php");
        exit();
    }

    else {
        $mensaje = "❌ Usuario o contraseña incorrectos";
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Login</title>
    <link rel="stylesheet" href="implementaciones.css">
</head>

<body>

<div class="container" style="max-width: 400px; margin-top: 80px;">

    <div class="card" style="text-align:center;">

        <h2>Iniciar Sesión</h2>

        <?php if ($mensaje != "") { ?>
            <p class="mensaje"><?= $mensaje ?></p>
        <?php } ?>

        <form method="POST">

            <label>Usuario</label>
            <input type="text" name="usuario" required>

            <label>Contraseña</label>
            <input type="password" name="password" required>

            <button type="submit" name="login" style="width:100%;">
                Ingresar
            </button>

        </form>

    </div>

</div>

</body>
</html>