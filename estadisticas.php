<?php
require_once __DIR__ . '/backend/auth.php';
require_once __DIR__ . '/backend/datos.php';

// Solo el docente tiene acceso a las estadísticas generales
protegerPagina('docente');

$stats = obtenerEstadisticas();
$cursos = obtenerCursos();

$tituloPagina = "Estadísticas del Asistente";
require_once __DIR__ . '/includes/header.php';
?>
<main class="container">
  <div class="acciones-top">
    <div>
      <h2>Métricas y Estadísticas del Asistente RAG</h2>
      <p class="intro">Análisis del comportamiento del sistema, satisfacción estudiantil y control de alucinaciones documentales.</p>
    </div>
    <div>
      <span class="badge badge-azul">Periodo 2026-I</span>
    </div>
  </div>

  <!-- Métricas principales -->
  <div class="stats-grid">
    <div class="stat-card">
      <div class="stat-num"><?php echo $stats['total_consultas']; ?></div>
      <div class="stat-label">Total de consultas realizadas</div>
    </div>

    <div class="stat-card azul">
      <div class="stat-num"><?php echo $stats['porcentaje_util']; ?>%</div>
      <div class="stat-label">Índice de utilidad percibida</div>
    </div>

    <div class="stat-card">
      <div class="stat-num"><?php echo $stats['total_fragmentos']; ?></div>
      <div class="stat-label">Fragmentos indexados en RAG</div>
    </div>

    <div class="stat-card amarillo">
      <div class="stat-num"><?php echo array_sum($stats['reportes']); ?></div>
      <div class="stat-label">Reportes de observación</div>
    </div>
  </div>

  <div class="grid">
    <!-- 1. Distribución de Utilidad -->
    <article class="card">
      <h3>Satisfacción de los Estudiantes</h3>
      <p style="font-size: 0.9rem; color: #555; margin-bottom: 15px;">Evaluaciones emitidas tras recibir una respuesta fundamentada con sus fuentes:</p>
      
      <div style="display: flex; flex-direction: column; gap: 10px;">
        <div>
          <div style="display: flex; justify-content: space-between; font-weight: 600; font-size: 0.9rem;">
            <span>👍 Útil: <?php echo $stats['utiles']; ?></span>
            <span><?php echo ($stats['total_consultas'] > 0) ? round(($stats['utiles'] / $stats['total_consultas']) * 100) : 0; ?>%</span>
          </div>
          <div style="background: #eef2f6; height: 10px; border-radius: 5px; overflow: hidden; margin-top: 4px;">
            <div style="background: #008080; height: 100%; width: <?php echo ($stats['total_consultas'] > 0) ? ($stats['utiles'] / $stats['total_consultas']) * 100 : 0; ?>%;"></div>
          </div>
        </div>

        <div>
          <div style="display: flex; justify-content: space-between; font-weight: 600; font-size: 0.9rem;">
            <span>😐 Parcialmente útil: <?php echo $stats['parciales']; ?></span>
            <span><?php echo ($stats['total_consultas'] > 0) ? round(($stats['parciales'] / $stats['total_consultas']) * 100) : 0; ?>%</span>
          </div>
          <div style="background: #eef2f6; height: 10px; border-radius: 5px; overflow: hidden; margin-top: 4px;">
            <div style="background: #f39c12; height: 100%; width: <?php echo ($stats['total_consultas'] > 0) ? ($stats['parciales'] / $stats['total_consultas']) * 100 : 0; ?>%;"></div>
          </div>
        </div>

        <div>
          <div style="display: flex; justify-content: space-between; font-weight: 600; font-size: 0.9rem;">
            <span>👎 No útil: <?php echo $stats['no_utiles']; ?></span>
            <span><?php echo ($stats['total_consultas'] > 0) ? round(($stats['no_utiles'] / $stats['total_consultas']) * 100) : 0; ?>%</span>
          </div>
          <div style="background: #eef2f6; height: 10px; border-radius: 5px; overflow: hidden; margin-top: 4px;">
            <div style="background: #e74c3c; height: 100%; width: <?php echo ($stats['total_consultas'] > 0) ? ($stats['no_utiles'] / $stats['total_consultas']) * 100 : 0; ?>%;"></div>
          </div>
        </div>
      </div>
    </article>

    <!-- 2. Control de Calidad y Reportes RAG -->
    <article class="card">
      <h3>Auditoría de Calidad y Alucinaciones</h3>
      <p style="font-size: 0.9rem; color: #555; margin-bottom: 12px;">Métricas para verificar que el modelo no genera respuestas no sustentadas en los materiales:</p>
      
      <div class="tabla-responsive">
        <table class="tabla">
          <thead>
            <tr>
              <th>Tipo de observación</th>
              <th>Incidentes</th>
              <th>Estado</th>
            </tr>
          </thead>
          <tbody>
            <tr>
              <td>Información incorrecta</td>
              <td><strong><?php echo $stats['reportes']['incorrecta']; ?></strong></td>
              <td><span class="badge <?php echo $stats['reportes']['incorrecta'] === 0 ? 'badge-verde' : 'badge-amarillo'; ?>"><?php echo $stats['reportes']['incorrecta'] === 0 ? 'Controlado' : 'Revisar'; ?></span></td>
            </tr>
            <tr>
              <td>Información incompleta</td>
              <td><strong><?php echo $stats['reportes']['incompleta']; ?></strong></td>
              <td><span class="badge <?php echo $stats['reportes']['incompleta'] === 0 ? 'badge-verde' : 'badge-amarillo'; ?>"><?php echo $stats['reportes']['incompleta'] === 0 ? 'Controlado' : 'Revisar'; ?></span></td>
            </tr>
            <tr>
              <td>Fuente no relacionada</td>
              <td><strong><?php echo $stats['reportes']['fuente_no_relacionada']; ?></strong></td>
              <td><span class="badge <?php echo $stats['reportes']['fuente_no_relacionada'] === 0 ? 'badge-verde' : 'badge-amarillo'; ?>"><?php echo $stats['reportes']['fuente_no_relacionada'] === 0 ? 'Controlado' : 'Revisar'; ?></span></td>
            </tr>
            <tr>
              <td>Respuesta no sustentada (alucinación)</td>
              <td><strong><?php echo $stats['reportes']['no_sustentada']; ?></strong></td>
              <td><span class="badge <?php echo $stats['reportes']['no_sustentada'] === 0 ? 'badge-verde' : 'badge-rojo'; ?>"><?php echo $stats['reportes']['no_sustentada'] === 0 ? '0% Alucinación' : 'Atención'; ?></span></td>
            </tr>
          </tbody>
        </table>
      </div>
    </article>
  </div>

  <!-- Desglose por curso -->
  <h3>Actividad por Asignatura</h3>
  <div class="tabla-responsive">
    <table class="tabla">
      <thead>
        <tr>
          <th>Código</th>
          <th>Nombre del curso</th>
          <th>Ciclo</th>
          <th>Materiales</th>
          <th>Fragmentos</th>
          <th>Consultas</th>
          <th>Acción</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($cursos as $c): 
          $mats = obtenerMateriales($c['id']);
          $numFrags = 0;
          foreach ($mats as $m) {
            $numFrags += count(obtenerFragmentosPorMaterial($m['id']));
          }
          $consu = obtenerConsultas(null, $c['id']);
        ?>
          <tr>
            <td><code><?php echo htmlspecialchars($c['codigo']); ?></code></td>
            <td><strong><?php echo htmlspecialchars($c['nombre']); ?></strong></td>
            <td>Ciclo <?php echo htmlspecialchars($c['ciclo']); ?></td>
            <td><span class="badge badge-azul"><?php echo count($mats); ?></span></td>
            <td><span class="badge badge-verde"><?php echo $numFrags; ?></span></td>
            <td><span class="badge badge-amarillo"><?php echo count($consu); ?></span></td>
            <td>
              <a href="materiales.php?curso_id=<?php echo $c['id']; ?>" class="btn btn-sec btn-sm">Ver material</a>
            </td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>

  <p style="margin-top: 25px;"><a href="dashboard.php">&laquo; Volver a mi panel</a></p>
</main>
<?php require_once __DIR__ . '/includes/footer.php'; ?>
