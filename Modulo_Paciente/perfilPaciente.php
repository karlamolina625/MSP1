<?php
include '../conexion.php';

session_start();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Recibir datos del formulario de login
    $usuario  = $_POST['usuario'] ?? '';
    $password = $_POST['password'] ?? '';

    // Buscar paciente por usuario
    $stmt = $conn->prepare("SELECT * FROM pacientes WHERE usuario = :usuario");
    $stmt->execute([':usuario' => $usuario]);
    $paciente = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$paciente) {
        echo "<script>alert('Usuario no encontrado'); window.location.href='../Modulo_Paciente/Paciente.html';</script>";
        exit;
    }

    // Verificar contraseña sin hash
    if ($password !== $paciente['password']) {
        echo "<script>alert('Contraseña incorrecta'); window.location.href='../Modulo_Paciente/Paciente.html';</script>";
        exit;
    }

    // Guardar datos en sesión
    $_SESSION['usuario'] = $paciente['usuario'];
    $_SESSION['nombre_completo'] = $paciente['nombre_completo'];
    $_SESSION['cedula'] = $paciente['cedula'];
    $_SESSION['correo'] = $paciente['correo'];
    $_SESSION['telefono'] = $paciente['telefono'];
    $_SESSION['direccion'] = $paciente['direccion'];
    $_SESSION['fecha_nacimiento'] = $paciente['fecha_nacimiento'];
    $_SESSION['genero'] = $paciente['genero'];

} 

