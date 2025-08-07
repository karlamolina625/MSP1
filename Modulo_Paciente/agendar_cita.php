<?php
include '../conexion.php';
session_start();

if (!isset($_SESSION['usuario'])) {
    header("Location: ../Modulo_Paciente/Paciente.html");
    exit;
}

$usuario = $_SESSION['usuario'];
$mensaje = "";

// Relación entre especialidad y médico
$mapa_medico = [
    "Medicina General" => "Dr. Stalin Roche",
    "Pediatría" => "Dra. Pamela Carriel",
    "Ginecología" => "Dr. Christian Donoso",
    "Cardiología" => "Dra. Karla Molina"
];

$especialidades = array_keys($mapa_medico);

// Inicializar variables
$fecha = $_POST['fecha'] ?? "";
$especialidad = $_POST['especialidad'] ?? "";
$hora = $_POST['hora'] ?? "";
$horarios_disponibles = [];

if (!empty($fecha)) {
    $fecha_actual = date('Y-m-d');

    if ($fecha < $fecha_actual) {
        $mensaje = "La fecha seleccionada ya pasó. Elige una fecha actual o futura.";
    } else {
        $diaSemana = date('N', strtotime($fecha));

        if ($diaSemana >= 1 && $diaSemana <= 5) {
            for ($h = 8; $h <= 16; $h++) {
                foreach ([0, 30] as $min) {
                    if ($h === 16 && $min === 30) continue;
                    $hora_str = sprintf("%02d:%02d", $h, $min);
                    $horarios_disponibles[] = $hora_str;
                }
            }

            // Validar si ya se seleccionó una especialidad
            if ($especialidad && isset($mapa_medico[$especialidad])) {
                $medico = $mapa_medico[$especialidad];

                // Obtener horas ocupadas de ese médico ese día
                $stmt = $conn->prepare("SELECT hora FROM citas_medicas WHERE fecha = :fecha AND medico = :medico");
                $stmt->execute([':fecha' => $fecha, ':medico' => $medico]);
                $ocupadas = $stmt->fetchAll(PDO::FETCH_COLUMN);
                $horarios_disponibles = array_values(array_diff($horarios_disponibles, $ocupadas));
            }
        } else {
            $mensaje = "Solo se permiten citas de lunes a viernes.";
        }
    }
}

// Procesar agendamiento
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $fecha && $hora && $especialidad) {
    if (!in_array($especialidad, $especialidades)) {
        $mensaje = "Especialidad inválida.";
    } else {
        $medico = $mapa_medico[$especialidad];

        $stmt = $conn->prepare("SELECT COUNT(*) FROM citas_medicas WHERE fecha = :fecha AND hora = :hora AND medico = :medico");
        $stmt->execute([':fecha' => $fecha, ':hora' => $hora, ':medico' => $medico]);
        $existe = $stmt->fetchColumn();

        if ($existe > 0) {
            $mensaje = "La hora seleccionada ya está ocupada. Elige otra.";
        } else {
            $stmt = $conn->prepare("INSERT INTO citas_medicas (usuario, fecha, hora, especialidad, medico) VALUES (:usuario, :fecha, :hora, :especialidad, :medico)");
            $stmt->execute([
                ':usuario' => $usuario,
                ':fecha' => $fecha,
                ':hora' => $hora,
                ':especialidad' => $especialidad,
                ':medico' => $medico
            ]);

            $mensaje = "✅ Cita agendada correctamente.";
            $fecha = $hora = $especialidad = "";
            $horarios_disponibles = [];
        }
    }
}
?>



<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Clínica Evosalud - Agendar Cita</title>
  <link rel="shortcut icon" href="../img/Evosal.png" />
  <link rel="stylesheet" href="../Style/cuentaP.css" />
  <link rel="stylesheet" href="../Style/index.css" />
  <link
    rel="stylesheet"
    href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css"
  />
  <style>
    main {
      padding: 40px 20px;
      max-width: 500px;
      margin: auto;
      background: #f5f9ff;
      border-radius: 10px;
      box-shadow: 0 0 15px rgba(0, 87, 160, 0.3);
    }
    .form-cita {
      display: flex;
      flex-direction: column;
      gap: 18px;
    }
    label {
      font-weight: 600;
      color: #003a75;
      margin-bottom: 5px;
    }
    input[type="date"],
    input[type="time"],
    select {
      padding: 10px;
      border-radius: 6px;
      border: 1px solid #ccc;
      font-size: 1rem;
      transition: border-color 0.3s ease;
    }
    input[type="date"]:focus,
    input[type="time"]:focus,
    select:focus {
      border-color: #0057a0;
      outline: none;
    }
    button.btn {
      background-color: #0057a0;
      color: white;
      padding: 12px;
      font-size: 1.1rem;
      font-weight: 600;
      border: none;
      border-radius: 8px;
      cursor: pointer;
      transition: background-color 0.3s ease;
    }
    button.btn:hover {
      background-color: #003a75;
    }
    .mensaje {
      font-weight: bold;
      padding: 10px;
      border-radius: 6px;
      margin-bottom: 15px;
      color: #004d00;
      background-color: #d4edda;
      border: 1px solid #c3e6cb;
    }
    .boton-actualizar {
      margin-top: 25px;
      text-align: center;
    }
    .boton-actualizar a {
      background-color: #008080;
      color: white;
      padding: 10px 20px;
      border-radius: 8px;
      font-weight: 600;
      text-decoration: none;
      transition: background-color 0.3s ease;
    }
    .boton-actualizar a:hover {
      background-color: #004d4d;
    }
  </style>
