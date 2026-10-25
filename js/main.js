/**
 * EduMentor RAG - Scripts de Interfaz y Comportamiento Dinámico
 * Aplicaciones Distribuidas I - Grupo 03
 */

const $ = id => document.getElementById(id);
const sesion = JSON.parse(sessionStorage.getItem('sesion') || 'null');

// 1. Menú desplegable para móviles
if ($('menuBtn') && $('menu')) {
  $('menuBtn').addEventListener('click', () => {
    $('menu').classList.toggle('abierto');
  });
}

// 2. Destacar enlace activo en navegación
const rutaActual = location.pathname.split('/').pop() || 'index.php';
document.querySelectorAll('#menu a').forEach(a => {
  const href = a.getAttribute('href');
  if (href === rutaActual || (href && href.startsWith(rutaActual))) {
    a.classList.add('activo');
  }
});

// 3. Sincronización de sesión frontend (para navegación estática)
if (sesion) {
  if ($('navLogin')) $('navLogin').hidden = true;
  if ($('navPanel')) $('navPanel').hidden = false;
  if ($('navSalir')) {
    $('navSalir').hidden = false;
    $('navSalir').addEventListener('click', e => {
      e.preventDefault();
      sessionStorage.removeItem('sesion');
      location.href = 'index.php';
    });
  }
}

// 4. Mostrar / ocultar contraseña
if ($('verClave') && $('clave')) {
  $('verClave').addEventListener('change', e => {
    $('clave').type = e.target.checked ? 'text' : 'password';
  });
}

// 5. Contador de caracteres en formularios de descripción
if ($('descripcion') && $('contador')) {
  $('descripcion').addEventListener('input', e => {
    $('contador').textContent = e.target.value.length + ' / 300';
  });
}

// 6. Geolocalización HTML5 en la sección de ubicación
if ($('btnUbicacion') && $('ubicacionMsg')) {
  $('btnUbicacion').addEventListener('click', () => {
    const msg = $('ubicacionMsg');
    msg.hidden = false;
    if (!navigator.geolocation) {
      msg.textContent = 'Tu navegador no admite geolocalización.';
      return;
    }
    msg.textContent = 'Consultando satélite y red para obtener ubicación...';
    navigator.geolocation.getCurrentPosition(
      p => {
        msg.textContent = 'Tu ubicación actual: Latitud ' + p.coords.latitude.toFixed(5) + ', Longitud ' + p.coords.longitude.toFixed(5) + ' (Cerca del campus universitario).';
      },
      err => {
        msg.textContent = 'No se pudo obtener la ubicación exacta. Por favor verifica los permisos del navegador.';
      }
    );
  });
}

// 7. Panel en HTML (fallback para pruebas directas en navegador)
if ($('panelDocente') && $('panelEstudiante') && sesion) {
  if ($('dashNombre')) $('dashNombre').textContent = sesion.nombre;
  if ($('dashRol')) $('dashRol').textContent = sesion.rol;
  if (sesion.rol === 'docente') {
    $('panelDocente').hidden = false;
  } else {
    $('panelEstudiante').hidden = false;
  }
}
