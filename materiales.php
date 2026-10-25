<?php
require_once __DIR__ . '/backend/auth.php';
require_once __DIR__ . '/backend/datos.php';

protegerPagina();

$usuario = obtenerUsuarioActual();
$cursos = obtenerCursos();
$cursoFiltroId = isset($_GET['curso_id']) ? (int)$_GET['curso_id'] : 0;

$mensajeExito = '';
$error = '';

// Procesar carga de documento por parte del docente
if (isset($_SERVER['REQUEST_METHOD']) && $_SERVER['REQUEST_METHOD'] === 'POST' && esDocente()) {
    $curso_id = isset($_POST['curso_id']) ? (int)$_POST['curso_id'] : 0;
    $titulo   = isset($_POST['titulo']) ? trim($_POST['titulo']) : '';
    $tipo     = isset($_POST['tipo']) ? trim($_POST['tipo']) : 'Separata';
    $unidad   = isset($_POST['unidad']) ? trim($_POST['unidad']) : 'Unidad 1';
    $resumen  = isset($_POST['resumen']) ? trim($_POST['resumen']) : '';
    
    // Simulación de nombre de archivo subido
    $archivoNombre = !empty($_FILES['archivo']['name']) ? basename($_FILES['archivo']['name']) : (isset($_POST['archivo_simulado']) ? trim($_POST['archivo_simulado']) : 'documento_academico.pdf');

    if ($curso_id <= 0 || empty($titulo)) {
        $error = 'Por favor selecciona un curso y escribe el título del material.';
    } elseif ($errorArchivo !== '') {
        $error = $errorArchivo;
    } else {
        guardarMaterial($curso_id, $titulo, $unidad, $tipo, $archivoNombre, $resumen);
        $mensajeExito = "Documento '{$titulo}' cargado correctamente y fragmentado para la base de conocimiento.";
    }
}

$materiales = obtenerMateriales($cursoFiltroId > 0 ? $cursoFiltroId : null);

