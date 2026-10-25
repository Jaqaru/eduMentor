<?php
/**
 * EduMentor RAG - Inicio de Sesión
 * Aplicaciones Distribuidas I - Grupo 03
 */
require_once __DIR__ . '/backend/auth.php';

// Si ya tiene sesión activa, redirigir al panel
if (estaAutenticado()) {
    header('Location: dashboard.php');
    exit;
}

$error = '';
$exito = '';

if (isset($_GET['error']) && $_GET['error'] === 'requiere_login') {
    $error = 'Debes iniciar sesión para acceder a ese módulo.';
}

// Detección de petición API / AJAX que espera JSON
$esPeticionJson = (isset($_SERVER['HTTP_ACCEPT']) && stripos($_SERVER['HTTP_ACCEPT'], 'application/json') !== false)
               || (isset($_SERVER['CONTENT_TYPE']) && stripos($_SERVER['CONTENT_TYPE'], 'application/json') !== false)
               || (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest')
               || isset($_GET['json'])
               || (isset($_POST['format']) && $_POST['format'] === 'json');

// Procesar formulario o petición POST de login
if (isset($_SERVER['REQUEST_METHOD']) && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $correo = '';
    $clave  = '';

    // Si viene en payload JSON crudo (ej. fetch con JSON.stringify)
    if ($esPeticionJson && empty($_POST)) {
        $inputJson = json_decode(file_get_contents('php://input'), true);
        if (is_array($inputJson)) {
            $correo = isset($inputJson['correo']) ? trim($inputJson['correo']) : '';
            $clave  = isset($inputJson['clave']) ? trim($inputJson['clave']) : '';
        }
    } else {
        $correo = isset($_POST['correo']) ? trim($_POST['correo']) : '';
        $clave  = isset($_POST['clave']) ? trim($_POST['clave']) : '';
    }

    if (empty($correo) || empty($clave)) {
        $error = 'Por favor ingresa tu correo y contraseña.';
        if ($esPeticionJson) {
            header('Content-Type: application/json; charset=UTF-8');
            http_response_code(400);
            echo json_encode([
                'status'  => 'error',
                'mensaje' => $error
            ]);
            exit;
        }
    } else {
        if (iniciarSesion($correo, $clave)) {
            if ($esPeticionJson) {
                header('Content-Type: application/json; charset=UTF-8');
                $u = obtenerUsuarioActual();
                // Seguridad: solo devolver id, nombre, correo y rol (NUNCA la contraseña)
                echo json_encode([
                    'status'  => 'ok',
                    'usuario' => [
                        'id'     => (int)$u['id'],
                        'nombre' => $u['nombre'],
                        'correo' => $u['correo'],
                        'rol'    => $u['rol']
                    ]
                ]);
                exit;
            } else {
                header('Location: dashboard.php');
                exit;
            }
        } else {
            $error = 'Correo o contraseña incorrectos. Revisa los datos e inténtalo de nuevo.';
            if ($esPeticionJson) {
                header('Content-Type: application/json; charset=UTF-8');
                http_response_code(401);
                echo json_encode([
                    'status'  => 'error',
                    'mensaje' => $error
                ]);
                exit;
            }
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
        <input class="input-control" type="email" id="correo" name="correo" required placeholder="tu@correo.com">
      </div>
      
      <div class="form-group">
        <label for="clave">Contraseña</label>
        <input class="input-control" type="password" id="clave" name="clave" required minlength="6" placeholder="••••••••">
        <label class="check"><input type="checkbox" id="verClave"> Mostrar contraseña</label>
      </div>
      
      <button type="submit" class="btn-submit">Ingresar</button>
    </form>
  </section>
</main>
<?php require_once __DIR__ . '/includes/footer.php'; ?>
