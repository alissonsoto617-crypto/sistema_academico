<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once "sistemaestudiantilmodulo3.3.php";

$obj = new clasesistema();
$mensaje = "";
$tipo_mensaje = "red";

// -------- FUNCIÓN PARA NORMALIZAR --------
function normalizar($texto) {
    $texto = trim($texto);
    $texto = strtolower($texto);
    $texto = str_replace(
        ['á','é','í','ó','ú','Á','É','Í','Ó','Ú'],
        ['a','e','i','o','u','a','e','i','o','u'],
        $texto
    );
    return $texto;
}

// -------- ELIMINAR --------
if (isset($_GET['eliminar'])) {
    $obj->eliminarGenerico("materias", "id_materia", $_GET['eliminar']);
    header("Location: materias3.php");
    exit();
}

// -------- CREAR --------
if (isset($_POST['guardar'])) {

    if (!isset($_POST['nombre']) || trim($_POST['nombre']) == "") {
        $mensaje = "El nombre no puede estar vacío";
    } elseif (strlen(trim($_POST['nombre'])) < 3) {
        $mensaje = "El nombre debe tener al menos 3 caracteres";
    } elseif (!isset($_POST['id_carrera']) || $_POST['id_carrera'] == "") {
        $mensaje = "Debes seleccionar una carrera";
    } elseif (!is_numeric($_POST['id_carrera'])) {
        $mensaje = "Carrera inválida";
    } else {

        $nombre = trim($_POST['nombre']);
        $id_carrera = $_POST['id_carrera'];

        // VALIDAR DUPLICADO NORMALIZADO
        $materias = $obj->obtenerGenerico("materias");
        $existe = false;

        while ($m = $materias->fetch_assoc()) {
            if (normalizar($m['nombre']) == normalizar($nombre) && 
                $m['id_carrera'] == $id_carrera) {
                $existe = true;
                break;
            }
        }

        if ($existe) {
            $mensaje = "Esta materia ya existe en esa carrera";
        } else {
            $datos = [
                "nombre" => $nombre,
                "id_carrera" => $id_carrera
            ];
            $obj->insertarGenerico("materias", $datos);
            header("Location: materias3.php?success=1");
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
    } elseif (!isset($_POST['id_carrera']) || $_POST['id_carrera'] == "") {
        $mensaje = "Debes seleccionar una carrera";
    } elseif (!is_numeric($_POST['id_carrera'])) {
        $mensaje = "Carrera inválida";
    } else {

        $nombre = trim($_POST['nombre']);
        $id_carrera = $_POST['id_carrera'];
        $id_materia = $_POST['id_materia'];

        // VALIDAR DUPLICADO NORMALIZADO (excepto el mismo)
        $materias = $obj->obtenerGenerico("materias");
        $existe = false;

        while ($m = $materias->fetch_assoc()) {
            if (normalizar($m['nombre']) == normalizar($nombre) && 
                $m['id_carrera'] == $id_carrera && 
                $m['id_materia'] != $id_materia) {
                $existe = true;
                break;
            }
        }

        if ($existe) {
            $mensaje = "Ya existe esa materia en esa carrera";
        } else {
            $datos = [
                "nombre" => $nombre,
                "id_carrera" => $id_carrera
            ];
            $obj->actualizarGenerico("materias", $datos, "id_materia", $id_materia);
            header("Location: materias3.php?success=1");
            exit();
        }
    }
}

// -------- MODO EDICIÓN --------
$editando = false;
$data = null;

if (isset($_GET['editar'])) {
    $editando = true;
    $data = $obj->seleccionarGenerico("materias", "id_materia", $_GET['editar']);
}

// Mensaje de éxito
if (isset($_GET['success'])) {
    $mensaje = "Operación realizada correctamente";
    $tipo_mensaje = "green";
}

// -------- LISTAR --------
$materias = $obj->obtenerGenerico("materias");
$carreras = $obj->obtenerGenerico("carreras");
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Gestión de Materias</title>
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

    <h2><?= $editando ? "Editar Materia" : "Nueva Materia" ?></h2>

    <?php if ($mensaje): ?>
        <div class="alerta" style="background-color: <?= $tipo_mensaje === 'green' ? '#d4edda' : '#f8d7da'; ?>; 
                                   color: <?= $tipo_mensaje === 'green' ? '#155724' : '#721c24'; ?>;">
            <?= htmlspecialchars($mensaje) ?>
        </div>
    <?php endif; ?>

    <form method="POST">
        <?php if ($editando): ?>
            <input type="hidden" name="id_materia" value="<?= $data['id_materia'] ?>">
        <?php endif; ?>

        <label>Nombre de la Materia</label>
        <input type="text" name="nombre" 
               value="<?= $editando ? htmlspecialchars($data['nombre']) : '' ?>" 
               required maxlength="100">

        <label>Carrera</label>
        <select name="id_carrera" required>
            <option value="">-- Seleccione una carrera --</option>
            <?php 
            $carreras->data_seek(0);
            while($c = $carreras->fetch_assoc()): ?>
                <option value="<?= $c['id_carrera'] ?>"
                    <?= ($editando && $c['id_carrera'] == $data['id_carrera']) ? 'selected' : '' ?>>
                    <?= htmlspecialchars($c['nombre']) ?>
                </option>
            <?php endwhile; ?>
        </select>

        <button type="submit" name="<?= $editando ? 'actualizar' : 'guardar' ?>" class="btn">
            <?= $editando ? "Actualizar Materia" : "Guardar Materia" ?>
        </button>

        <?php if ($editando): ?>
            <a href="materias3.php">Cancelar</a>
        <?php endif; ?>
    </form>

    <hr>

    <h2>Lista de Materias</h2>

    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>Nombre de la Materia</th>
                <th>Carrera</th>
                <th>Acciones</th>
            </tr>
        </thead>
        <tbody>
            <?php while($row = $materias->fetch_assoc()): 
                $carrera = $obj->seleccionarGenerico("carreras", "id_carrera", $row['id_carrera']);
            ?>
            <tr>
                <td><?= $row['id_materia'] ?></td>
                <td><?= htmlspecialchars($row['nombre']) ?></td>
                <td><?= htmlspecialchars($carrera['nombre'] ?? 'Sin carrera') ?></td>
                <td>
                    <a href="materias3.php?editar=<?= $row['id_materia'] ?>">Editar</a>
                    <a href="materias3.php?eliminar=<?= $row['id_materia'] ?>" 
                       onclick="return confirm('¿Estás seguro de eliminar esta materia?')" 
                       class="btn-del">Eliminar</a>
                </td>
            </tr>
            <?php endwhile; ?>
        </tbody>
    </table>

</div>

</body>
</html>