</head>
<body>

<header id="inicio">
  <div class="top-header">
    <div class="contenedor-top">
      <div class="logo-texto">
        <a href="../index.html"><img src="../img/Evosal.png" alt="Logo Evosalud" /></a>
      </div>
      <h2 class="titulo-header">El Nuevo Ecuador</h2>
      <a href="../Modulo_Paciente/Paciente.html" class="btn" style="width:160px;">Cerrar Sesión</a>
    </div>
  </div>
  <nav class="navegacion">
    <ul class="contenedor-nav">
      <li><a href="perfilPaciente.php">Mi Perfil</a></li>
      <li><a href="perfilPaciente.php">Citas Médicas</a></li>
      <li><a href="perfilPaciente.php">Médicos Disponibles</a></li>
      <li><a href="perfilPaciente.php">Resultados</a></li>
    </ul>
  </nav>
</header>

<main>
  <h1 class="titulo-seccion">Agendar Nueva Cita Médica</h1>

  <?php if ($mensaje): ?>
    <div class="mensaje"><?php echo htmlspecialchars($mensaje); ?></div>
  <?php endif; ?>

    <form method="POST" action="" class="form-cita" autocomplete="off">
    <label for="fecha">Fecha:</label>
    <input type="date" name="fecha" id="fecha" value="<?= htmlspecialchars($fecha) ?>" required onchange="this.form.submit()">

    <label for="hora">Hora disponible:</label>
    <select name="hora" required>
        <option value="">-- Seleccione --</option>
        <?php foreach ($horarios_disponibles as $h): ?>
        <option value="<?= $h ?>" <?= $h == $hora ? "selected" : "" ?>><?= $h ?></option>
        <?php endforeach; ?>
    </select>

    <label>Especialidad:</label>
    <select name="especialidad" required>
        <option value="">-- Seleccione --</option>
        <?php foreach ($especialidades as $esp): ?>
        <option value="<?= $esp ?>" <?= $esp == $especialidad ? "selected" : "" ?>><?= $esp ?></option>
        <?php endforeach; ?>
    </select>


    </select>

    <button type="submit" class="btn">Agendar Cita</button>
    </form>


  <div class="boton-actualizar">
    <a href="actualizar_cita.php" title="Actualizar citas">Actualizar Cita</a>
  </div>
</main>

<footer id="contactenos" class="footer-seccion">
  <div class="contenedor-footer">
    <div class="footer-logo">
      <img src="../img/Evosal.png" alt="Clínica Evosalud Logo" />
    </div>

    <div class="footer-contacto">
      <h3>Datos de contacto</h3>
      <p><strong>Teléfono:</strong> +593 123 456 789</p>
      <p><strong>Email:</strong> evosalud@salud.com.ec</p>
      <p><strong>Dirección:</strong> Av. de los Shyris y 6 de Diciembre, Quito</p>
    </div>

    <div class="footer-redes">
      <h3>Síguenos</h3>
      <div class="iconos-redes">
        <a href="https://www.facebook.com" class="icono" aria-label="Facebook">
          <i class="fab fa-facebook-f"></i>
        </a>
        <a href="https://www.instagram.com" class="icono" aria-label="Instagram">
          <i class="fab fa-instagram"></i>
        </a>
        <a href="https://wa.me/593994076669" class="icono" aria-label="WhatsApp">
          <i class="fab fa-whatsapp"></i>
        </a>
      </div>
    </div>

    <div class="footer-mapa">
      <h3>¡Visítanos!</h3>
      <iframe
        src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d997.2649749441351!2d-78.48025983048759!3d-0.1783401313175207!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x91d59a91c742f253%3A0x4afc2c6488eb0b38!2sAv.%20de%20los%20Shyris%20%26%206%20De%20Diciembre%2C%20Quito%20170135!5e0!3m2!1ses!2sec!4v1622739487761!5m2!1ses!2sec"
        width="100%"
        height="150"
        style="border: 0"
        allowfullscreen
        loading="lazy"
      ></iframe>
    </div>
  </div>

  <div class="footer-copy">© 2025 Clínica Evosalud. Todos los derechos reservados.</div>
</footer>

</body>
</html>
