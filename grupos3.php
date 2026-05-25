<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once "sistemaestudiantilmodulo3.3.php";

$obj = new clasesistema();
$mensaje = "";
$tipo_mensaje = "red";

// -------- ELIMINAR --------
if (isset($_GET['eliminar'])) {
    $obj->eliminarGenerico("grupos", "id_grupo", $_GET['eliminar']);
    header("Location: grupos3.php");
    exit();
}

// -------- CREAR --------
if (isset($_POST['guardar'])) {

    if (!is_numeric($_POST['id_materia']) || !is_numeric($_POST['id_maestro'])) {
        $mensaje = "Datos inválidos";
    } elseif (!isset($_POST['cupo_maximo']) || $_POST['cupo_maximo'] == "") {
        $mensaje = "El cupo es obligatorio";
    } elseif (!is_numeric($_POST['cupo_maximo'])) {
        $mensaje = "El cupo debe ser numérico";
    } elseif ($_POST['cupo_maximo'] <= 0) {
        $mensaje = "El cupo debe ser mayor a 0";
    } elseif ($_POST['cupo_maximo'] > 55) {
        $mensaje = "El cupo es demasiado alto (máximo 55)";
    } else {

        $id_materia = $_POST['id_materia'];
        $id_maestro = $_POST['id_maestro'];
        $cupo_maximo = $_POST['cupo_maximo'];

        // VALIDAR DUPLICADO
        $grupos = $obj->obtenerGenerico("grupos");
        $existe = false;

        while ($g = $grupos->fetch_assoc()) {
            if ($g['id_materia'] == $id_materia && $g['id_maestro'] == $id_maestro) {
                $existe = true;
                break;
            }
        }

        if ($existe) {
            $mensaje = "Ya existe un grupo con esta materia y maestro";
        } else {
            $datos = [
                "id_materia" => $id_materia,
                "id_maestro" => $id_maestro,
                "cupo_maximo" => $cupo_maximo
            ];

            $obj->insertarGenerico("grupos", $datos);
            header("Location: grupos3.php?success=1");
            exit();
        }
    }
}

// -------- ACTUALIZAR --------
if (isset($_POST['actualizar'])) {

    if (!is_numeric($_POST['id_materia']) || !is_numeric($_POST['id_maestro'])) {
        $mensaje = "Datos inválidos";
    } elseif (!isset($_POST['cupo_maximo']) || $_POST['cupo_maximo'] == "") {
        $mensaje = "El cupo es obligatorio";
    } elseif (!is_numeric($_POST['cupo_maximo'])) {
        $mensaje = "El cupo debe ser numérico";
    } elseif ($_POST['cupo_maximo'] <= 0) {
        $mensaje = "El cupo debe ser mayor a 0";
    } elseif ($_POST['cupo_maximo'] > 55) {
        $mensaje = "El cupo es demasiado alto (máximo 55)";
    } else {

        $id_materia = $_POST['id_materia'];
        $id_maestro = $_POST['id_maestro'];
        $cupo_maximo = $_POST['cupo_maximo'];
        $id_grupo = $_POST['id_grupo'];

        // VALIDAR DUPLICADO (excepto el mismo)
        $grupos = $obj->obtenerGenerico("grupos");
        $existe = false;

        while ($g = $grupos->fetch_assoc()) {
            if ($g['id_materia'] == $id_materia && 
                $g['id_maestro'] == $id_maestro && 
                $g['id_grupo'] != $id_grupo) {
                $existe = true;
                break;
            }
        }

        if ($existe) {
            $mensaje = "Ya existe otro grupo con esta materia y maestro";
        } else {
            $datos = [
                "id_materia" => $id_materia,
                "id_maestro" => $id_maestro,
                "cupo_maximo" => $cupo_maximo
            ];

            $obj->actualizarGenerico("grupos", $datos, "id_grupo", $id_grupo);
            header("Location: grupos3.php?success=1");
            exit();
        }
    }
}

// -------- MODO EDICIÓN --------
$editando = false;
$data = null;

