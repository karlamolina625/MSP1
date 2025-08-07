<?php 
include("../conexion.php");
session_start();

// 1. Si viene la petición para actualizar el estado, la procesamos primero
if (isset($_POST['diagnosticar']) && isset($_POST['actualizar_cita_id'])) {
    $idCita = $_POST['actualizar_cita_id'];
    $sqlUpdate = "UPDATE citas_medicas SET estado = 'diagnosticado' WHERE id = ?";
    $stmtUpdate = $conn->prepare($sqlUpdate);
    $stmtUpdate->execute([$idCita]);

    // Redirigimos para evitar reenvío del formulario al recargar
    header("Location: " . $_SERVER['PHP_SELF']);
    exit();
}

// 2. Verificar usuario
if (!isset($_POST['usuario']) && !isset($_SESSION['usuario'])) {
    die("Faltan datos");
}

if (isset($_POST['usuario'])) {
    $usuario = $_POST['usuario'];
    $_SESSION['usuario'] = $usuario;
} else {
    $usuario = $_SESSION['usuario'];
}

// 3. Mapeo de usuarios a nombre y especialidad
$mapaUsuarios = [
    'stalinroche' => ['nombre' => 'Dr. Stalin Roche', 'especialidad' => 'Medicina General'],
    'pamelacarriel' => ['nombre' => 'Dra. Pamela Carriel', 'especialidad' => 'Pediatría'],
    'christiandonoso' => ['nombre' => 'Dr. Christian Donoso', 'especialidad' => 'Ginecología'],
    'karlamolina' => ['nombre' => 'Dra. Karla Molina', 'especialidad' => 'Cardiología'],
];

if (!array_key_exists($usuario, $mapaUsuarios)) {
    echo "<script>alert('Usuario no válido'); window.location.href = '../Modulo_Doctor/Doctor.html';</script>";
    exit();
}

// 4. Asignar valores correctos (corregido)
$nombreDoctor = $mapaUsuarios[$usuario]['nombre'];
$especialidadDoctor = $mapaUsuarios[$usuario]['especialidad'];
$_SESSION['nombre'] = $nombreDoctor;
$_SESSION['especialidad'] = $especialidadDoctor;

// 5. Citas pendientes HOY (estado NULL)
// 5. Citas pendientes HOY (estado NULL)
date_default_timezone_set('America/Guayaquil');
$fechaHoy = date("Y-m-d");

$sqlCitasPendientes = "SELECT COUNT(*) AS total FROM citas_medicas WHERE medico = ? AND fecha = ? AND estado IS NULL";
$stmtPendientes = $conn->prepare($sqlCitasPendientes);
$stmtPendientes->execute([$nombreDoctor, $fechaHoy]);
$row = $stmtPendientes->fetch(PDO::FETCH_ASSOC);
$citasPendientesHoy = $row['total'] ?? 0;

// 5. Obtener la próxima cita disponible (estado NULL)
$sqlCita = "SELECT * FROM citas_medicas
            WHERE medico = ?
              AND fecha >= CURRENT_DATE
              AND estado IS NULL
            ORDER BY fecha ASC, hora ASC
            LIMIT 1";

$stmtCita = $conn->prepare($sqlCita);
$stmtCita->execute([$nombreDoctor]);
$cita = $stmtCita->fetch(PDO::FETCH_ASSOC);

// Inicializar variables
$paciente = $fecha = $hora = $especialidad = $estado = "No disponible";

if ($cita) {
    $paciente = $cita['usuario'];
    $fecha = date("d/m/Y", strtotime($cita['fecha']));
    $hora = date("H:i", strtotime($cita['hora']));
    $especialidad = $cita['especialidad'];
    $estado = $cita['estado'] ?? "No disponible";
}

// 6. Obtener todas las citas del médico
$sqlCitasDia = "SELECT usuario, fecha, hora, especialidad, estado 
                FROM citas_medicas
                WHERE medico = ?
                  AND fecha = CURRENT_DATE
                ORDER BY hora ASC";

