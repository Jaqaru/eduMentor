<?php
require_once __DIR__ . '/backend/auth.php';
require_once __DIR__ . '/backend/datos.php';

protegerPagina();

$usuario = obtenerUsuarioActual();
$cursos = obtenerCursos();
$cursoId = isset($_GET['curso_id']) ? (int)$_GET['curso_id'] : (isset($_POST['curso_id']) ? (int)$_POST['curso_id'] : (isset($cursos[0]['id']) ? $cursos[0]['id'] : 1));

$mensajeExito = '';
$error = '';
$resultadoQuiz = null;

// Docente: registrar nueva pregunta en el banco
if (isset($_SERVER['REQUEST_METHOD']) && $_SERVER['REQUEST_METHOD'] === 'POST' && esDocente() && isset($_POST['accion']) && $_POST['accion'] === 'crear_pregunta') {
    $enunciado       = isset($_POST['enunciado']) ? trim($_POST['enunciado']) : '';
    $opcionCorrecta  = isset($_POST['opcion_correcta']) ? trim($_POST['opcion_correcta']) : '';
    $distractor1     = isset($_POST['distractor1']) ? trim($_POST['distractor1']) : '';
    $distractor2     = isset($_POST['distractor2']) ? trim($_POST['distractor2']) : '';
    $distractor3     = isset($_POST['distractor3']) ? trim($_POST['distractor3']) : '';
    $explicacion     = isset($_POST['explicacion']) ? trim($_POST['explicacion']) : '';

    if (empty($enunciado) || empty($opcionCorrecta) || empty($distractor1)) {
        $error = 'Por favor completa el enunciado, la respuesta correcta y al menos una opción incorrecta.';
    } else {
        $incorrectas = array_filter([$distractor1, $distractor2, $distractor3]);
        guardarPreguntaBanco($cursoId, $enunciado, $opcionCorrecta, $incorrectas, $explicacion);
        $mensajeExito = 'Pregunta agregada exitosamente al banco de evaluación del curso.';
    }
}

// Estudiante: calificar examen
if (isset($_SERVER['REQUEST_METHOD']) && $_SERVER['REQUEST_METHOD'] === 'POST' && esEstudiante() && isset($_POST['accion']) && $_POST['accion'] === 'calificar_quiz') {
    $respuestasUsuario = isset($_POST['resp']) ? $_POST['resp'] : [];
    $resultadoQuiz = evaluarRespuestasQuiz($cursoId, $respuestasUsuario);
}

$preguntasBanco = obtenerPreguntasBanco($cursoId);
$cursoActual = obtenerCursoPorId($cursoId);

