<?php
$usuario = $_POST['usuario'] ?? '';
$nombre = $_POST['nombre'] ?? '';
$password = $_POST['password'] ?? '';
?>

<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>MSP - Pacientes</title>
  <link rel="shortcut icon" href="../img/Evosal.png">
  <link rel="stylesheet" href="../Style/login.css"/>
  <link rel="stylesheet" href="../Style/cuentaP.css"/>

</head>
<body>
  <!-- Header -->
  <header id="inicio" class="encabezado" style="background-color: #0057A0 !important;">
         
    <div class="top-header">
      <div class="contenedor-top">
          <div class="logo-texto">
            <a href="../index.html"><img src="../img/Evosal.png" alt="Logo Evosalud"></a>
            
          </div>
      </div>
        
  </header>


  <article class="contenido">
        <section class="formp-wrapper">
        <br>
        <!-- TÍTULO FUERA DEL FORMULARIO -->
        <h2 class="titulo-principal">Formulario de Datos del Paciente</h2>

        <div class="formp-layout">

            <!-- FORMULARIO A LA IZQUIERDA -->
            <div class="formp-container">
            <form method="post" action="guardarPaciente.php">
                <div class="formp-group">
                <label>Usuario:</label>
                <input type="text"  pattern="[A-Za-z\s]+" name="usuario" value="<?php echo $usuario; ?>" readonly>
                </div>

                <div class="formp-group">
                <label>Nombre completo:</label>
                <input type="text"  pattern="[A-Za-z\s]+" name="nombre_completo" value="<?php echo $nombre; ?>" readonly>
                </div>

                <div class="formp-group">
                <label>Contraseña:</label>
                <input type="text" name="password" value="<?php echo $password; ?>" readonly>
                </div>

                <div class="formp-group">
                <label>Cédula:</label>
                <input   type="text" name="cedula"  pattern="[0-9]{10}" maxlength="10" inputmode="numeric" required title="La cédula debe tener 10 números">
                </div>

                <div class="formp-group">
                <label>Correo:</label>
                <input type="email" name="correo" required>
                </div>

                <div class="formp-group">
                <label>Teléfono:</label>
                <input type="text" name="telefono" pattern="[0-9]{10}" maxlength="10" inputmode="numeric" required title="Su teléfono debe tener 10 números">
                </div>

                <div class="formp-group">
                <label>Dirección:</label>
                <input type="text" name="direccion">
                </div>

                <div class="formp-group">
                <label>Fecha de nacimiento:</label>
                <input type="date" name="fecha_nacimiento" id="fecha_nacimiento" required>
                </div>

                <div class="formp-group">
                <label>Género:</label>
                <select name="genero">
                    <option value="Masculino">Masculino</option>
                    <option value="Femenino">Femenino</option>
                    <option value="Otro">Otro</option>
                </select>
                </div>

                <input class="btn-ingresar" style="width:130px;" type="submit" value="Guardar">
            </form>
            </div>

            <!-- VIDEO A LA DERECHA -->
            <div class="formp-video">
            <video autoplay muted loop playsinline>
                <source src="../img/video.mp4" type="video/mp4">
                Tu navegador no soporta el video.
            </video>
            </div>

        </div>
        </section>

  </article>

  <!-- Footer -->
  <footer class="footer">
    <div class="footer-links">
      <a href="#">Términos y Condiciones</a>
      <a href="#">Políticas de Privacidad</a>
    </div>
    <p>Clínica Evosalud - 2025</p>
  </footer>

  <script>
    function formatoFechaLocal(date) {
      const año = date.getFullYear();
      const mes = String(date.getMonth() + 1).padStart(2, '0'); // Mes empieza en 0
      const dia = String(date.getDate()).padStart(2, '0');
      return `${año}-${mes}-${dia}`;
    }

    document.addEventListener("DOMContentLoaded", function () {
      const fechaInput = document.getElementById("fecha_nacimiento");

      if (fechaInput) {
        const hoy = new Date();
        const fechaMax = formatoFechaLocal(hoy);

        const hace150Anios = new Date();
        hace150Anios.setFullYear(hoy.getFullYear() - 150);
        const fechaMin = formatoFechaLocal(hace150Anios);

        fechaInput.max = fechaMax;
        fechaInput.min = fechaMin;
      }
    });
  </script>

  <script>
    // Script para validar cédula ecuatoriana al salir del campo
    document.addEventListener("DOMContentLoaded", function () {
      const cedulaInput = document.querySelector('input[name="cedula"]');

      cedulaInput.addEventListener("blur", function () {
        const cedula = cedulaInput.value.trim();

        if (!validarCedulaEcuatoriana(cedula)) {
          alert("La cédula ingresada no es válida.");
          cedulaInput.value = "";

          // Evitar cuelgue con timeout antes de hacer focus
          setTimeout(() => {
            cedulaInput.focus();
          }, 100);
        }
      });

      function validarCedulaEcuatoriana(cedula) {
        if (!/^\d{10}$/.test(cedula)) return false;

        const digitos = cedula.split("").map(Number);
        const provincia = parseInt(cedula.substring(0, 2));

        if (provincia < 1 || provincia > 24) return false;

        const tercerDigito = digitos[2];
        if (tercerDigito >= 6) return false;

        let suma = 0;
        for (let i = 0; i < 9; i++) {
          let valor = digitos[i];
          if (i % 2 === 0) {
            valor *= 2;
            if (valor > 9) valor -= 9;
          }
          suma += valor;
        }

        const verificador = (10 - (suma % 10)) % 10;
        return verificador === digitos[9];
      }
    });
  </script>

</body>
</html>
