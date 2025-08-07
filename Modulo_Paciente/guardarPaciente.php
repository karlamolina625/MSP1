<?php
include '../conexion.php';  // Incluye conexión y crea $conn (PDO)

$usuario         = $_POST['usuario'] ?? '';
$password        = $_POST['password'] ?? '';
$nombre_completo = $_POST['nombre_completo'] ?? '';
$cedula          = $_POST['cedula'] ?? '';
$correo          = $_POST['correo'] ?? '';
$telefono        = $_POST['telefono'] ?? '';
$direccion       = $_POST['direccion'] ?? '';
$fecha_nacimiento = $_POST['fecha_nacimiento'] ?? null;
$genero          = $_POST['genero'] ?? '';

// Verificar que no exista la cédula ni el usuario
$sqlVerificar = "SELECT 1 FROM pacientes WHERE cedula = :cedula OR usuario = :usuario";
$stmtVerificar = $conn->prepare($sqlVerificar);
$stmtVerificar->execute([':cedula' => $cedula, ':usuario' => $usuario]);

if ($stmtVerificar->fetch()) {
    echo "<script>alert('La cédula o el usuario ya están registrados.'); window.location.href='Paciente.html';</script>";
    exit;
}

// Insertar nuevo paciente sin cifrar el password
$sqlInsertar = "INSERT INTO pacientes 
    (usuario, password, nombre_completo, cedula, correo, telefono, direccion, fecha_nacimiento, genero) 
    VALUES (:usuario, :password, :nombre_completo, :cedula, :correo, :telefono, :direccion, :fecha_nacimiento, :genero)";

$stmtInsertar = $conn->prepare($sqlInsertar);

try {
    $stmtInsertar->execute([
        ':usuario'         => $usuario,
        ':password'        => $password, // sin hash
        ':nombre_completo' => $nombre_completo,
        ':cedula'          => $cedula,
        ':correo'          => $correo,
        ':telefono'        => $telefono,
        ':direccion'       => $direccion,
        ':fecha_nacimiento'=> $fecha_nacimiento ?: null,
        ':genero'          => $genero,
    ]);
} catch (PDOException $e) {
    echo "<script>alert('Error al registrar paciente: " . addslashes($e->getMessage()) . "'); window.location.href='Paciente.html';</script>";
    exit;
}

echo "<script>alert('Registro exitoso. Ahora puede iniciar sesión.'); window.location.href='Paciente.html';</script>";
exit;
