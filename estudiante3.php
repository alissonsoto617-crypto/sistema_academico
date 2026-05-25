<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once "sistemaestudiantilmodulo3.3.php";

$obj = new clasesistema();
$mensaje = "";
$tipo_mensaje = "red";

// -------- ELIMINAR --------
if (isset($_GET['eliminarestudiante'])) {
    $obj->eliminarGenerico("estudiantes", "id_estudiante", $_GET['eliminarestudiante']);
    header("Location: estudiante3.php");
    exit();
}

// -------- GUARDAR --------
if (isset($_POST['guardarestudiante'])) {
    if (!preg_match("/^[0-9]{4}-[0-9]{4}$/", $_POST['telefono'])) {
        $mensaje = "Teléfono inválido (formato: 1234-5678)";
    } elseif (!filter_var($_POST['correo'], FILTER_VALIDATE_EMAIL)) {
        $mensaje = "Correo electrónico inválido";
    } else {
        $datos = [
            "nombre"     => $_POST['nombre'],
            "apellido"   => $_POST['apellido'],
            "correo"     => $_POST['correo'],
            "telefono"   => $_POST['telefono'],
            "id_carrera" => $_POST['id_carrera']
        ];

        $obj->insertarGenerico("estudiantes", $datos);
        header("Location: estudiante3.php?success=1");
        exit();
    }
}

// -------- ACTUALIZAR --------
if (isset($_POST['actualizarestudiante'])) {
    if (!preg_match("/^[0-9]{4}-[0-9]{4}$/", $_POST['telefono'])) {
        $mensaje = "Teléfono inválido (formato: 1234-5678)";
    } elseif (!filter_var($_POST['correo'], FILTER_VALIDATE_EMAIL)) {
        $mensaje = "Correo electrónico inválido";
    } else {
        $datos = [
            "nombre"     => $_POST['nombre'],
            "apellido"   => $_POST['apellido'],
            "correo"     => $_POST['correo'],
            "telefono"   => $_POST['telefono'],
            "id_carrera" => $_POST['id_carrera']
        ];

        $obj->actualizarGenerico("estudiantes", $datos, "id_estudiante", $_POST['id_estudiante']);
        header("Location: estudiante3.php?success=1");
        exit();
    }
}

// -------- MODO EDICIÓN --------
$editando = false;
$data = null;

if (isset($_GET['editarestudiante'])) {
    $editando = true;
    $data = $obj->seleccionarGenerico("estudiantes", "id_estudiante", $_GET['editarestudiante']);
}

// Mensaje de éxito / error
if (isset($_GET['success'])) {
    $mensaje = "Operación realizada correctamente";
    $tipo_mensaje = "green";
}

// -------- LISTAR CON JOIN --------
$estudiantes = $obj->ejecutarConsulta("
    SELECT e.*, c.nombre AS carrera 
    FROM estudiantes e
    JOIN carreras c ON e.id_carrera = c.id_carrera
    ORDER BY e.apellido, e.nombre
");

// Obtener carreras para el formulario
$carreras = $obj->obtenerGenerico("carreras");
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Gestión de Estudiantes</title>
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

    <h2><?= $editando ? "Editar Estudiante" : "Nuevo Estudiante" ?></h2>

    <?php if ($mensaje): ?>
        <div class="alerta" style="background-color: <?= $tipo_mensaje === 'green' ? '#d4edda' : '#f8d7da'; ?>; 
                                   color: <?= $tipo_mensaje === 'green' ? '#155724' : '#721c24'; ?>;">
            <?= htmlspecialchars($mensaje) ?>
        </div>
    <?php endif; ?>

    <form method="POST" action="estudiante3.php">
        <?php if ($editando): ?>
            <input type="hidden" name="id_estudiante" value="<?= $data['id_estudiante'] ?>">
        <?php endif; ?>

        <label>Nombre</label>
        <input type="text" name="nombre" 
               value="<?= $editando ? htmlspecialchars($data['nombre'] ?? '') : '' ?>" required>

        <label>Apellido</label>
        <input type="text" name="apellido" 
               value="<?= $editando ? htmlspecialchars($data['apellido'] ?? '') : '' ?>" required>

        <label>Correo Electrónico</label>
        <input type="email" name="correo" 
               value="<?= $editando ? htmlspecialchars($data['correo'] ?? '') : '' ?>" required>

        <label>Teléfono (1234-5678)</label>
        <input type="text" name="telefono" placeholder="1234-5678"
               value="<?= $editando ? htmlspecialchars($data['telefono'] ?? '') : '' ?>" required>

        <label>Carrera</label>
        <select name="id_carrera" required>
            <option value="">-- Seleccione una carrera --</option>
            <?php 
            $carreras->data_seek(0);
            while($c = $carreras->fetch_assoc()): ?>
                <option value="<?= $c['id_carrera'] ?>"
                    <?= ($editando && $data['id_carrera'] == $c['id_carrera']) ? 'selected' : '' ?>>
                    <?= htmlspecialchars($c['nombre']) ?>
                </option>
            <?php endwhile; ?>
        </select>

        <button type="submit" name="<?= $editando ? 'actualizarestudiante' : 'guardarestudiante' ?>" class="btn">
            <?= $editando ? "Actualizar Estudiante" : "Guardar Estudiante" ?>
        </button>

        <?php if ($editando): ?>
            <a href="estudiante3.php">Cancelar</a>
        <?php endif; ?>
    </form>

    <hr>

    <h2>Lista de Estudiantes</h2>

    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>Nombre</th>
                <th>Apellido</th>
                <th>Correo</th>
                <th>Teléfono</th>
                <th>Carrera</th>
                <th>Acciones</th>
            </tr>
        </thead>
        <tbody>
            <?php while($fila = $estudiantes->fetch_assoc()): ?>
            <tr>
                <td><?= $fila['id_estudiante'] ?></td>
                <td><?= htmlspecialchars($fila['nombre']) ?></td>
                <td><?= htmlspecialchars($fila['apellido']) ?></td>
                <td><?= htmlspecialchars($fila['correo']) ?></td>
                <td><?= htmlspecialchars($fila['telefono']) ?></td>
                <td><?= htmlspecialchars($fila['carrera']) ?></td>
                <td>
                    <a href="estudiante3.php?editarestudiante=<?= $fila['id_estudiante'] ?>">Editar</a>
                    <a href="estudiante3.php?eliminarestudiante=<?= $fila['id_estudiante'] ?>" 
                       onclick="return confirm('¿Estás seguro de eliminar este estudiante?')" 
                       class="btn-del">Eliminar</a>
                </td>
            </tr>
            <?php endwhile; ?>
        </tbody>
    </table>

</div>

</body>
</html>