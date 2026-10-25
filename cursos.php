<?php
require_once __DIR__ . '/backend/auth.php';
require_once __DIR__ . '/backend/datos.php';

protegerPagina();

$usuario = obtenerUsuarioActual();
$cursos = obtenerCursos();
$materiales = obtenerMateriales();

$tituloPagina = "Cursos";
require_once __DIR__ . '/includes/header.php';
?>
<main class="container">
  <div class="acciones-top">
    <div>
      <h2>Asignaturas Académicas</h2>
      <p class="intro">Cursos activos configurados en la plataforma EduMentor RAG.</p>
    </div>
    <?php if (esDocente()): ?>
      <div>
        <a href="registrar_curso.php" class="btn">+ Registrar nuevo curso</a>
      </div>
    <?php endif; ?>
  </div>

  <?php if (isset($_GET['mensaje'])): ?>
    <div class="alerta-exito">
      <?php echo htmlspecialchars($_GET['mensaje']); ?>
    </div>
  <?php endif; ?>

  <div class="grid">
    <?php foreach ($cursos as $c): 
      $numMateriales = count(obtenerMateriales($c['id']));
      $numPreguntas = count(obtenerPreguntasBanco($c['id']));
    ?>
      <article class="card">
        <div style="display: flex; justify-content: space-between; align-items: flex-start;">
          <span class="badge badge-azul">Código: <?php echo htmlspecialchars($c['codigo']); ?></span>
          <span class="badge badge-verde">Ciclo <?php echo htmlspecialchars($c['ciclo']); ?></span>
        </div>
        
        <h3 style="margin-top: 10px;"><?php echo htmlspecialchars($c['nombre']); ?></h3>
        <p style="font-size: 0.92rem; color: #555; margin: 8px 0;"><?php echo htmlspecialchars($c['descripcion']); ?></p>
        
        <div style="background: #f8fafc; border-radius: 6px; padding: 10px; margin: 12px 0; font-size: 0.88rem;">
          <p><strong>Documentos en base de conocimiento:</strong> <?php echo $numMateriales; ?></p>
          <p><strong>Preguntas de autoevaluación:</strong> <?php echo $numPreguntas; ?></p>
        </div>

        <div style="display: flex; gap: 6px; flex-wrap: wrap;">
          <?php if (esDocente()): ?>
            <a href="materiales.php?curso_id=<?php echo $c['id']; ?>" class="btn btn-sm">Gestionar material</a>
            <a href="evaluaciones.php?curso_id=<?php echo $c['id']; ?>" class="btn btn-sec btn-sm">Banco de preguntas</a>
          <?php else: ?>
            <a href="asistente.php?curso_id=<?php echo $c['id']; ?>" class="btn btn-sm">Consultar asistente</a>
            <a href="materiales.php?curso_id=<?php echo $c['id']; ?>" class="btn btn-sec btn-sm">Ver material</a>
            <a href="evaluaciones.php?curso_id=<?php echo $c['id']; ?>" class="btn btn-sec btn-sm">Autoevaluación</a>
          <?php endif; ?>
        </div>
      </article>
    <?php endforeach; ?>
  </div>

  <p style="margin-top: 20px;"><a href="dashboard.php">&laquo; Volver a mi panel</a></p>
</main>
<?php require_once __DIR__ . '/includes/footer.php'; ?>
