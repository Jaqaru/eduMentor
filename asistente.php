<?php
require_once __DIR__ . '/backend/auth.php';
require_once __DIR__ . '/backend/datos.php';

protegerPagina();

$usuario = obtenerUsuarioActual();
$cursos = obtenerCursos();
$cursoSeleccionadoId = isset($_GET['curso_id']) ? (int)$_GET['curso_id'] : (isset($cursos[0]['id']) ? $cursos[0]['id'] : 1);

$tituloPagina = "Asistente RAG";
require_once __DIR__ . '/includes/header.php';
?>
<main class="container">
  <div class="acciones-top">
    <div>
      <p><a href="dashboard.php">&laquo; Volver a mi panel</a></p>
      <h2>Tutoría Inteligente EduMentor RAG</h2>
    </div>
    <div style="display: flex; gap: 8px; align-items: center;">
      <label for="cursoSel" style="font-weight: 600;">Asignatura:</label>
      <select id="cursoSel" class="input-control" style="width: auto; min-width: 240px;">
        <?php foreach ($cursos as $c): ?>
          <option value="<?php echo $c['id']; ?>" <?php echo ($cursoSeleccionadoId === $c['id']) ? 'selected' : ''; ?>>
            <?php echo htmlspecialchars($c['nombre']); ?> (Ciclo <?php echo $c['ciclo']; ?>)
          </option>
        <?php endforeach; ?>
      </select>
    </div>
  </div>

  <section class="chat">
    <p class="aviso">
      <strong>Garantía de contenido autorizado:</strong> Las respuestas se fundamentan exclusivamente en los documentos cargados por el docente para el curso seleccionado. Cada respuesta muestra su fuente y fragmento exacto.
    </p>

    <!-- Contenedor interactivo de mensajes -->
    <div class="chat-msgs" id="chatMsgs" aria-live="polite">
      <div class="burbuja ia">
        <p>Hola <strong><?php echo htmlspecialchars($usuario['nombre']); ?></strong>, soy tu asistente de tutoría académica. Selecciona tu curso y hazme cualquier pregunta sobre las separatas, diapositivas y guías autorizadas.</p>
      </div>
    </div>

    <!-- Sugerencias rápidas (chips) -->
    <div class="chips" id="chips">
      <button type="button" class="chip">¿Cuál es la diferencia entre arquitectura cliente-servidor y distribuida?</button>
      <button type="button" class="chip">¿Para qué sirve el patrón MVC?</button>
      <button type="button" class="chip">¿Cuáles son las ventajas de una API REST?</button>
      <button type="button" class="chip">¿Qué es la normalización de bases de datos?</button>
    </div>

    <!-- Formulario de pregunta -->
    <form id="chatForm" class="chat-form">
      <label for="pregunta" class="sr">Tu pregunta</label>
      <input class="input-control" type="text" id="pregunta" required maxlength="300" placeholder="Escribe tu consulta académica en lenguaje natural...">
      <button type="submit" class="btn" id="btnEnviar">Preguntar</button>
    </form>
  </section>

  <p style="margin-top: 15px; font-size: 0.9rem; color: #555;">
    ¿Deseas revisar consultas anteriores? Consulta tu <a href="historial.php">Historial de Consultas</a>.
  </p>
</main>

