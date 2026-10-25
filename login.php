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
