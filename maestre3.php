<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once "sistemaestudiantilmodulo3.3.php";

$obj = new clasesistema();
$mensaje = "";
$tipo_mensaje = "red";

// -------- ELIMINAR --------
if (isset($_GET['eliminarmaestro'])) {
    $obj->eliminarGenerico("maestros", "id_maestro", $_GET['eliminarmaestro']);
    header("Location: maestre3.php");
    exit();
}

// -------- GUARDAR --------
if (isset($_POST['guardarmaestro'])) {
    if (!filter_var($_POST['correo'], FILTER_VALIDATE_EMAIL)) {
        $mensaje = "Correo electrónico inválido";
    } else {
        $datos = [
            "nombre"       => $_POST['nombre'],
            "apellido"     => $_POST['apellido'],
            "especialidad" => $_POST['especialidad'],
            "correo"       => $_POST['correo']
        ];

        $obj->insertarGenerico("maestros", $datos);
        header("Location: maestre3.php?success=1");
        exit();
    }
}

// -------- ACTUALIZAR --------
if (isset($_POST['actualizarmaestro'])) {
    if (!filter_var($_POST['correo'], FILTER_VALIDATE_EMAIL)) {
        $mensaje = "Correo electrónico inválido";
    } else {
        $datos = [
            "nombre"       => $_POST['nombre'],
            "apellido"     => $_POST['apellido'],
            "especialidad" => $_POST['especialidad'],
            "correo"       => $_POST['correo']
        ];

        $obj->actualizarGenerico("maestros", $datos, "id_maestro", $_POST['id_maestro']);
        header("Location: maestre3.php?success=1");
        exit();
    }
}

// -------- MODO EDICIÓN --------
$editando = false;
$data = null;

if (isset($_GET['editarmaestro'])) {
    $editando = true;
    $data = $obj->seleccionarGenerico("maestros", "id_maestro", $_GET['editarmaestro']);
}

// Mensaje de éxito
if (isset($_GET['success'])) {
    $mensaje = "Operación realizada correctamente";
    $tipo_mensaje = "green";
}

// -------- LISTAR --------
$maestros = $obj->obtenerGenerico("maestros");
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Gestión de Maestros</title>
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

    <h2><?= $editando ? "Editar Maestro" : "Nuevo Maestro" ?></h2>

    <?php if ($mensaje): ?>
        <div class="alerta" style="background-color: <?= $tipo_mensaje === 'green' ? '#d4edda' : '#f8d7da'; ?>; 
                                   color: <?= $tipo_mensaje === 'green' ? '#155724' : '#721c24'; ?>;">
            <?= htmlspecialchars($mensaje) ?>
        </div>
    <?php endif; ?>

    <form method="POST" action="maestre3.php">
        <?php if ($editando): ?>
            <input type="hidden" name="id_maestro" value="<?= $data['id_maestro'] ?>">
        <?php endif; ?>

        <label>Nombre</label>
        <input type="text" name="nombre" 
               value="<?= $editando ? htmlspecialchars($data['nombre'] ?? '') : '' ?>" 
               required>

        <label>Apellido</label>
        <input type="text" name="apellido" 
               value="<?= $editando ? htmlspecialchars($data['apellido'] ?? '') : '' ?>" 
               required>

        <label>Especialidad</label>
        <input type="text" name="especialidad" 
               value="<?= $editando ? htmlspecialchars($data['especialidad'] ?? '') : '' ?>" 
               required>

        <label>Correo Electrónico</label>
        <input type="email" name="correo" 
               value="<?= $editando ? htmlspecialchars($data['correo'] ?? '') : '' ?>" 
               required>

        <button type="submit" name="<?= $editando ? 'actualizarmaestro' : 'guardarmaestro' ?>" class="btn">
            <?= $editando ? "Actualizar Maestro" : "Guardar Maestro" ?>
        </button>

        <?php if ($editando): ?>
            <a href="maestre3.php" style="margin-left: 10px;">Cancelar</a>
        <?php endif; ?>
    </form>

    <hr>

    <h2>Lista de Maestros</h2>

    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>Nombre</th>
                <th>Apellido</th>
                <th>Especialidad</th>
                <th>Correo</th>
                <th>Acciones</th>
            </tr>
        </thead>
        <tbody>
            <?php while($row = $maestros->fetch_assoc()): ?>
            <tr>
                <td><?= $row['id_maestro'] ?></td>
                <td><?= htmlspecialchars($row['nombre']) ?></td>
                <td><?= htmlspecialchars($row['apellido']) ?></td>
                <td><?= htmlspecialchars($row['especialidad']) ?></td>
                <td><?= htmlspecialchars($row['correo']) ?></td>
                <td>
                    <a href="maestre3.php?editarmaestro=<?= $row['id_maestro'] ?>">Editar</a>
                    <a href="maestre3.php?eliminarmaestro=<?= $row['id_maestro'] ?>" 
                       onclick="return confirm('¿Estás seguro de eliminar este maestro?')" 
                       class="btn-del">Eliminar</a>
                </td>
            </tr>
            <?php endwhile; ?>
        </tbody>
    </table>

</div>

</body>
</html>