$stmtCitasDia = $conn->prepare($sqlCitasDia);
$stmtCitasDia->execute([$nombreDoctor]);
$citasDia = $stmtCitasDia->fetchAll(PDO::FETCH_ASSOC);

// Separar datos para uso en el HTML
$pacientes = [];
$horas = [];
$especialidades = [];
$estados = [];

foreach ($citasDia as $citaItem) {
    $pacientes[] = $citaItem['usuario'];
    $horas[] = date("H:i", strtotime($citaItem['hora']));
    $especialidades[] = $citaItem['especialidad'];
    $estados[] = $citaItem['estado'] ?? 'No disponible';
}

// Log si no hay citas
if (!$citasDia) {
    error_log("No hay citas para $nombreDoctor el día " . date('Y-m-d'));
}

$fechaHoy = date('Y-m-d'); 


// Determinar imagen del doctor según nombre de usuario
$extensiones = ['jpg', 'jpeg', 'png', 'webp'];
$rutaImagen = '../img/default.png'; // Imagen de respaldo

foreach ($extensiones as $ext) {
    $posibleRuta = "../img/" . $usuario . "." . $ext;
    if (file_exists($posibleRuta)) {
        $rutaImagen = $posibleRuta;
        break;
    }
}



?>


<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Ministerio de Salud Pública</title>
  <link rel="shortcut icon" href="imgDp/Evosal.png">
  <link rel="stylesheet" href="../Style/cuentaP.css">
  <link rel="stylesheet" href="../Style/index.css">
</head>

