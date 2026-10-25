<?php
require_once __DIR__ . '/backend/auth.php';
require_once __DIR__ . '/backend/datos.php';

protegerPagina();

$usuario = obtenerUsuarioActual();
$cursos = obtenerCursos();
$cursoFiltroId = isset($_GET['curso_id']) ? (int)$_GET['curso_id'] : 0;

// Si es estudiante, muestra solo sus consultas; si es docente, ve todas las consultas de sus alumnos
$estudianteFiltroId = esEstudiante() ? $usuario['id'] : null;
$consultas = obtenerConsultas($estudianteFiltroId, $cursoFiltroId > 0 ? $cursoFiltroId : null);

$tituloPagina = esDocente() ? "Consultas de Estudiantes" : "Mi Historial de Consultas";
require_once __DIR__ . '/includes/header.php';
?>
<main class="container">
  <div class="acciones-top">
    <div>
      <h2><?php echo esDocente() ? 'Supervisión de Consultas de Estudiantes' : 'Mi Historial de Consultas Académicas'; ?></h2>
      <p class="intro">
        <?php echo esDocente() 
          ? 'Revisa las preguntas que tus estudiantes están formulando al asistente RAG, las fuentes citadas y la satisfacción registrada.' 
          : 'Consulta tus preguntas anteriores y revisa las explicaciones fundamentadas sin necesidad de volver a consultar.'; ?>
      </p>
    </div>
    <div>
      <form method="GET" action="historial.php" style="display: flex; gap: 8px; align-items: center;">
        <label for="filtroCurso" style="font-weight: 600; font-size: 0.9rem;">Curso:</label>
        <select name="curso_id" id="filtroCurso" class="input-control" style="width: auto;" onchange="this.form.submit()">
          <option value="0">Todos los cursos</option>
          <?php foreach ($cursos as $c): ?>
            <option value="<?php echo $c['id']; ?>" <?php echo ($cursoFiltroId === $c['id']) ? 'selected' : ''; ?>>
              <?php echo htmlspecialchars($c['nombre']); ?>
            </option>
          <?php endforeach; ?>
        </select>
      </form>
    </div>
  </div>

  <?php if (empty($consultas)): ?>
    <div class="card">
      <p class="alerta">No hay consultas registradas en este momento.</p>
      <?php if (esEstudiante()): ?>
        <p><a href="asistente.php" class="btn">Realizar una consulta al asistente</a></p>
      <?php endif; ?>
    </div>
  <?php else: ?>
    <div style="display: flex; flex-direction: column; gap: 18px;">
      <?php foreach ($consultas as $item): 
        $cursoObj = obtenerCursoPorId($item['curso_id']);
        $nombreCurso = $cursoObj ? $cursoObj['nombre'] : 'Curso general';
      ?>
        <article class="card" style="border-left: 5px solid var(--azul);">
          <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; margin-bottom: 10px;">
            <div>
              <span class="badge badge-azul"><?php echo htmlspecialchars($nombreCurso); ?></span>
              <?php if (esDocente()): ?>
                <span class="badge badge-verde">Estudiante: <?php echo htmlspecialchars($item['estudiante_nombre']); ?></span>
              <?php endif; ?>
            </div>
            <span style="font-size: 0.85rem; color: #666;"><?php echo htmlspecialchars($item['creado_en']); ?></span>
          </div>

          <h3 style="color: var(--azul-osc); font-size: 1.1rem; margin-bottom: 8px;">
            Q: <?php echo htmlspecialchars($item['pregunta']); ?>
          </h3>

          <div style="background: #fafcff; border: 1px solid #e1eaf2; border-radius: 6px; padding: 12px; margin-bottom: 10px;">
            <p style="margin-bottom: 8px;"><strong>Respuesta generada por el asistente:</strong></p>
            <p><?php echo nl2br(htmlspecialchars($item['respuesta'])); ?></p>
          </div>

          <!-- Fuentes citadas -->
          <?php if (!empty($item['fuentes'])): ?>
            <div style="margin-top: 10px;">
              <strong style="font-size: 0.88rem; color: var(--verde-osc);">Fuentes académicas verificadas:</strong>
              <?php foreach ($item['fuentes'] as $f): ?>
                <div class="fuente-box">
                  <strong><?php echo htmlspecialchars($f['documento']); ?></strong> &middot; 
                  <span><?php echo htmlspecialchars($f['unidad']); ?> (Pág. <?php echo htmlspecialchars($f['pagina']); ?>)</span>
                  <span class="fuente-texto">"<?php echo htmlspecialchars($f['texto']); ?>"</span>
                </div>
              <?php endforeach; ?>
            </div>
          <?php endif; ?>

          <!-- Estado de valoración -->
          <div style="margin-top: 12px; padding-top: 10px; border-top: 1px solid #f0f2f5; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; font-size: 0.88rem;">
            <div>
              <span>Calificación del estudiante: </span>
              <?php if ($item['utilidad'] === 'util'): ?>
                <span class="badge badge-verde">👍 Útil</span>
              <?php elseif ($item['utilidad'] === 'parcial'): ?>
                <span class="badge badge-amarillo">😐 Parcialmente útil</span>
              <?php elseif ($item['utilidad'] === 'no_util'): ?>
                <span class="badge badge-rojo">👎 No útil</span>
              <?php else: ?>
                <span class="badge" style="background: #e2e8f0;">Sin calificar</span>
              <?php endif; ?>

              <?php if (!empty($item['reporte'])): ?>
                <span class="badge badge-rojo" style="margin-left: 6px;">Reporte: <?php echo htmlspecialchars(str_replace('_', ' ', $item['reporte'])); ?></span>
              <?php endif; ?>
            </div>

            <?php if (esEstudiante()): ?>
              <a href="asistente.php?curso_id=<?php echo $item['curso_id']; ?>" class="btn btn-sec btn-sm">Preguntar de nuevo</a>
            <?php endif; ?>
          </div>
        </article>
      <?php endforeach; ?>
    </div>
  <?php endif; ?>

  <p style="margin-top: 25px;"><a href="dashboard.php">&laquo; Volver a mi panel</a></p>
</main>
<?php require_once __DIR__ . '/includes/footer.php'; ?>
