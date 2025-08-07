<?php
include '../conexion.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $usuario = $_POST['usuario'];
    $fecha = $_POST['fecha'];
    $hora = $_POST['hora'];
    $especialidad = $_POST['especialidad'];
    $medico = $_POST['medico'];

    $sql = "INSERT INTO citas_medicas (usuario, fecha, hora, especialidad, medico) 
            VALUES (:usuario, :fecha, :hora, :especialidad, :medico)";
    $stmt = $conn->prepare($sql);
    $stmt->execute([
        ':usuario' => $usuario,
        ':fecha' => $fecha,
        ':hora' => $hora,
        ':especialidad' => $especialidad,
        ':medico' => $medico
    ]);

    header("Location: perfilPaciente.php"); // Redirige al perfil
    exit;
}
?>