<body>
  <!-- Header superior -->
  <header id="inicio">       
    <div class="top-header">
      <div class="contenedor-top">
        <div class="logo-texto">
          <a href="../index.html"><img src="../img/Evosal.png" alt="Logo Evosalud"></a>
        </div>
        <h2 class="titulo-header">El Nuevo Ecuador</h2>
        <a href="../Modulo_Doctor/Doctor.html" class="btn" style="width:160px;">Cerrar Sesión</a>
      </div>
    </div>

    <!-- Navegación principal -->
    <nav class="navegacion">
      <ul class="contenedor-nav">
        <li><a href="#inicio">Inicio</a></li>
        <li><a href="#proximaCita">Próxima Cita</a></li>
        <li><a href="#cita">Citas</a></li>
        <li><a href="#resultados">Laboratorio</a></li>
      </ul>
    </nav>    
  </header>

  <br><br> <br><br> <br><br>

  <!-- Contenido principal -->
  <main>
    <section id="inicio" class="perfil-contenedor" style="display: flex; align-items: stretch; gap: 2rem;">
      <!-- Panel principal -->
      <div class="perfil-paciente">
        <h2 class="titulo-seccion">Bienvenido, <?php echo htmlspecialchars($_SESSION['nombre']); ?></h2>
        <ul class="perfil-lista">
          <li><strong>Especialidad:</strong> <?php echo htmlspecialchars($_SESSION['especialidad']); ?></li>
          <li><strong>Fecha actual:</strong> 
            <?php
              date_default_timezone_set('America/Guayaquil');
              setlocale(LC_TIME, 'es_ES.UTF-8');
              echo strftime("%d de %B de %Y");
            ?>
          </li>
          <li><strong>Citas pendientes hoy:</strong> <?php echo $citasPendientesHoy; ?></li>
        </ul>

        <h3 class="titulo-seccion">Notas Rápidas</h3>
        <div class="formp-group">
          <textarea rows="4" style="width: 100%; border-radius: 10px; border: 1px solid #ccc; padding: 10px; background-color: var(--color-lectura); resize: vertical;"></textarea>
        </div>
      </div>



      <!-- Imagen del doctor -->
      <div class="perfil-imagenes">
        <img src="<?php echo $rutaImagen; ?>" alt="Foto del doctor"
          style="height: 100%; max-height: 500px; width: 280px; object-fit: cover; border-radius: 1rem; box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);">
      </div>

    </section>

    <section id="proximaCita" class="citas-medicas">
      <div class="tarjeta" style="max-width: 800px; margin: auto;">
        <h2 class="titulo-seccion" style="display: flex; align-items: center; gap: 0.5rem;">
          🗓️ Próxima Cita
        </h2>
        <ul class="perfil-lista" style="grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));">
          <li><strong>Paciente:</strong> <?php echo htmlspecialchars($paciente); ?></li>
          <li><strong>Fecha:</strong> <?php echo htmlspecialchars($fecha); ?></li>
          <li><strong>Hora:</strong> <?php echo htmlspecialchars($hora); ?></li>
          <li><strong>Especialidad:</strong> <?php echo htmlspecialchars($especialidad); ?></li>
          <li><strong>Estado:</strong> 
            <?php echo htmlspecialchars($estado); ?>
            <?php if ($cita && ($estado === null || $estado === "" || $estado === "No disponible") && $cita['fecha'] === $fechaHoy): ?>
              <form method="POST" style="display:inline;">
                <input type="hidden" name="actualizar_cita_id" value="<?php echo htmlspecialchars($cita['id']); ?>">
                <input type="hidden" name="usuario" value="<?php echo htmlspecialchars($usuario); ?>">
                <input type="hidden" name="password" value="<?php echo htmlspecialchars($_POST['password']); ?>">
                <button type="submit" name="diagnosticar" style="margin-left:10px;">Marcar como Diagnosticado</button>
              </form>
            <?php endif; ?>
          </li>
        </ul>
      </div>
    </section>


    <section id="cita" class="citas-medicas">
      <h2 class="titulo-seccion">Citas Proximas</h2>
      <table>
        <thead>
          <tr>
            <th>Paciente</th>
            <th>Fecha</th>
            <th>Hora</th>
            <th>Especialidad</th>
            <th>Estado</th>
          </tr>
        </thead>
        <tbody>
          <?php if (count($pacientes) > 0): ?>
            <?php for ($i = 0; $i < count($pacientes); $i++): ?>
              <tr>
                <td><?php echo htmlspecialchars($pacientes[$i]); ?></td>
                <td><?php echo htmlspecialchars(date("d/m/Y", strtotime($citasDia[$i]['fecha']))); ?></td>
                <td><?php echo htmlspecialchars($horas[$i]); ?></td>
                <td><?php echo htmlspecialchars($especialidades[$i]); ?></td>
                <td><?php echo htmlspecialchars($estados[$i]); ?></td>
              </tr>
            <?php endfor; ?>
          <?php else: ?>
            <tr>
              <td colspan="4" style="text-align:center;">No hay citas para hoy.</td>
            </tr>
          <?php endif; ?>
        </tbody>
      </table>
    </section>


  </main>

  <!-- Pie de página -->
  <footer id="contactenos" class="footer-seccion">
    <div class="contenedor-footer">

        <!-- Logo -->
        <div class="footer-logo">
        <img src="../img/Evosal.png" alt="Clínica Evosalud Logo">
        </div>

        <!-- Datos de contacto -->
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

        <!-- Mapa -->
        <div class="footer-mapa">
        <h3>¡Visítanos!</h3>
        <iframe
            src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d997.2649749441351!2d-78.48025983048759!3d-0.1783401313175207!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x91d59a91c742f253%3A0x4afc2c6488eb0b38!2sAv.%20de%20los%20Shyris%20%26%206%20De%20Diciembre%2C%20Quito%20170135!5e0!3m2!1ses!2sec!4v1622739487761!5m2!1ses!2sec"
            width="100%" height="150" style="border:0;" allowfullscreen loading="lazy">
        </iframe>
        </div>

    </div>
    
        <!-- Texto de derechos reservado -->
        <div class="footer-copy">
            © 2025 Clínica Evosalud. Todos los derechos reservados.
        </div>

  </footer>
</body>
</html>
