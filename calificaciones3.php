<?php
require_once "sistemaestudiantilmodulo3.3.php";
$obj = new clasesistema();

$mensaje = "";
$tipo_mensaje = "red";

// -------- CREAR CALIFICACIÓN --------
if (isset($_POST['guardar'])) {
    $id_inscripcion = $_POST['id_inscripcion'];
    $tareas = (float)$_POST['tareas'];
    $laboratorios = (float)$_POST['laboratorios'];
    $examen_final = (float)$_POST['examen_final'];

    if ($tareas < 0 || $tareas > 10 || $laboratorios < 0 || $laboratorios > 10 || $examen_final < 0 || $examen_final > 10) {
        $mensaje = "Todas las notas deben estar entre 0 y 10";
    } else {
        try {
            $datos = [
                "id_inscripcion" => $id_inscripcion,
                "tareas"         => $tareas,
                "laboratorios"   => $laboratorios,
                "examen_final"   => $examen_final
            ];

            $obj->insertarGenerico("calificaciones", $datos);
            header("Location: calificaciones3.php?success=1");
            exit();
        } catch (Exception $e) {
            $mensaje = "Error al registrar calificación: " . $e->getMessage();
        }
    }
}

// -------- ELIMINAR --------
if (isset($_GET['eliminar'])) {
    $obj->eliminarGenerico("calificaciones", "id_calificacion", $_GET['eliminar']);
    header("Location: calificaciones3.php");
    exit();
}

if (isset($_GET['success'])) {
    $mensaje = "Calificación guardada correctamente";
    $tipo_mensaje = "green";
}

// -------- CONSULTAS --------
$calificaciones = $obj->ejecutarConsulta("
    SELECT c.*, 
           e.nombre AS nombre_estudiante,
           e.apellido,
           m.nombre AS materia,
           i.id_grupo
    FROM calificaciones c
    JOIN inscripciones i ON c.id_inscripcion = i.id_inscripcion
    JOIN estudiantes e ON i.id_estudiante = e.id_estudiante
    JOIN grupos g ON i.id_grupo = g.id_grupo
    JOIN materias m ON g.id_materia = m.id_materia
    ORDER BY e.apellido, e.nombre
");

$inscripciones = $obj->ejecutarConsulta("
    SELECT i.id_inscripcion, 
           CONCAT(e.nombre, ' ', e.apellido) AS estudiante,
           m.nombre AS materia,
           g.id_grupo
    FROM inscripciones i
    JOIN estudiantes e ON i.id_estudiante = e.id_estudiante
    JOIN grupos g ON i.id_grupo = g.id_grupo
    JOIN materias m ON g.id_materia = m.id_materia
    LEFT JOIN calificaciones c ON i.id_inscripcion = c.id_inscripcion
    WHERE c.id_calificacion IS NULL
");
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Registro de Calificaciones</title>
    <link rel="stylesheet" href="implementaciones.css">
    <style>
        .alerta {
            padding: 15px;
            margin-bottom: 20px;
            border-radius: 8px;
            font-weight: 600;
        }
        .nota-final { font-size: 1.1rem; font-weight: 700; }
    </style>
</head>
<body>

<div class="container">

    <h2>Asignar Calificación</h2>

    <?php if ($mensaje): ?>
        <div class="alerta" style="background-color: <?= $tipo_mensaje === 'green' ? '#d4edda' : '#f8d7da'; ?>; 
                                   color: <?= $tipo_mensaje === 'green' ? '#155724' : '#721c24'; ?>;">
            <?= htmlspecialchars($mensaje) ?>
        </div>
    <?php endif; ?>

    <form method="POST" action="calificaciones3.php">
        <label>Seleccionar Estudiante / Materia</label>
        <select name="id_inscripcion" required>
            <option value="">-- Seleccione un estudiante sin calificación --</option>
            <?php while($i = $inscripciones->fetch_assoc()): ?>
                <option value="<?= $i['id_inscripcion'] ?>">
                    <?= htmlspecialchars($i['estudiante']) ?> → <?= htmlspecialchars($i['materia']) ?> (Grupo <?= $i['id_grupo'] ?>)
                </option>
            <?php endwhile; ?>
        </select>

        <label>Tareas (30%)</label>
        <input type="number" name="tareas" step="0.01" min="0" max="10" required>

        <label>Laboratorios (30%)</label>
        <input type="number" name="laboratorios" step="0.01" min="0" max="10" required>

        <label>Examen Final (40%)</label>
        <input type="number" name="examen_final" step="0.01" min="0" max="10" required>

        <button type="submit" name="guardar" class="btn">Guardar Calificación</button>
    </form>

    <hr>

    <h2>Registro de Notas</h2>

    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>Estudiante</th>
                <th>Materia</th>
                <th>Grupo</th>
                <th>Tareas</th>
                <th>Laboratorios</th>
                <th>Examen Final</th>
                <th>Nota Final</th>
                <th>Estado</th>
                <th>Acciones</th>
            </tr>
        </thead>
        <tbody>
            <?php while($c = $calificaciones->fetch_assoc()): ?>
            <tr>
                <td><?= $c['id_calificacion'] ?></td>
                <td><?= htmlspecialchars($c['nombre_estudiante'] . " " . $c['apellido']) ?></td>
                <td><?= htmlspecialchars($c['materia']) ?></td>
                <td><?= $c['id_grupo'] ?></td>
                <td><?= $c['tareas'] ?></td>
                <td><?= $c['laboratorios'] ?></td>
                <td><?= $c['examen_final'] ?></td>
                <td class="nota-final"><?= number_format($c['nota'], 2) ?></td>
                <td>
                    <?php if($c['nota'] >= 6): ?>
                        <span style="color:green;font-weight:bold;">Aprobado</span>
                    <?php else: ?>
                        <span style="color:red;font-weight:bold;">Reprobado</span>
                    <?php endif; ?>
                </td>
                <td>
                    <a href="?eliminar=<?= $c['id_calificacion'] ?>" 
                       class="btn-del" 
                       onclick="return confirm('¿Eliminar esta calificación?')">Eliminar</a>
                </td>
            </tr>
            <?php endwhile; ?>
        </tbody>
    </table>

</div>

</body>
</html>