<script>
document.addEventListener('DOMContentLoaded', () => {
  const msgs = document.getElementById('chatMsgs');
  const chatForm = document.getElementById('chatForm');
  const preguntaInput = document.getElementById('pregunta');
  const cursoSel = document.getElementById('cursoSel');
  const chips = document.getElementById('chips');

  // Insertar chip al hacer clic
  chips.addEventListener('click', (e) => {
    if (e.target.classList.contains('chip')) {
      preguntaInput.value = e.target.textContent;
      preguntaInput.focus();
    }
  });

  // Enviar pregunta al backend RAG
  chatForm.addEventListener('submit', (e) => {
    e.preventDefault();
    const texto = preguntaInput.value.trim();
    const cursoId = cursoSel.value;
    if (!texto) return;

    // Mostrar burbuja del estudiante
    agregarBurbuja(texto, 'est');
    preguntaInput.value = '';

    // Burbuja de espera
    const esperando = agregarBurbuja('Recuperando fragmentos autorizados y generando respuesta...', 'ia');

    const formData = new FormData();
    formData.append('action', 'preguntar');
    formData.append('curso_id', cursoId);
    formData.append('estudiante_id', <?php echo (int)$usuario['id']; ?>);
    formData.append('pregunta', texto);

    fetch('backend/api.php', {
      method: 'POST',
      body: formData
    })
    .then(res => res.json())
    .then(data => {
      esperando.remove();
      if (data.error) {
        agregarBurbuja('Error: ' + data.error, 'ia');
        return;
      }

      // Renderizar respuesta fundamentada con fuentes y panel de valoración
      mostrarRespuestaRAG(data);
    })
    .catch(err => {
      esperando.remove();
      agregarBurbuja('Ocurrió un error al conectar con el servidor RAG.', 'ia');
    });
  });

  function agregarBurbuja(texto, clase) {
    const p = document.createElement('div');
    p.className = 'burbuja ' + clase;
    p.innerHTML = `<p>${texto}</p>`;
    msgs.appendChild(p);
    msgs.scrollTop = msgs.scrollHeight;
    return p;
  }

  function mostrarRespuestaRAG(data) {
    const contenedor = document.createElement('div');
    contenedor.className = 'burbuja ia';

    let html = `<p>${data.respuesta}</p>`;

    // Fuentes recuperadas
    if (data.fuentes && data.fuentes.length > 0) {
      data.fuentes.forEach(f => {
        html += `
          <div class="fuente-box">
            <strong>Fuente oficial:</strong> ${f.documento} &middot; <span>${f.unidad}</span> (Pág. ${f.pagina})
            <span class="fuente-texto">"${f.texto}"</span>
          </div>
        `;
      });
    }

    // Panel de Valoración y Reporte de Alucinaciones
    html += `
      <div class="valorar" id="valBox_${data.consulta_id}">
        <span style="font-size:0.8rem; font-weight:600; align-self:center;">¿Fue útil la respuesta?</span>
        <button type="button" onclick="registrarValoracion(${data.consulta_id}, 'util')">👍 Útil</button>
        <button type="button" onclick="registrarValoracion(${data.consulta_id}, 'parcial')">😐 Parcialmente útil</button>
        <button type="button" onclick="registrarValoracion(${data.consulta_id}, 'no_util')">👎 No útil</button>
        
        <div class="reportar-box">
          <label>Reportar problema: </label>
          <select onchange="reportarProblema(${data.consulta_id}, this.value)">
            <option value="">-- Seleccionar observación --</option>
            <option value="incorrecta">Información incorrecta</option>
            <option value="incompleta">Información incompleta</option>
            <option value="fuente_no_relacionada">Fuente no relacionada</option>
            <option value="no_sustentada">Respuesta no sustentada</option>
          </select>
        </div>
      </div>
      <div id="valMsg_${data.consulta_id}" class="estado" style="display:none; padding:6px 10px; margin-top:8px; font-size:0.85rem;"></div>
    `;

    contenedor.innerHTML = html;
    msgs.appendChild(contenedor);
    msgs.scrollTop = msgs.scrollHeight;
  }
});

// Función para enviar la valoración de utilidad
function registrarValoracion(consultaId, tipo) {
  const fd = new FormData();
  fd.append('action', 'valorar');
  fd.append('consulta_id', consultaId);
  fd.append('utilidad', tipo);

  fetch('backend/api.php', { method: 'POST', body: fd })
    .then(res => res.json())
    .then(data => {
      const msgBox = document.getElementById('valMsg_' + consultaId);
      const valBox = document.getElementById('valBox_' + consultaId);
      if (msgBox) {
        const etiquetas = { 'util': 'Útil', 'parcial': 'Parcialmente útil', 'no_util': 'No útil' };
        msgBox.textContent = '✓ Valoración registrada: ' + (etiquetas[tipo] || tipo);
        msgBox.style.display = 'block';
      }
    });
}

// Función para reportar calidad / alucinación
function reportarProblema(consultaId, problema) {
  if (!problema) return;
  const fd = new FormData();
  fd.append('action', 'valorar');
  fd.append('consulta_id', consultaId);
  fd.append('reporte', problema);

  fetch('backend/api.php', { method: 'POST', body: fd })
    .then(res => res.json())
    .then(data => {
      const msgBox = document.getElementById('valMsg_' + consultaId);
      if (msgBox) {
        msgBox.textContent = '✓ Reporte registrado: ' + problema.replace(/_/g, ' ') + '. Gracias por ayudar a mejorar la base documental.';
        msgBox.style.display = 'block';
      }
    });
}
</script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
