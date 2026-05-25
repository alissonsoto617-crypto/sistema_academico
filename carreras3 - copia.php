<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once "sistemaestudiantilmodulo3.3.php";

$obj = new clasesistema();
$mensaje = "";
$tipo_mensaje = "red";

// -------- ELIMINAR --------
if (isset($_GET['eliminar'])) {
    $obj->eliminarGenerico("carreras", "id_carrera", $_GET['eliminar']);
    header("Location: carreras3 - copia.php");
    exit();
}

// -------- CREAR --------
if (isset($_POST['guardar'])) {

    if (!isset($_POST['nombre']) || trim($_POST['nombre']) == "") {
        $mensaje = "El nombre no puede estar vacío";
    } elseif (strlen(trim($_POST['nombre'])) < 3) {
        $mensaje = "El nombre debe tener al menos 3 caracteres";
    } else {

        $nombre = trim($_POST['nombre']);

        $carreras = $obj->obtenerGenerico("carreras");
        $existe = false;

        while ($c = $carreras->fetch_assoc()) {
            if (strtolower($c['nombre']) == strtolower($nombre)) {
                $existe = true;
                break;
            }
        }

        if ($existe) {
            $mensaje = "La carrera ya existe";
        } else {
            $datos = ["nombre" => $nombre];
            $obj->insertarGenerico("carreras", $datos);
            header("Location: carreras3 - copia.php?success=1");
            exit();
        }
    }
}

// -------- ACTUALIZAR --------
if (isset($_POST['actualizar'])) {

    if (!isset($_POST['nombre']) || trim($_POST['nombre']) == "") {
        $mensaje = "El nombre no puede estar vacío";
    } elseif (strlen(trim($_POST['nombre'])) < 3) {
        $mensaje = "El nombre debe tener al menos 3 caracteres";
    } else {

        $nombre = trim($_POST['nombre']);
        $id_carrera = $_POST['id_carrera'];

        $carreras = $obj->obtenerGenerico("carreras");
        $existe = false;

        while ($c = $carreras->fetch_assoc()) {
            if (strtolower($c['nombre']) == strtolower($nombre) && 
                $c['id_carrera'] != $id_carrera) {
                $existe = true;
                break;
            }
        }

        if ($existe) {
            $mensaje = "Ya existe otra carrera con ese nombre";
        } else {
            $datos = ["nombre" => $nombre];
            $obj->actualizarGenerico("carreras", $datos, "id_carrera", $id_carrera);
            header("Location: carreras3 - copia.php?success=1");
            exit();
        }
    }
}

// -------- MODO EDICIÓN --------
$editando = false;
$data = null;

if (isset($_GET['editar'])) {
    $editando = true;
    $data = $obj->seleccionarGenerico("carreras", "id_carrera", $_GET['editar']);
}

if (isset($_GET['success'])) {
    $mensaje = "Operación realizada correctamente";
    $tipo_mensaje = "green";
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Gestión de Carreras</title>
    <link rel="stylesheet" href="implementaciones.css">
    <style>
        .alerta {
            padding: 15px;
            margin-bottom: 20px;
            border-radius: 8px;
            font-weight: 600;
        }
    </style>
</head>
<body>

<div class="container">

    <h2><?= $editando ? "Editar Carrera" : "Nueva Carrera" ?></h2>

    <?php if ($mensaje): ?>
        <div class="alerta" style="background-color: <?= $tipo_mensaje === 'green' ? '#d4edda' : '#f8d7da'; ?>; 
                                   color: <?= $tipo_mensaje === 'green' ? '#155724' : '#721c24'; ?>;">
            <?= htmlspecialchars($mensaje) ?>
        </div>
    <?php endif; ?>

    <!-- === FORMULARIO CORREGIDO === -->
    <form method="POST" action="carreras3 - copia.php">
        <?php if ($editando): ?>
            <input type="hidden" name="id_carrera" value="<?= $data['id_carrera'] ?>">
        <?php endif; ?>

        <label>Nombre de la Carrera</label>
        <input type="text" name="nombre" 
               value="<?= $editando ? htmlspecialchars($data['nombre'] ?? '') : '' ?>" 
               required maxlength="100">

        <button type="submit" name="<?= $editando ? 'actualizar' : 'guardar' ?>" class="btn">
            <?= $editando ? "Actualizar Carrera" : "Guardar Carrera" ?>
        </button>

        <?php if ($editando): ?>
            <a href="carreras3 - copia.php" style="margin-left: 10px;">Cancelar</a>
        <?php endif; ?>
    </form>

    <hr>

    <h2>Lista de Carreras</h2>

    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>Nombre de la Carrera</th>
                <th>Acciones</th>
            </tr>
        </thead>
        <tbody>
            <?php 
            $carreras = $obj->obtenerGenerico("carreras");
            while($row = $carreras->fetch_assoc()): 
            ?>
            <tr>
                <td><?= $row['id_carrera'] ?></td>
                <td><?= htmlspecialchars($row['nombre']) ?></td>
                <td>
                    <a href="carreras3 - copia.php?editar=<?= $row['id_carrera'] ?>">Editar</a>
                    <a href="carreras3 - copia.php?eliminar=<?= $row['id_carrera'] ?>" 
                       onclick="return confirm('¿Estás seguro de eliminar esta carrera? Esta acción no se puede deshacer.')" 
                       class="btn-del">Eliminar</a>
                </td>
            </tr>
            <?php endwhile; ?>
        </tbody>
    </table>

</div>

</body>
</html>