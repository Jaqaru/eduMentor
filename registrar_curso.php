<?php
require_once __DIR__ . '/backend/auth.php';
require_once __DIR__ . '/backend/datos.php';

// Solo el docente tiene permiso para registrar cursos
protegerPagina('docente');

$usuario = obtenerUsuarioActual();
$mensajeExito = '';
$error = '';

if (isset($_SERVER['REQUEST_METHOD']) && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $codigo      = isset($_POST['codigo']) ? trim($_POST['codigo']) : '';
    $cursoNombre = isset($_POST['curso']) ? trim($_POST['curso']) : '';
    $ciclo       = isset($_POST['ciclo']) ? (int)$_POST['ciclo'] : 0;
    $descripcion = isset($_POST['descripcion']) ? trim($_POST['descripcion']) : '';

    if (empty($codigo) || empty($cursoNombre) || empty($ciclo) || empty($descripcion)) {
        $error = 'Por favor completa todos los campos del formulario.';
    } elseif (!preg_match('/^[0-9]{5,6}$/', $codigo)) {
        $error = 'El código del curso debe tener entre 5 y 6 dígitos numéricos.';
    } else {
        $nuevoCurso = guardarCurso($usuario['id'], $codigo, $cursoNombre, $ciclo, $descripcion);
        $mensajeExito = "El curso '{$nuevoCurso['nombre']}' fue registrado exitosamente con código {$nuevoCurso['codigo']}.";
    }
}

$tituloPagina = "Registrar Curso (Docente)";
require_once __DIR__ . '/includes/header.php';
?>
<main class="container estrecho">
  <section class="card" id="registro">
    <h2>Registro de Curso Académico</h2>
    <p class="intro">Módulo exclusivo para docentes. Registra una nueva asignatura para luego cargar sus contenidos y separatas autorizadas.</p>

    <?php if (!empty($mensajeExito)): ?>
      <div class="resultado">
        <p><strong>Estado:</strong> <span class="ok"><?php echo htmlspecialchars($mensajeExito); ?></span></p>
        <div style="margin-top: 15px;">
          <a class="btn btn-sm" href="cursos.php">Ir al listado de cursos</a>
          <a class="btn btn-sec btn-sm" href="materiales.php">Cargar material a este curso</a>
        </div>
      </div>
    <?php endif; ?>

    <?php if (!empty($error)): ?>
      <p class="alerta"><?php echo htmlspecialchars($error); ?></p>
    <?php endif; ?>

    <form action="registrar_curso.php" method="POST" class="form">
      <div class="form-group">
        <label for="docente">Docente responsable</label>
        <input class="input-control" type="text" id="docente" readonly value="<?php echo htmlspecialchars($usuario['nombre']); ?> (<?php echo htmlspecialchars($usuario['correo']); ?>)" style="background: #eef2f6;">
      </div>

      <div class="form-group">
        <label for="curso">Nombre del curso</label>
        <input class="input-control" type="text" id="curso" name="curso" required placeholder="Ej. Redes y Comunicaciones II" value="<?php echo isset($_POST['curso']) && empty($mensajeExito) ? htmlspecialchars($_POST['curso']) : ''; ?>">
      </div>

      <div class="form-group">
        <label for="codigo">Código del curso (5 o 6 dígitos numéricos)</label>
        <input class="input-control" type="text" id="codigo" name="codigo" required pattern="[0-9]{5,6}" placeholder="Ej. 101237" value="<?php echo isset($_POST['codigo']) && empty($mensajeExito) ? htmlspecialchars($_POST['codigo']) : ''; ?>">
      </div>

      <div class="form-group">
        <label for="ciclo">Ciclo académico</label>
        <select class="input-control" id="ciclo" name="ciclo" required>
          <option value="">-- Seleccione el ciclo --</option>
          <?php for ($i = 1; $i <= 10; $i++): ?>
            <option value="<?php echo $i; ?>" <?php echo (isset($_POST['ciclo']) && $_POST['ciclo'] == $i && empty($mensajeExito)) ? 'selected' : ''; ?>>
              Ciclo <?php echo $i; ?>
            </option>
          <?php endfor; ?>
        </select>
      </div>

      <div class="form-group">
        <label for="descripcion">Descripción y sumilla del curso</label>
        <textarea class="input-control" id="descripcion" name="descripcion" rows="4" maxlength="300" required placeholder="Describe brevemente los objetivos y contenidos del curso..."><?php echo isset($_POST['descripcion']) && empty($mensajeExito) ? htmlspecialchars($_POST['descripcion']) : ''; ?></textarea>
        <small id="contador">0 / 300</small>
      </div>

      <button type="submit" class="btn-submit">Guardar y dar de alta curso</button>
    </form>
    
    <p style="margin-top: 15px;"><a href="cursos.php">&laquo; Volver al listado de cursos</a></p>
  </section>
</main>
<?php require_once __DIR__ . '/includes/footer.php'; ?>
