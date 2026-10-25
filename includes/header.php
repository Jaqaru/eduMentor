<?php
/**
 * EduMentor RAG - Cabecera común y navegación
 * Control de acceso y visibilidad de opciones por rol
 */
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once __DIR__ . '/../backend/auth.php';

$usuarioActual = obtenerUsuarioActual();
$paginaActual = basename($_SERVER['PHP_SELF']);
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?php echo isset($tituloPagina) ? htmlspecialchars($tituloPagina) . ' | EduMentor RAG' : 'EduMentor RAG'; ?></title>
  <link rel="stylesheet" href="css/styles.css">
</head>
<body>
<header class="header">
  <a class="logo" href="index.php">EduMentor <span>RAG</span></a>
  <button class="menu-btn" id="menuBtn" aria-label="Abrir menú">&#9776;</button>
  
  <nav class="nav" id="menu">
    <a href="index.php" class="<?php echo ($paginaActual === 'index.php') ? 'activo' : ''; ?>">Inicio</a>
    
    <?php if (!$usuarioActual): ?>
      <!-- Menú para visitantes / público en general -->
      <!-- 'Registrar Curso' no se muestra aquí: solo corresponde al docente -->
      <a href="index.php#proyecto">El proyecto</a>
      <a href="index.php#nosotros">Quiénes somos</a>
      <a href="index.php#ubicacion">Ubicación</a>
      <a href="login.php" class="btn-nav <?php echo ($paginaActual === 'login.php') ? 'activo' : ''; ?>">Ingresar</a>

    <?php elseif ($usuarioActual['rol'] === 'docente'): ?>
      <!-- Menú exclusivo para el DOCENTE -->
      <a href="dashboard.php" class="<?php echo ($paginaActual === 'dashboard.php') ? 'activo' : ''; ?>">Mi panel</a>
      <a href="cursos.php" class="<?php echo (in_array($paginaActual, ['cursos.php', 'registrar_curso.php'])) ? 'activo' : ''; ?>">Mis cursos</a>
      <a href="materiales.php" class="<?php echo ($paginaActual === 'materiales.php') ? 'activo' : ''; ?>">Material académico</a>
      <a href="historial.php" class="<?php echo ($paginaActual === 'historial.php') ? 'activo' : ''; ?>">Consultas</a>
      <a href="evaluaciones.php" class="<?php echo ($paginaActual === 'evaluaciones.php') ? 'activo' : ''; ?>">Banco preguntas</a>
      <a href="estadisticas.php" class="<?php echo ($paginaActual === 'estadisticas.php') ? 'activo' : ''; ?>">Estadísticas</a>
      <a href="logout.php" class="btn-nav">Salir (Docente)</a>

    <?php elseif ($usuarioActual['rol'] === 'estudiante'): ?>
      <!-- Menú exclusivo para el ESTUDIANTE -->
      <a href="dashboard.php" class="<?php echo ($paginaActual === 'dashboard.php') ? 'activo' : ''; ?>">Mi panel</a>
      <a href="asistente.php" class="<?php echo ($paginaActual === 'asistente.php') ? 'activo' : ''; ?>">Asistente RAG</a>
      <a href="materiales.php" class="<?php echo ($paginaActual === 'materiales.php') ? 'activo' : ''; ?>">Materiales</a>
      <a href="historial.php" class="<?php echo ($paginaActual === 'historial.php') ? 'activo' : ''; ?>">Mi historial</a>
      <a href="evaluaciones.php" class="<?php echo ($paginaActual === 'evaluaciones.php') ? 'activo' : ''; ?>">Evaluaciones</a>
      <a href="logout.php" class="btn-nav">Salir (Estudiante)</a>
    <?php endif; ?>
  </nav>
</header>
