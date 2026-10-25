<?php
$tituloPagina = "Inicio";
require_once __DIR__ . '/includes/header.php';
?>
<main>
  <section class="hero">
    <div class="hero-txt">
      <h1>Pregunta sobre tu curso y recibe respuestas con sus fuentes.</h1>
      <p>EduMentor RAG es una plataforma web de tutoría académica fundamentada en contenidos autorizados. El asistente responde exclusivamente con el material que el docente ha cargado: separatas, diapositivas y guías.</p>
      
      <?php if (!estaAutenticado()): ?>
        <a class="btn" href="login.php">Ingresar a la plataforma</a>
      <?php else: ?>
        <a class="btn" href="dashboard.php">Ir a mi panel (<?php echo htmlspecialchars($usuarioActual['rol']); ?>)</a>
      <?php endif; ?>
      <a class="btn btn-sec" href="#proyecto">Conocer el proyecto</a>
    </div>

    <div class="chat-demo" aria-label="Ejemplo de consulta">
      <p class="burbuja est" style="color: var(--azul-osc) !important; font-weight: 600 !important;">¿Cuál es la diferencia entre arquitectura cliente-servidor y arquitectura distribuida?</p>
      <p class="burbuja ia" style="color: var(--azul-osc) !important; font-weight: 600 !important;">En la cliente-servidor, un servidor central atiende a los clientes. En la distribuida, los componentes se reparten en varios nodos que cooperan a través de una red sin depender de un único punto de fallo.
        <small style="color: var(--verde-osc) !important; font-weight: 700 !important;">Fuente: Separata Unidad 1: Fundamentos de Arquitecturas Distribuidas, pág. 4</small>
      </p>
    </div>
  </section>

  <section class="container" id="proyecto">
    <h2>¿Para qué sirve?</h2>
    <p class="intro">Disminuye el riesgo de que la IA genere información no sustentada (alucinaciones): cada respuesta se basa estrictamente en los documentos académicos registrados por el docente y cita la fuente exacta.</p>
    
    <div class="grid">
      <article class="card">
        <h3>Estudiante</h3>
        <p>Selecciona un curso, formula preguntas en lenguaje natural, consulta las fuentes académicas citadas, valora la utilidad y practica con evaluaciones del banco de preguntas.</p>
        <?php if (esEstudiante()): ?>
          <a href="asistente.php" class="btn btn-sm">Abrir asistente</a>
        <?php endif; ?>
      </article>

      <article class="card">
        <h3>Docente</h3>
        <p>Registra sus cursos, carga y procesa documentos (separatas, diapositivas, guías), crea preguntas de autoevaluación, revisa las consultas de sus alumnos y analiza estadísticas de uso.</p>
        <?php if (esDocente()): ?>
          <a href="cursos.php" class="btn btn-sm">Gestionar cursos</a>
        <?php endif; ?>
      </article>

      <article class="card">
        <h3>Asistente RAG</h3>
        <p>Recupera primero los fragmentos más relevantes dentro de los materiales autorizados del curso y posteriormente genera una respuesta precisa citando unidad, documento y fragmento.</p>
      </article>
    </div>

    <h2>Módulos del sistema</h2>
    <ul class="modulos">
      <li>Usuarios y roles (docente, estudiante)</li>
      <li>Cursos y material académico</li>
      <li>Carga y procesamiento documental en fragmentos</li>
      <li>Consultas al asistente RAG con fuentes fundamentadas</li>
      <li>Historial y valoración de respuestas (útil, parcial, no útil)</li>
      <li>Reporte de información incorrecta o no sustentada</li>
      <li>Banco de preguntas y módulo de evaluaciones</li>
      <li>Estadísticas de uso y dashboard docente</li>
    </ul>
  </section>

  <section class="container" id="nosotros">
    <h2>Quiénes somos</h2>
    <div class="card"> 
      <p>Somos el <strong>Grupo 03</strong> de la asignatura <strong>Aplicaciones Distribuidas I</strong>, VI ciclo, Escuela Profesional de Ingeniería de Sistemas e Informática, Universidad Nacional del Santa.</p>
      <ul class="integrantes">
        <li>Luis Santiago Patricio Miranda &mdash; Coordinador</li>
        <li>Eddy Joseph Giraldo Barreto</li>
        <li>Diego Jeanpiere Hinostroza Melo</li>
        <li>David Jesús Valverde Maldonado</li>
        <li>Victor Rodolfo Tufinio Moreno</li>
      </ul>
      <p style="margin-top: 15px; font-size: 0.9rem; color: #555;">Docente del curso: <strong>Dr. Mirko Manrique Ronceros</strong></p>
    </div>
  </section>

  <section class="container" id="ubicacion">
    <h2>Dónde estamos</h2>
    <p class="intro">Universidad Nacional del Santa, Av. Universitaria s/n, Urb. Bellamar, Nuevo Chimbote, Áncash, Perú.</p>
    <div class="mapa">
      <iframe title="Mapa de la Universidad Nacional del Santa"
        src="https://www.google.com/maps?q=Universidad+Nacional+del+Santa+Nuevo+Chimbote&output=embed"
        loading="lazy" allowfullscreen referrerpolicy="no-referrer-when-downgrade"></iframe>
    </div>
    <button class="btn btn-sec" id="btnUbicacion" type="button">Ver mi ubicación actual</button>
    <p id="ubicacionMsg" class="estado" aria-live="polite" hidden></p>
  </section>
</main>
<?php require_once __DIR__ . '/includes/footer.php'; ?>