$tituloPagina = "Material Académico";
require_once __DIR__ . '/includes/header.php';
?>
<main class="container">
  <div class="acciones-top">
    <div>
      <h2>Material Académico Autorizado</h2>
      <p class="intro">Documentos oficiales (separatas, diapositivas y guías) procesados para alimentar la base de conocimiento del asistente RAG.</p>
    </div>
    <div>
      <form method="GET" action="materiales.php" style="display: flex; gap: 8px; align-items: center;">
        <label for="filtroCurso" style="font-weight: 600; font-size: 0.9rem;">Filtrar curso:</label>
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

  <?php if (!empty($mensajeExito)): ?>
    <div class="alerta-exito"><?php echo htmlspecialchars($mensajeExito); ?></div>
  <?php endif; ?>

  <?php if (!empty($error)): ?>
    <p class="alerta"><?php echo htmlspecialchars($error); ?></p>
  <?php endif; ?>

  <?php if (esDocente()): ?>
    <!-- Formulario para cargar y procesar documentos (Solo Docente) -->
    <section class="card" style="margin-bottom: 30px;">
      <h3>+ Cargar y Procesar Nuevo Material de Estudio</h3>
      <p style="font-size: 0.9rem; color: #555; margin-bottom: 15px;">Al cargar el documento, el sistema extrae automáticamente sus fragmentos y páginas para permitir la búsqueda semántica y recuperación de fuentes.</p>
      
      <form action="materiales.php" method="POST" class="form" enctype="multipart/form-data">
        <div class="grid" style="margin-bottom: 0;">
          <div class="form-group" style="flex: 1 1 250px;">
            <label for="curso_id">Curso de destino</label>
            <select name="curso_id" id="curso_id" class="input-control" required>
              <option value="">-- Seleccionar curso --</option>
              <?php foreach ($cursos as $c): ?>
                <option value="<?php echo $c['id']; ?>" <?php echo ($cursoFiltroId === $c['id']) ? 'selected' : ''; ?>>
                  <?php echo htmlspecialchars($c['nombre']); ?> (Ciclo <?php echo $c['ciclo']; ?>)
                </option>
              <?php endforeach; ?>
            </select>
          </div>

          <div class="form-group" style="flex: 1 1 250px;">
            <label for="tipo">Tipo de material</label>
            <select name="tipo" id="tipo" class="input-control" required>
              <option value="Separata">Separata de teoría</option>
              <option value="Diapositivas">Diapositivas de clase</option>
              <option value="Guía">Guía de laboratorio / taller</option>
              <option value="Artículo">Artículo científico</option>
              <option value="Reglamento">Reglamento o sílabo</option>
            </select>
          </div>

          <div class="form-group" style="flex: 1 1 200px;">
            <label for="unidad">Unidad académica</label>
            <select name="unidad" id="unidad" class="input-control" required>
              <option value="Unidad 1">Unidad 1</option>
              <option value="Unidad 2">Unidad 2</option>
              <option value="Unidad 3">Unidad 3</option>
              <option value="Unidad 4">Unidad 4</option>
            </select>
          </div>
        </div>

        <div class="form-group">
          <label for="titulo">Título descriptivo del documento</label>
          <input class="input-control" type="text" id="titulo" name="titulo" required placeholder="Ej. Separata Unidad 3: Servicios Web y Arquitectura REST">
        </div>

        <div class="form-group">
          <label for="archivo">Archivo del documento (PDF o DOCX, máximo 10 MB)</label>
          <input class="input-control" type="file" id="archivo" name="archivo" accept=".pdf,.docx" required>
        </div>

        <div class="form-group">
          <label for="resumen">Contenido textual del documento (para indexación y fragmentación RAG)</label>
          <textarea class="input-control" id="resumen" name="resumen" rows="3" placeholder="Pega aquí párrafos o texto del documento. Cada párrafo formará un fragmento indexado con su número de página en la base de conocimiento..."></textarea>
          <small style="color: #666;">EduMentor dividirá este texto en fragmentos con páginas para que el asistente pueda citarlo con precisión.</small>
        </div>

        <button type="submit" class="btn">Procesar y agregar a la base de conocimiento</button>
      </form>
    </section>
  <?php endif; ?>

  <!-- Listado de materiales -->
  <h3>Documentos autorizados registrados (<?php echo count($materiales); ?>)</h3>
  <?php if (empty($materiales)): ?>
    <p class="alerta">No hay documentos registrados para este filtro. <?php echo esDocente() ? 'Utiliza el formulario superior para cargar el primer material.' : 'Tu docente aún no ha cargado separatas.'; ?></p>
  <?php else: ?>
    <div class="tabla-responsive">
      <table class="tabla">
        <thead>
          <tr>
            <th>Documento / Título</th>
            <th>Curso</th>
            <th>Tipo</th>
            <th>Unidad</th>
            <th>Archivo</th>
            <th>Base RAG</th>
            <th>Acción</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($materiales as $m): 
            $cursoObj = obtenerCursoPorId($m['curso_id']);
            $frags = obtenerFragmentosPorMaterial($m['id']);
          ?>
            <tr>
              <td><strong><?php echo htmlspecialchars($m['titulo']); ?></strong></td>
              <td><?php echo $cursoObj ? htmlspecialchars($cursoObj['nombre']) : 'General'; ?></td>
              <td><span class="badge badge-azul"><?php echo htmlspecialchars($m['tipo']); ?></span></td>
              <td><span class="badge badge-verde"><?php echo htmlspecialchars($m['unidad']); ?></span></td>
              <td>
                <?php if (file_exists(__DIR__ . '/uploads/' . $m['archivo'])): ?>
                  <a href="uploads/<?php echo rawurlencode($m['archivo']); ?>" target="_blank">Ver archivo</a>
                <?php else: ?>
                  <code><?php echo htmlspecialchars($m['archivo']); ?></code> <small>(sin archivo)</small>
                <?php endif; ?>
              </td>
              <td><span class="badge badge-verde"><?php echo count($frags); ?> fragmentos</span></td>
              <td>
                <button type="button" class="btn btn-sec btn-sm" onclick="verFragmentos(<?php echo $m['id']; ?>, '<?php echo addslashes(htmlspecialchars($m['titulo'])); ?>')">
                  Ver fragmentos
                </button>
                <?php if (esDocente()): ?>
                  <form method="POST" action="materiales.php" style="display:inline"
                        onsubmit="return confirm('¿Eliminar este material y sus fragmentos?');">
                    <input type="hidden" name="accion" value="eliminar">
                    <input type="hidden" name="material_id" value="<?php echo $m['id']; ?>">
                    <button type="submit" class="btn btn-sm" style="background:#c0392b;">Eliminar</button>
                  </form>
                <?php endif; ?>
              </td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  <?php endif; ?>

  <!-- Modal / Contenedor para ver fragmentos procesados -->
  <div id="modalFragmentos" class="card" style="display: none; margin-top: 25px; border-left: 5px solid var(--azul);">
    <div style="display: flex; justify-content: space-between; align-items: center;">
      <h3 id="modalTitulo">Fragmentos procesados</h3>
      <button type="button" class="btn btn-sec btn-sm" onclick="document.getElementById('modalFragmentos').style.display='none'">Cerrar</button>
    </div>
    <p style="font-size: 0.9rem; color: #555;">Estos son los fragmentos de texto indexados que el asistente RAG consulta para responder las preguntas de los estudiantes.</p>
    <div id="modalContenido" style="margin-top: 15px;"></div>
  </div>

  <p style="margin-top: 20px;"><a href="dashboard.php">&laquo; Volver a mi panel</a></p>
</main>

<script>
function verFragmentos(materialId, titulo) {
  const modal = document.getElementById('modalFragmentos');
  const modalTitulo = document.getElementById('modalTitulo');
  const modalContenido = document.getElementById('modalContenido');
  
  modalTitulo.textContent = 'Fragmentos extraídos de: ' + titulo;
  modalContenido.innerHTML = '<p>Cargando fragmentos indexados...</p>';
  modal.style.display = 'block';
  modal.scrollIntoView({ behavior: 'smooth' });

  fetch('backend/api.php?action=fragmentos&material_id=' + materialId)
    .then(res => res.json())
    .then(data => {
      if (data.fragmentos && data.fragmentos.length > 0) {
        let html = '';
        data.fragmentos.forEach((f, idx) => {
          html += `
            <div class="fuente-box" style="margin-bottom: 12px;">
              <strong>Fragmento #${idx + 1} &middot; Página ${f.pagina}</strong>
              <span class="fuente-texto">${f.texto}</span>
            </div>
          `;
        });
        modalContenido.innerHTML = html;
      } else {
        modalContenido.innerHTML = '<p class="nota">Este documento no tiene fragmentos de texto asociados aún.</p>';
      }
    })
    .catch(err => {
      modalContenido.innerHTML = '<p class="alerta">Error al recuperar los fragmentos del documento.</p>';
    });
}
</script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