$tituloPagina = esDocente() ? "Banco de Preguntas" : "Módulo de Evaluaciones";
require_once __DIR__ . '/includes/header.php';
?>
<main class="container">
  <div class="acciones-top">
    <div>
      <h2><?php echo esDocente() ? 'Gestión de Banco de Preguntas' : 'Autoevaluación del Curso'; ?></h2>
      <p class="intro">
        <?php echo esDocente() 
          ? 'Administra los reactivos y preguntas de opción múltiple para que los estudiantes comprueben su aprendizaje.' 
          : 'Responde las preguntas generadas a partir de los materiales oficiales del curso para medir tu rendimiento.'; ?>
      </p>
    </div>
    <div>
      <form method="GET" action="evaluaciones.php" style="display: flex; gap: 8px; align-items: center;">
        <label for="curso_id" style="font-weight: 600;">Asignatura:</label>
        <select name="curso_id" id="curso_id" class="input-control" style="width: auto;" onchange="this.form.submit()">
          <?php foreach ($cursos as $c): ?>
            <option value="<?php echo $c['id']; ?>" <?php echo ($cursoId === $c['id']) ? 'selected' : ''; ?>>
              <?php echo htmlspecialchars($c['nombre']); ?>
            </option>
          <?php endforeach; ?>
        </select>
      </form>
    </div>
  </div>

  <?php if (!empty($mensajeExito)): ?>
    <div class="alerta-exito"><?php echo htmlspecialchars($mensajeExito); ?></div>
  <?php endif; ?>

  <?php if (!empty($error)): ?>
    <p class="alerta"><?php echo htmlspecialchars($error); ?></p>
  <?php endif; ?>

  <?php if (esDocente()): ?>
    <!-- ==========================================
         VISTA DEL DOCENTE: BANCO DE PREGUNTAS
         ========================================== -->
    <section class="card" style="margin-bottom: 25px;">
      <h3>+ Agregar Nueva Pregunta al Banco (<?php echo htmlspecialchars($cursoActual['nombre']); ?>)</h3>
      <p style="font-size: 0.9rem; color: #555; margin-bottom: 12px;">Formula un reactivo académico basado en las separatas o diapositivas cargadas.</p>
      
      <form action="evaluaciones.php" method="POST" class="form">
        <input type="hidden" name="accion" value="crear_pregunta">
        <input type="hidden" name="curso_id" value="<?php echo $cursoId; ?>">

        <div class="form-group">
          <label for="enunciado">Enunciado de la pregunta</label>
          <textarea class="input-control" id="enunciado" name="enunciado" rows="2" required placeholder="Ej. ¿Cuál es la principal ventaja de una arquitectura orientada a servicios frente a una monolítica?"></textarea>
        </div>

        <div class="form-group">
          <label for="opcion_correcta" style="color: #006655;">✓ Opción correcta</label>
          <input class="input-control" type="text" id="opcion_correcta" name="opcion_correcta" required placeholder="Escribe la respuesta correcta exacta">
        </div>

        <div class="grid" style="margin-bottom: 0;">
          <div class="form-group" style="flex: 1 1 200px;">
            <label for="distractor1">Opción incorrecta 1</label>
            <input class="input-control" type="text" id="distractor1" name="distractor1" required placeholder="Distractor A">
          </div>
          <div class="form-group" style="flex: 1 1 200px;">
            <label for="distractor2">Opción incorrecta 2</label>
            <input class="input-control" type="text" id="distractor2" name="distractor2" placeholder="Distractor B">
          </div>
          <div class="form-group" style="flex: 1 1 200px;">
            <label for="distractor3">Opción incorrecta 3</label>
            <input class="input-control" type="text" id="distractor3" name="distractor3" placeholder="Distractor C">
          </div>
        </div>

        <div class="form-group">
          <label for="explicacion">Fundamento académico / Fuente documental</label>
          <input class="input-control" type="text" id="explicacion" name="explicacion" placeholder="Ej. Fuente: Separata Unidad 2, pág. 10. Los microservicios permiten despliegues independientes...">
        </div>

        <button type="submit" class="btn">Guardar pregunta en el banco</button>
      </form>
    </section>

    <h3>Preguntas registradas en este curso (<?php echo count($preguntasBanco); ?>)</h3>
    <?php if (empty($preguntasBanco)): ?>
      <p class="alerta">No hay preguntas registradas aún para este curso. Agrega la primera en el formulario de arriba.</p>
    <?php else: ?>
      <div style="display: flex; flex-direction: column; gap: 15px;">
        <?php foreach ($preguntasBanco as $idx => $p): ?>
          <div class="card" style="border-left: 4px solid var(--verde);">
            <h4>#<?php echo ($idx + 1); ?>: <?php echo htmlspecialchars($p['enunciado']); ?></h4>
            <p style="margin: 8px 0; color: #006655;"><strong>Respuesta correcta:</strong> <?php echo htmlspecialchars($p['opcion_correcta']); ?></p>
            <p style="font-size: 0.9rem; color: #666;">
              <strong>Distractores:</strong> <?php echo htmlspecialchars(implode(' &bull; ', $p['opciones_incorrectas'])); ?>
            </p>
            <?php if (!empty($p['explicacion'])): ?>
              <p style="font-size: 0.85rem; color: #555; margin-top: 6px;"><em>Fundamento: <?php echo htmlspecialchars($p['explicacion']); ?></em></p>
            <?php endif; ?>
          </div>
        <?php endforeach; ?>
      </div>
    <?php endif; ?>

  <?php else: ?>
    <!-- ==========================================
         VISTA DEL ESTUDIANTE: AUTOEVALUACIÓN
         ========================================== -->
    <?php if ($resultadoQuiz !== null): ?>
      <div class="card" style="border-top: 5px solid <?php echo ($resultadoQuiz['nota'] >= 11) ? '#2ecc71' : '#e74c3c'; ?>; margin-bottom: 25px;">
        <h3>Resultado de la Evaluación</h3>
        <p style="font-size: 1.2rem; margin: 10px 0;">
          Calificación obtenida: <strong><?php echo $resultadoQuiz['nota']; ?> / 20</strong> 
          (<?php echo $resultadoQuiz['correctas']; ?> de <?php echo $resultadoQuiz['total']; ?> aciertos)
        </p>

        <?php if ($resultadoQuiz['nota'] >= 11): ?>
          <p class="alerta-exito">¡Buen trabajo! Has demostrado dominio de los conceptos autorizados en este curso.</p>
        <?php else: ?>
          <p class="alerta">Revisa las explicaciones de los reactivos y utiliza el Asistente RAG para reforzar los temas de la separata.</p>
        <?php endif; ?>

        <div style="margin-top: 20px;">
          <h4>Revisión de respuestas:</h4>
          <?php foreach ($resultadoQuiz['detalles'] as $idx => $d): ?>
            <div class="card" style="margin-top: 12px; border-left: 4px solid <?php echo $d['es_correcta'] ? '#2ecc71' : '#e74c3c'; ?>;">
              <p><strong>Pregunta #<?php echo ($idx + 1); ?>:</strong> <?php echo htmlspecialchars($d['enunciado']); ?></p>
              <p style="margin: 6px 0;">
                Tu respuesta: 
                <span style="color: <?php echo $d['es_correcta'] ? '#1e8449' : '#c0392b'; ?>; font-weight: 600;">
                  <?php echo !empty($d['respuesta_dada']) ? htmlspecialchars($d['respuesta_dada']) : '(Sin responder)'; ?>
                  <?php echo $d['es_correcta'] ? '✓ Correcto' : '✗ Incorrecto'; ?>
                </span>
              </p>
              <?php if (!$d['es_correcta']): ?>
                <p style="color: #1e8449;"><strong>Respuesta correcta:</strong> <?php echo htmlspecialchars($d['opcion_correcta']); ?></p>
              <?php endif; ?>
              <?php if (!empty($d['explicacion'])): ?>
                <div class="quiz-explicacion"><?php echo htmlspecialchars($d['explicacion']); ?></div>
              <?php endif; ?>
            </div>
          <?php endforeach; ?>
        </div>

        <div style="margin-top: 20px;">
          <a href="evaluaciones.php?curso_id=<?php echo $cursoId; ?>" class="btn">Intentar de nuevo</a>
          <a href="asistente.php?curso_id=<?php echo $cursoId; ?>" class="btn btn-sec">Preguntar al Asistente RAG</a>
        </div>
      </div>

    <?php elseif (empty($preguntasBanco)): ?>
      <div class="card">
        <p class="alerta">El docente todavía no ha configurado preguntas de evaluación para este curso.</p>
        <p><a href="asistente.php?curso_id=<?php echo $cursoId; ?>" class="btn">Consultar al Asistente</a></p>
      </div>

    <?php else: ?>
      <form action="evaluaciones.php" method="POST">
        <input type="hidden" name="accion" value="calificar_quiz">
        <input type="hidden" name="curso_id" value="<?php echo $cursoId; ?>">

        <p class="intro">
          Evaluación práctica de <strong><?php echo htmlspecialchars($cursoActual['nombre']); ?></strong>. 
          Selecciona la respuesta que consideres correcta para cada una de las siguientes <?php echo count($preguntasBanco); ?> preguntas:
        </p>

        <?php foreach ($preguntasBanco as $idx => $p): 
          $opciones = array_merge([$p['opcion_correcta']], $p['opciones_incorrectas']);
          shuffle($opciones);
        ?>
          <div class="quiz-card">
            <p class="quiz-pregunta">Pregunta <?php echo ($idx + 1); ?>: <?php echo htmlspecialchars($p['enunciado']); ?></p>
            
            <?php foreach ($opciones as $opc): ?>
              <label class="quiz-opcion">
                <input type="radio" name="resp[<?php echo $p['id']; ?>]" value="<?php echo htmlspecialchars($opc); ?>" required>
                <?php echo htmlspecialchars($opc); ?>
              </label>
            <?php endforeach; ?>
          </div>
        <?php endforeach; ?>

        <button type="submit" class="btn-submit" style="margin-top: 15px; font-size: 1.05rem;">
          Enviar y Calificar Evaluación
        </button>
      </form>
    <?php endif; ?>
  <?php endif; ?>

  <p style="margin-top: 25px;"><a href="dashboard.php">&laquo; Volver a mi panel</a></p>
</main>
<?php require_once __DIR__ . '/includes/footer.php'; ?>
