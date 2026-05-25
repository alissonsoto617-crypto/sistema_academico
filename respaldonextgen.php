<?php
$mensaje = "";

if(isset($_POST['manual'])){

    $usuario = "root";
    $password = "chilaquilas25";
    $bd = "nextgen_db";

    $fecha = date("Y-m-d_H-i-s");
    $archivo = "respaldo_" . $fecha . ".sql";

    $ruta = "C:/respaldos/" . $archivo;

    $comando = "\"C:/AppServ/MySQL/bin/mysqldump\" -u $usuario -p\"$password\" $bd > \"$ruta\" 2>&1";

    system($comando);

    $mensaje = "✅ Respaldo creado: $archivo";
}

if(isset($_POST['programar'])){

    $frecuencia = $_POST['frecuencia'];
    $tiempo = ($frecuencia == "diario") ? "DAILY" : "WEEKLY";

    $comando = 'schtasks /create /sc '.$tiempo.' /tn "RespaldoBD" /tr "\"C:\\AppServ\\php\\php.exe\" C:\\AppServ\\www\\Banco\\backup_auto.php" /st 10:00 /f';

    system($comando);

    $mensaje = "⏰ Respaldo automático programado ($frecuencia)";
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Respaldos</title>
    <link rel="stylesheet" href="implementaciones.css">
</head>

<body>

<div class="container" style="max-width: 500px; margin-top: 80px;">

    <div class="card" style="text-align:center;">

        <h2>Respaldos del Sistema</h2>

        <?php if ($mensaje != "") { ?>
            <p class="mensaje"><?= $mensaje ?></p>
        <?php } ?>

        <form method="post" style="margin-bottom:20px;">
            <button name="manual" style="width:100%;">
                Crear Respaldo Manual
            </button>
        </form>

        <hr>

        <h3>Programar respaldo automático</h3>

        <form method="post">

            <label>Frecuencia</label>

            <select name="frecuencia" style="width:100%; padding:10px; margin-bottom:15px;">
                <option value="diario">Diario</option>
                <option value="semanal">Semanal</option>
            </select>

            <button name="programar" style="width:100%;">
                Programar Respaldo
            </button>

        </form>

    </div>

</div>

</body>
</html>