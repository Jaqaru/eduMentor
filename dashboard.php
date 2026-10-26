<?php
require_once __DIR__ . '/backend/auth.php';
require_once __DIR__ . '/backend/datos.php';

// Proteger la página: solo usuarios con sesión
protegerPagina();

$usuario = obtenerUsuarioActual();
$stats = obtenerEstadisticas();
$cursos = obtenerCursos();

$tituloPagina = "Mi panel";
require_once __DIR__ . '/includes/header.php';
?>
<main class="container">
  <div class="acciones-top">
    <div>
      <h2>Hola, <?php echo htmlspecialchars($usuario['nombre']); ?></h2>
      <p class="intro">Rol en la plataforma: <strong class="badge <?php echo ($usuario['rol'] === 'docente') ? 'badge-azul' : 'badge-verde'; ?>"><?php echo strtoupper(htmlspecialchars($usuario['rol'])); ?></strong></p>
    </div>
    <div>
      <span style="font-size: 0.9rem; color: #555;">Semestre Académico 2026-I</span>
    </div>
  </div>

  <?php if ($usuario['rol'] === 'docente'): ?>
    <!-- ==========================================
         PANEL FUNCIONAL PARA EL DOCENTE
         ========================================== -->
    <div class="stats-grid">
      <div class="stat-card azul">
        <div class="stat-num"><?php echo count($cursos); ?></div>
        <div class="stat-label">Cursos a cargo</div>
      </div>
      <div class="stat-card">
        <div class="stat-num"><?php echo $stats['total_materiales']; ?></div>
        <div class="stat-label">Documentos autorizados</div>
      </div>
      <div class="stat-card amarillo">
        <div class="stat-num"><?php echo $stats['total_consultas']; ?></div>
        <div class="stat-label">Consultas de alumnos</div>
      </div>
      <div class="stat-card">
        <div class="stat-num"><?php echo $stats['total_banco_preguntas']; ?></div>
        <div class="stat-label">Preguntas en el banco</div>
      </div>
    </div>

    <h2>Herramientas del Docente</h2>
    <div class="grid">
      <!-- 1. Mis Cursos -->
      <article class="card">
        <h3>1. Mis Cursos</h3>
        <p>Administra los cursos asignados, sus códigos y descripciones académicas.</p>
        <div style="margin-top: 15px;">
          <a class="btn btn-sm" href="cursos.php">Ver mis cursos (<?php echo count($cursos); ?>)</a>
          <a class="btn btn-sec btn-sm" href="registrar_curso.php">+ Registrar nuevo curso</a>
        </div>
      </article>

      <!-- 2. Material Académico y Procesamiento RAG -->
      <article class="card">
        <h3>2. Material Académico</h3>
        <p>Carga separatas, diapositivas y guías. El sistema procesa los documentos en fragmentos para alimentar el asistente RAG.</p>
        <div style="margin-top: 15px;">
          <a class="btn btn-sm" href="materiales.php">Cargar y gestionar materiales</a>
        </div>
      </article>

      <!-- 3. Consultas de Estudiantes -->
      <article class="card">
        <h3>3. Preguntas de Estudiantes</h3>
        <p>Revisa qué están consultando los alumnos, qué respuestas recibieron y sus valoraciones de utilidad.</p>
        <div style="margin-top: 15px;">
          <a class="btn btn-sm" href="historial.php">Revisar historial de consultas</a>
        </div>
      </article>

      <!-- 4. Banco de Preguntas -->
      <article class="card">
        <h3>4. Banco de Preguntas</h3>
        <p>Crea preguntas de opción múltiple para que los alumnos practiquen con evaluaciones automáticas basadas en el curso.</p>
        <div style="margin-top: 15px;">
          <a class="btn btn-sm" href="evaluaciones.php">Gestionar banco de preguntas</a>
        </div>
      </article>

      <!-- 5. Estadísticas de Uso -->
      <article class="card">
        <h3>5. Estadísticas del Asistente</h3>
        <p>Monitorea el índice de utilidad de las respuestas, temas más frecuentes y reportes de calidad documental.</p>
        <div style="margin-top: 15px;">
          <a class="btn btn-sm" href="estadisticas.php">Ver métricas y reportes</a>
        </div>
      </article>
    </div>

  <?php else: ?>
    <!-- 
         PANEL FUNCIONAL PARA EL ESTUDIANTE
          -->
    <div class="grid">
      <!-- 1. Asistente RAG -->
      <a class="card card-link" href="asistente.php" aria-label="Abrir el asistente RAG">
        <h3>Consultar al Asistente RAG</h3>
        <div class="boceto" aria-hidden="true">
          <span class="b-linea b-der"></span>
          <span class="b-linea b-izq"></span>
          <span class="b-linea b-izq corta"></span>
          <span class="b-input"></span>
        </div>
        <p>Formula preguntas en lenguaje natural sobre tus asignaturas y recibe respuestas basadas exclusivamente en el material del docente con citas exactas.</p>
        <span class="btn">Abrir chat de tutoría &raquo;</span>
      </a>

      <!-- 2. Historial de Consultas -->
      <article class="card">
        <h3>Mi Historial de Consultas</h3>
        <p>Revisa las dudas que ya has consultado al asistente, las fuentes que te sirvieron de fundamento y tus calificaciones registradas.</p>
        <div style="margin-top: 20px;">
          <a class="btn" href="historial.php">Ver mi historial</a>
        </div>
      </article>

      <!-- 3. Módulo de Evaluaciones -->
      <article class="card">
        <h3>Módulo de Autoevaluación</h3>
        <p>Practica resolviendo preguntas del banco de reactivos autorizado de tus cursos para medir tu aprendizaje antes de los exámenes.</p>
        <div style="margin-top: 20px;">
          <a class="btn" href="evaluaciones.php">Comenzar evaluación</a>
        </div>
      </article>

      <!-- 4. Materiales de Estudio -->
      <article class="card">
        <h3>Materiales Oficiales del Curso</h3>
        <p>Accede a las separatas, diapositivas y guías de laboratorio oficiales subidas por tus docentes para este ciclo académico.</p>
        <div style="margin-top: 20px;">
          <a class="btn btn-sec" href="materiales.php">Ver material de estudio</a>
        </div>
      </article>
    </div>
  <?php endif; ?>
</main>
<?php require_once __DIR__ . '/includes/footer.php'; ?>
