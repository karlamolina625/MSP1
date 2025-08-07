<?php
include '../conexion.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = $_POST['id'];

    $stmt = $conn->prepare("DELETE FROM citas_medicas WHERE id = :id");
    $stmt->execute([':id' => $id]);

    header("Location: perfilPaciente.php");
    exit;
}
?>
