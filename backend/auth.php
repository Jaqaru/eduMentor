<?php
/**
 * EduMentor RAG - Módulo de Autenticación y Control de Sesiones
 * Arquitectura Cliente-Servidor (Backend PHP)
 */

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/datos.php';

// Verifica si existe un usuario autenticado
function estaAutenticado() {
    return isset($_SESSION['usuario']) && !empty($_SESSION['usuario']);
}

// Obtiene los datos del usuario en sesión actual
function obtenerUsuarioActual() {
    return estaAutenticado() ? $_SESSION['usuario'] : null;
}

// Verifica si el usuario actual es Docente
function esDocente() {
    $u = obtenerUsuarioActual();
    return $u && $u['rol'] === 'docente';
}

// Verifica si el usuario actual es Estudiante
function esEstudiante() {
    $u = obtenerUsuarioActual();
    return $u && $u['rol'] === 'estudiante';
}

// Inicia sesión verificando credenciales
function iniciarSesion($correo, $clave) {
    $correo = trim(strtolower($correo));
    $clave = trim($clave);

    $usuarios = obtenerUsuarios();
    foreach ($usuarios as $u) {
        if (strtolower($u['correo']) === $correo && $u['clave'] === $clave) {
            $_SESSION['usuario'] = [
                'id'     => $u['id'],
                'nombre' => $u['nombre'],
                'correo' => $u['correo'],
                'rol'    => $u['rol']
            ];
            return true;
        }
    }
    return false;
}

// Cierra la sesión activa
function cerrarSesion() {
    unset($_SESSION['usuario']);
    if (session_status() === PHP_SESSION_ACTIVE) {
        session_destroy();
    }
}

// Protege una página según el rol requerido
function protegerPagina($rolRequerido = null) {
    if (!estaAutenticado()) {
        header('Location: login.php?error=requiere_login');
        exit;
    }

    if ($rolRequerido !== null) {
        $u = obtenerUsuarioActual();
        if ($u['rol'] !== $rolRequerido) {
            header('Location: dashboard.php?error=acceso_denegado');
            exit;
        }
    }
}
