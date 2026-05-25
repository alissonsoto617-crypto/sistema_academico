<?php
require_once "sistemaestudiantilmodulo3.3.php";
$obj = new clasesistema();

$mensaje = "";
$tipo_mensaje = "red";

// -------- CREAR INSCRIPCIÓN --------
if (isset($_POST['guardar'])) {
    $id_estudiante = $_POST['id_estudiante'];
    $id_grupo = $_POST['id_grupo'];
    $periodo = trim($_POST['periodo']);

    if (empty($id_estudiante) || empty($id_grupo) || empty($periodo)) {
        $mensaje = "Todos los campos son obligatorios";
    } 
    elseif (!preg_match("/^[0-9]{4}-[0-9]{2}$/", $periodo)) {
        $mensaje = "Formato de periodo inválido (Ej: 2026-01)";
    }
    else {

        // VALIDAR CUPO
        $cupo = $obj->ejecutarConsulta("
            SELECT cupo_maximo,
            (SELECT COUNT(*) FROM inscripciones WHERE id_grupo = $id_grupo) AS inscritos
            FROM grupos
            WHERE id_grupo = $id_grupo
        ")->fetch_assoc();

        if ($cupo['inscritos'] >= $cupo['cupo_maximo']) {
            $mensaje = "El grupo ya alcanzó su cupo máximo";
        }
        // VALIDAR DUPLICADO (misma materia y periodo)
        else {
            $validar = $obj->ejecutarConsulta("
                SELECT i.* 
                FROM inscripciones i
                JOIN grupos g ON i.id_grupo = g.id_grupo
                WHERE i.id_estudiante = $id_estudiante
                AND i.periodo = '$periodo'
                AND g.id_materia = (
                    SELECT id_materia FROM grupos WHERE id_grupo = $id_grupo
                )
            ");

            if ($validar->num_rows > 0) {
                $mensaje = "El estudiante ya está inscrito en esta materia en este periodo";
            }
            else {
                try {
                    $datos = [
                        "id_estudiante" => $id_estudiante,
                        "id_grupo"      => $id_grupo,
                        "periodo"       => $periodo
                    ];

                    $obj->insertarGenerico("inscripciones", $datos);
                    header("Location: inscripciones3.php?success=1");
                    exit();

                } catch (Exception $e) {
                    $mensaje = "Error de Inscripción: " . $e->getMessage();
                }
            }
        }
    }
}

// -------- ELIMINAR --------
if (isset($_GET['eliminar'])) {
    $obj->eliminarGenerico("inscripciones", "id_inscripcion", $_GET['eliminar']);
    header("Location: inscripciones3.php");
    exit();
}

// Mensaje de éxito
if (isset($_GET['success'])) {
    $mensaje = "¡Inscripción realizada con éxito!";
    $tipo_mensaje = "green";
}

// -------- CONSULTAS --------
$estudiantes = $obj->obtenerGenerico("estudiantes");

$grupos = $obj->ejecutarConsulta("
    SELECT g.id_grupo, m.nombre AS materia, g.cupo_maximo,
           (SELECT COUNT(*) FROM inscripciones WHERE id_grupo = g.id_grupo) AS inscritos
    FROM grupos g
    JOIN materias m ON g.id_materia = m.id_materia
");

$inscripciones = $obj->ejecutarConsulta("
    SELECT i.*, 
           e.nombre AS nombre_estudiante,
           e.apellido,
           m.nombre AS materia,
           CONCAT(ma.nombre, ' ', ma.apellido) AS nombre_maestro
    FROM inscripciones i
    JOIN estudiantes e ON i.id_estudiante = e.id_estudiante
    JOIN grupos g ON i.id_grupo = g.id_grupo
    JOIN materias m ON g.id_materia = m.id_materia
    JOIN maestros ma ON g.id_maestro = ma.id_maestro
    ORDER BY i.periodo DESC, e.apellido
");
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Gestión de Inscripciones</title>
    <link rel="stylesheet" href="implementaciones.css">
    <style>
        .alerta {
            padding: 15px;
            margin-bottom: 20px;
            border-radius: 8px;
            font-weight: 600;
        }
        .info-cupo {
            font-size: 0.9rem;
            color: #666;
        }
    </style>
</head>
<body>

<div class="container">

    <h2>Nueva Inscripción</h2>

    <?php if ($mensaje): ?>
        <div class="alerta" style="background-color: <?= $tipo_mensaje === 'green' ? '#d4edda' : '#f8d7da'; ?>; 
                                   color: <?= $tipo_mensaje === 'green' ? '#155724' : '#721c24'; ?>;">
            <?= htmlspecialchars($mensaje) ?>
        </div>
    <?php endif; ?>

    <form method="POST" action="inscripciones3.php">

        <label>Estudiante</label>
        <select name="id_estudiante" required>
            <option value="">-- Seleccione un estudiante --</option>
            <?php while($est = $estudiantes->fetch_assoc()): ?>
                <option value="<?= $est['id_estudiante'] ?>">
                    <?= htmlspecialchars($est['nombre'] . " " . $est['apellido']) ?>
                </option>
            <?php endwhile; ?>
        </select>

        <label>Grupo - Materia</label>
        <select name="id_grupo" required>
            <option value="">-- Seleccione un grupo --</option>
            <?php while($gp = $grupos->fetch_assoc()): 
                $disponible = $gp['cupo_maximo'] - $gp['inscritos'];
            ?>
                <option value="<?= $gp['id_grupo'] ?>">
                    Grupo <?= $gp['id_grupo'] ?> - <?= htmlspecialchars($gp['materia']) ?> 
                    (Cupo: <?= $gp['inscritos'] ?>/<?= $gp['cupo_maximo'] ?>)
                </option>
            <?php endwhile; ?>
        </select>

        <label>Periodo (Ej: 2026-01)</label>
        <input type="text" name="periodo" placeholder="2026-01" required>

        <button type="submit" name="guardar" class="btn">Realizar Inscripción</button>
    </form>

    <hr>

    <h2>Listado de Inscripciones</h2>

    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>Estudiante</th>
                <th>Materia</th>
                <th>Maestro</th>
                <th>Grupo</th>
                <th>Periodo</th>
                <th>Acciones</th>
            </tr>
        </thead>
        <tbody>
            <?php while($ins = $inscripciones->fetch_assoc()): ?>
            <tr>
                <td><?= $ins['id_inscripcion'] ?></td>
                <td><?= htmlspecialchars($ins['nombre_estudiante'] . " " . $ins['apellido']) ?></td>
                <td><?= htmlspecialchars($ins['materia']) ?></td>
                <td><?= htmlspecialchars($ins['nombre_maestro']) ?></td>
                <td><?= $ins['id_grupo'] ?></td>
                <td><?= htmlspecialchars($ins['periodo']) ?></td>
                <td>
                    <a href="inscripciones3.php?eliminar=<?= $ins['id_inscripcion'] ?>" 
                       onclick="return confirm('¿Eliminar esta inscripción?')" 
                       class="btn-del">Eliminar</a>
                </td>
            </tr>
            <?php endwhile; ?>
        </tbody>
    </table>

</div>

</body>
</html>