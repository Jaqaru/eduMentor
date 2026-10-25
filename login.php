<?php
require_once __DIR__ . '/backend/auth.php';

// Si ya tiene sesión, redirigir al panel
if (estaAutenticado()) {
    header('Location: dashboard.php');
    exit;
}

$error = '';
$exito = '';

if (isset($_GET['error']) && $_GET['error'] === 'requiere_login') {
    $error = 'Debes iniciar sesión para acceder a ese módulo.';
}

// Procesar formulario de login
if (isset($_SERVER['REQUEST_METHOD']) && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $correo = isset($_POST['correo']) ? trim($_POST['correo']) : '';
    $clave  = isset($_POST['clave']) ? trim($_POST['clave']) : '';

    if (empty($correo) || empty($clave)) {
        $error = 'Por favor ingresa tu correo y contraseña.';
    } else {
        if (iniciarSesion($correo, $clave)) {
            header('Location: dashboard.php');
            exit;
        } else {
            $error = 'Correo o contraseña incorrectos. Revisa los datos e inténtalo de nuevo.';
        }
    }
}

$tituloPagina = "Ingresar";
require_once __DIR__ . '/includes/header.php';
?>
<main class="container estrecho">
  <section class="card">
    <h2>Ingresar a EduMentor RAG</h2>
    <p class="intro">Inicia sesión con tu cuenta institucional para acceder a tus cursos y herramientas académicas.</p>
    
    <?php if (!empty($error)): ?>
      <p class="alerta"><?php echo htmlspecialchars($error); ?></p>
    <?php endif; ?>

    <form action="login.php" method="POST" class="form" id="loginForm">
      <div class="form-group">
        <label for="correo">Correo institucional</label>
        <input class="input-control" type="email" id="correo" name="correo" required placeholder="usuario@uns.edu.pe" value="<?php echo isset($_POST['correo']) ? htmlspecialchars($_POST['correo']) : ''; ?>">
      </div>
      
      <div class="form-group">
        <label for="clave">Contraseña</label>
        <input class="input-control" type="password" id="clave" name="clave" required minlength="6">
        <label class="check"><input type="checkbox" id="verClave"> Mostrar contraseña</label>
      </div>
      
      <button type="submit" class="btn-submit">Ingresar</button>
    </form>

    <div style="margin-top: 25px; padding-top: 15px; border-top: 1px solid #e1e8ed;">
      <p style="font-weight: 600; margin-bottom: 8px;">Acceso rápido de prueba:</p>
      <div style="display: flex; gap: 10px; flex-wrap: wrap;">
        <button type="button" class="btn btn-sm" onclick="cargarCredenciales('docente@uns.edu.pe', 'docente123')">
          Acceder como Docente
        </button>
        <button type="button" class="btn btn-sec btn-sm" onclick="cargarCredenciales('estudiante@uns.edu.pe', 'estudiante123')">
          Acceder como Estudiante
        </button>
      </div>
      <p class="nota">Cuentas: <code>docente@uns.edu.pe</code> (docente123) &middot; <code>estudiante@uns.edu.pe</code> (estudiante123)</p>
    </div>
  </section>
</main>

<script>
function cargarCredenciales(correo, clave) {
  document.getElementById('correo').value = correo;
  document.getElementById('clave').value = clave;
  document.getElementById('loginForm').submit();
}
</script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