if (isset($_GET['editar'])) {
    $editando = true;
    $data = $obj->seleccionarGenerico("grupos", "id_grupo", $_GET['editar']);
}

// Mensaje de éxito
if (isset($_GET['success'])) {
    $mensaje = "Operación realizada correctamente";
    $tipo_mensaje = "green";
}

// -------- LISTAR --------
$grupos = $obj->obtenerGenerico("grupos");
$materias = $obj->obtenerGenerico("materias");
$maestros = $obj->obtenerGenerico("maestros");
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Gestión de Grupos</title>
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

    <h2><?= $editando ? "Editar Grupo" : "Nuevo Grupo" ?></h2>

    <?php if ($mensaje): ?>
        <div class="alerta" style="background-color: <?= $tipo_mensaje === 'green' ? '#d4edda' : '#f8d7da'; ?>; 
                                   color: <?= $tipo_mensaje === 'green' ? '#155724' : '#721c24'; ?>;">
            <?= htmlspecialchars($mensaje) ?>
        </div>
    <?php endif; ?>

    <form method="POST" action="grupos3.php">
        <?php if ($editando): ?>
            <input type="hidden" name="id_grupo" value="<?= $data['id_grupo'] ?>">
        <?php endif; ?>

        <label>Materia</label>
        <select name="id_materia" required>
            <option value="">-- Seleccione una materia --</option>
            <?php 
            $materias->data_seek(0);
            while($m = $materias->fetch_assoc()): ?>
                <option value="<?= $m['id_materia'] ?>"
                    <?= ($editando && $m['id_materia'] == $data['id_materia']) ? 'selected' : '' ?>>
                    <?= htmlspecialchars($m['nombre']) ?>
                </option>
            <?php endwhile; ?>
        </select>

        <label>Maestro</label>
        <select name="id_maestro" required>
            <option value="">-- Seleccione un maestro --</option>
            <?php 
            $maestros->data_seek(0);
            while($ma = $maestros->fetch_assoc()): ?>
                <option value="<?= $ma['id_maestro'] ?>"
                    <?= ($editando && $ma['id_maestro'] == $data['id_maestro']) ? 'selected' : '' ?>>
                    <?= htmlspecialchars($ma['nombre'] . " " . $ma['apellido']) ?>
                </option>
            <?php endwhile; ?>
        </select>

        <label>Cupo Máximo</label>
        <input type="number" name="cupo_maximo" min="1" max="55"
               value="<?= $editando ? $data['cupo_maximo'] : '' ?>" required>

        <button type="submit" name="<?= $editando ? 'actualizar' : 'guardar' ?>" class="btn">
            <?= $editando ? "Actualizar Grupo" : "Guardar Grupo" ?>
        </button>

        <?php if ($editando): ?>
            <a href="grupos3.php">Cancelar</a>
        <?php endif; ?>
    </form>

    <hr>

    <h2>Lista de Grupos</h2>

    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>Materia</th>
                <th>Maestro</th>
                <th>Cupo Máximo</th>
                <th>Acciones</th>
            </tr>
        </thead>
        <tbody>
            <?php while($row = $grupos->fetch_assoc()): 
                $materia = $obj->seleccionarGenerico("materias", "id_materia", $row['id_materia']);
                $maestro = $obj->seleccionarGenerico("maestros", "id_maestro", $row['id_maestro']);
            ?>
            <tr>
                <td><?= $row['id_grupo'] ?></td>
                <td><?= htmlspecialchars($materia['nombre'] ?? '—') ?></td>
                <td><?= htmlspecialchars($maestro ? $maestro['nombre']." ".$maestro['apellido'] : '—') ?></td>
                <td><strong><?= $row['cupo_maximo'] ?></strong></td>
                <td>
                    <a href="grupos3.php?editar=<?= $row['id_grupo'] ?>">Editar</a>
                    <a href="grupos3.php?eliminar=<?= $row['id_grupo'] ?>" 
                       onclick="return confirm('¿Estás seguro de eliminar este grupo?')" 
                       class="btn-del">Eliminar</a>
                </td>
            </tr>
            <?php endwhile; ?>
        </tbody>
    </table>

</div>

</body>
</html>