if (isset($_SESSION['usuario'])) {
    // Si ya hay sesión iniciada, cargar datos
    $usuario = $_SESSION['usuario'];
    $nombre = $_SESSION['nombre_completo']; // <-- aquí uso $nombre para coincidir con el HTML
    $cedula = $_SESSION['cedula'];
    $correo = $_SESSION['correo'];
    $telefono = $_SESSION['telefono'];
    $direccion = $_SESSION['direccion'];
    $fecha_nacimiento = $_SESSION['fecha_nacimiento'];
    $genero = $_SESSION['genero'];

} else {
    // No hay datos ni sesión, redirigir a login
    header('Location: ../Modulo_Paciente/Paciente.html');
    exit;
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Clínica Evosalud</title>
  <link rel="shortcut icon" href="../img/Evosal.png">
  <link rel="stylesheet" href="../Style/cuentaP.css" />
  <link rel="stylesheet" href="../Style/index.css">

  <link
    rel="stylesheet"
    href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css"
  />  
</head>
<body>

    <header id="inicio">
         
        <!-- Header superior -->
        <div class="top-header">
            <div class="contenedor-top">
            <div class="logo-texto">
                <a href="../index.html"><img src="../img/Evosal.png" alt="Logo Evosalud"></a>
            </div>
            <h2 class="titulo-header">El Nuevo Ecuador</h2>
            <a href="../Modulo_Paciente/Paciente.html" class="btn" style="width:160px;">Cerrar Sesión</a>
            </div>
        </div>

        <!-- Navegación principal -->
        <nav class="navegacion">
        <ul class="contenedor-nav">
            <li><a href="#perfil">Mi Perfil</a></li>
            <li><a href="#citas-medicas">Citas Médicas</a></li>
            <li><a href="#doctores">Médicos Disponibles</a></li>
            <li><a href="#resultados">Resultados</a></li>
        </ul>
        </nav>    
    </header>

    <br><br><br><br><br><br>

    <main> 

        <h1 class="titulo-seccion">Bienvenido/a, <?php echo htmlspecialchars($usuario); ?></h1>

        <section class="perfil-contenedor" id="perfil">
        <!-- Columna izquierda con 2 imágenes -->
        <div class="perfil-imagenes">
            <img src="../img/hor1.png" alt="Doctor 1">
            <img src="../img/hor2.png" alt="Doctor 2">
        </div>

        <!-- Columna derecha con datos del paciente -->
        <div class="perfil-paciente">
            <h2 class="titulo-seccion">Perfil del Paciente</h2>
            <ul class="perfil-lista">
                <li><strong>Usuario:</strong> <?php echo htmlspecialchars($usuario); ?></li>
                <li><strong>Nombre completo:</strong> <?php echo htmlspecialchars($nombre); ?></li>
                <li><strong>Cédula:</strong> <?php echo htmlspecialchars($cedula); ?></li>
                <li><strong>Correo:</strong> <?php echo htmlspecialchars($correo); ?></li>
                <li><strong>Teléfono:</strong> <?php echo htmlspecialchars($telefono); ?></li>
                <li><strong>Dirección:</strong> <?php echo htmlspecialchars($direccion); ?></li>
                <li><strong>Fecha de nacimiento:</strong> <?php echo htmlspecialchars($fecha_nacimiento); ?></li>
                <li><strong>Género:</strong> <?php echo htmlspecialchars($genero); ?></li>
            </ul>
        </div>
        </section>

        <section id="citas-medicas" class="citas-medicas">

            <table class="tabla-citas">
            <thead>
                <tr>
                <th>ID</th>
                <th>Fecha</th>
                <th>Hora</th>
                <th>Especialidad</th>
                <th>Médico</th>
                <th>Estado</th>
                <th>Acción</th>
                </tr>
            </thead>
            <tbody>
            <?php
            // Asegúrate de tener la conexión ya establecida en $conn
            // Y que $usuario esté definido previamente (por ejemplo desde $_SESSION)
            if (isset($usuario)) {
                try {
                    $sql = "SELECT * FROM citas_medicas WHERE usuario = :usuario";
                    $stmt = $conn->prepare($sql);
                    $stmt->execute([':usuario' => $usuario]);
                    $citas = $stmt->fetchAll(PDO::FETCH_ASSOC);

                    if ($citas) {
                        foreach ($citas as $cita) {
                            echo "<tr>";
                            echo "<td>" . htmlspecialchars($cita['id']) . "</td>";
                            echo "<td>" . htmlspecialchars($cita['fecha']) . "</td>";
                            echo "<td>" . htmlspecialchars($cita['hora']) . "</td>";
                            echo "<td>" . htmlspecialchars($cita['especialidad']) . "</td>";
                            echo "<td>" . htmlspecialchars($cita['medico']) . "</td>";
                            echo "<td>" . htmlspecialchars($cita['estado']) . "</td>";
                            echo "<td>
                                    <form method='POST' action='eliminar_cita.php' style='display:inline;'>
                                        <input type='hidden' name='id' value='" . $cita['id'] . "'>
                                        <button type='submit' class='btn-mini rojo'>Eliminar</button>
                                    </form>
                                </td>";
                            echo "</tr>";
                        }
                    } else {
                        echo "<tr><td colspan='6'>No hay citas registradas.</td></tr>";
                    }
                } catch (PDOException $e) {
                    echo "<tr><td colspan='6'>Error al obtener las citas: " . htmlspecialchars($e->getMessage()) . "</td></tr>";
                }
            } else {
                echo "<tr><td colspan='6'>Usuario no definido.</td></tr>";
            }
            ?>
            </tbody>

            </table>

            <a href="agendar_cita.php" class="btn">Agendar cita</a>



        </section>


        <section id="doctores" class="seccion fondo-claro">
            <h2 class="titulo-general">Agenda tu Cita</h2>

            <div class="contenedor-grid doctores">
            
                <div class="tarjeta">
                    <img src="../img/stalinroche.png" alt="Dr. Stalin Roche">
                    <h3 class="titulo-tarjeta">Stalin Roche</h3>
                    <p class="texto-secundario">Medicina General</p>
                    <a href="agendar_cita.php" class="btn">Agendar cita</a>
                </div>

                <div class="tarjeta">
                    <img src="../img/pamelacarriel.png" alt="Dra. Pamela Carriel">
                    <h3 class="titulo-tarjeta">Pamela Carriel</h3>
                    <p class="texto-secundario">pediatra</p>
                    <a href="agendar_cita.php" class="btn">Agendar cita</a>
                </div>

                <div class="tarjeta">
                    <img src="../img/karlamolina.png" alt="Dra. Karla Molina">
                    <h3 class="titulo-tarjeta">Karla Molina</h3>
                    <p class="texto-secundario">Cardiología</p>
                    <a href="agendar_cita.php" class="btn">Agendar cita</a>
                </div>

                <div class="tarjeta">
                    <img src="../img/christiandonoso.png" alt="Dr. Christian Donoso">
                    <h3 class="titulo-tarjeta">Christian Donoso</h3>
                    <p class="texto-secundario">Ginecología</p>
                    <a href="agendar_cita.php" class="btn">Agendar cita</a>
                </div>

            </div>
        </section>

        <section id="resultados" class="resultados-examenes">
        <h2 class="titulo-seccion">Resultados de Exámenes</h2>

        <div class="grid-auto">
            <div class="resultado-card">
            <h3 class="titulo-seccion">Hemograma</h3>
            <p><strong>Fecha:</strong> 2025-06-10</p>
            <p><strong>Resultado:</strong> Normal</p>
            <p><strong>Estado:</strong> <span class="entregado">Entregado</span></p>
            <a href="examenes/hemograma.pdf" class="btn-ver" target="_blank">Ver PDF</a>
            </div>

            <div class="resultado-card">
            <h3 class="titulo-seccion">Orina</h3>
            <p><strong>Fecha:</strong> 2025-06-02</p>
            <p><strong>Resultado:</strong> Leve infección</p>
            <p><strong>Estado:</strong> <span class="pendiente">Pendiente</span></p>
            <span class="btn-pendiente">No disponible</span>
            </div>

        </div>
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
