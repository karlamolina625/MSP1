<?php
include '../conexion.php';
session_start();

if (!isset($_SESSION['usuario'])) {
    header("Location: ../Modulo_Paciente/Paciente.html");
    exit;
}

$usuario = $_SESSION['usuario'];
$mensaje = "";

// Mapear especialidades a médicos
$mapa_medico = [
    "Medicina General" => "Dr. Stalin Roche",
    "Pediatría" => "Dra. Pamela Carriel",
    "Ginecología" => "Dr. Christian Donoso",
    "Cardiología" => "Dra. Karla Molina"
];

$especialidades = array_keys($mapa_medico);

// Obtener citas del usuario
$stmt = $conn->prepare("SELECT * FROM citas_medicas WHERE usuario = :usuario ORDER BY fecha, hora");
$stmt->execute([':usuario' => $usuario]);
$citas = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Procesar actualización
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['id'])) {
    $id = $_POST['id'];
    $fecha = $_POST['fecha'] ?? "";
    $hora = $_POST['hora'] ?? "";
    $especialidad = $_POST['especialidad'] ?? "";

    if (!in_array($especialidad, $especialidades)) {
        $mensaje = "Especialidad inválida.";
    } else {
        $medico = $mapa_medico[$especialidad];

        if ($fecha < date('Y-m-d')) {
            $mensaje = "La fecha debe ser actual o futura.";
        } else {
            $dia = date('N', strtotime($fecha));
            if ($dia > 5) {
                $mensaje = "Solo se permiten días entre lunes y viernes.";
            } else {
                $validas = [];
                for ($h = 8; $h <= 16; $h++) {
                    foreach ([0, 30] as $m) {
                        if ($h === 16 && $m === 30) continue;
                        $validas[] = sprintf("%02d:%02d", $h, $m);
                    }
                }
                if (!in_array($hora, $validas)) {
                    $mensaje = "Hora inválida.";
                } else {
                    $stmt = $conn->prepare("SELECT COUNT(*) FROM citas_medicas WHERE fecha = :fecha AND hora = :hora AND medico = :medico AND id != :id");
                    $stmt->execute([':fecha' => $fecha, ':hora' => $hora, ':medico' => $medico, ':id' => $id]);
                    if ($stmt->fetchColumn() > 0) {
                        $mensaje = "La hora ya está ocupada para ese médico.";
                    } else {
                        $stmt = $conn->prepare("UPDATE citas_medicas SET fecha = :fecha, hora = :hora, especialidad = :especialidad, medico = :medico WHERE id = :id AND usuario = :usuario");
                        $stmt->execute([
                            ':fecha' => $fecha,
                            ':hora' => $hora,
                            ':especialidad' => $especialidad,
                            ':medico' => $medico,
                            ':id' => $id,
                            ':usuario' => $usuario
                        ]);
                        $mensaje = "✅ Cita actualizada correctamente.";
                        header("Location: actualizar_cita.php");
                        exit;
                    }
                }
            }
        }
    }

    // Recargar citas tras error
    $stmt = $conn->prepare("SELECT * FROM citas_medicas WHERE usuario = :usuario ORDER BY fecha, hora");
    $stmt->execute([':usuario' => $usuario]);
    $citas = $stmt->fetchAll(PDO::FETCH_ASSOC);
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Actualizar Citas - Clínica Evosalud</title>
    <link rel="stylesheet" href="../Style/cuentaP.css">
    <style>
        main { max-width: 900px; margin: auto; padding: 30px; background: #f2f9ff; border-radius: 10px; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; background: white; }
        th, td { padding: 12px; border: 1px solid #ccc; text-align: center; }
        th { background-color: #0057a0; color: white; }
        select, input[type=date], input[type=time] { padding: 5px; }
        .btn-guardar { background: #007BFF; color: white; padding: 6px 12px; border: none; border-radius: 4px; cursor: pointer; }
        .mensaje { margin: 10px 0; padding: 10px; background: #d4edda; color: #155724; border: 1px solid #c3e6cb; border-radius: 5px; }
    </style>
</head>
<body>
<main>
    <h1>Actualizar Mis Citas</h1>

    <?php if ($mensaje): ?>
        <div class="mensaje"><?= htmlspecialchars($mensaje) ?></div>
    <?php endif; ?>

    <table>
        <thead>
        <tr>
            <th>Fecha</th>
            <th>Hora</th>
            <th>Especialidad</th>
            <th>Médico</th>
            <th>Acción</th>
        </tr>
        </thead>
        <tbody>
        <?php foreach ($citas as $cita): ?>
            <tr>
                <form method="POST">
                    <input type="hidden" name="id" value="<?= $cita['id'] ?>">
                    <td><input type="date" name="fecha" value="<?= $cita['fecha'] ?>" required></td>
                    <td>
                        <select name="hora" required>
                            <option value="">--</option>
                            <?php
                            for ($h = 8; $h <= 16; $h++):
                                foreach ([0, 30] as $m):
                                    if ($h === 16 && $m === 30) continue;
                                    $time = sprintf("%02d:%02d", $h, $m);
                            ?>
                            <option value="<?= $time ?>" <?= date('H:i', strtotime($cita['hora'])) == $time ? 'selected' : '' ?>><?= $time ?></option>
                            <?php endforeach; endfor; ?>
                        </select>
                    </td>
                    <td>
                        <select name="especialidad" required>
                            <?php foreach ($especialidades as $esp): ?>
                                <option value="<?= $esp ?>" <?= $cita['especialidad'] == $esp ? 'selected' : '' ?>><?= $esp ?></option>
                            <?php endforeach; ?>
                        </select>
                    </td>
                    <td><?= $mapa_medico[$cita['especialidad']] ?? 'N/A' ?></td>
                    <td><button type="submit" class="btn-guardar">Guardar</button></td>
                </form>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>

    <div style="margin-top: 20px;">
        <a href="../Modulo_Paciente/perfilPaciente.php">&larr; Volver</a>
    </div>
</main>
</body>
</html>
