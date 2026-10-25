<?php
/**
 * EduMentor RAG - Módulo de Autenticación y Control de Sesiones
 * Las contraseñas se guardan con hash (bcrypt) y se validan con password_verify().
 */

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/datos.php';

function estaAutenticado() {
    return isset($_SESSION['usuario']) && !empty($_SESSION['usuario']);
}

function obtenerUsuarioActual() {
    return estaAutenticado() ? $_SESSION['usuario'] : null;
}

function esDocente() {
    $u = obtenerUsuarioActual();
    return $u && $u['rol'] === 'docente';
}

function esEstudiante() {
    $u = obtenerUsuarioActual();
    return $u && $u['rol'] === 'estudiante';
}

// Inicia sesión consultando la tabla `usuarios` en MySQL
function iniciarSesion($correo, $clave) {
    $correo = trim(strtolower($correo));

    $u = obtenerUsuarioPorCorreo($correo);
    if ($u && password_verify($clave, $u['clave'])) {
        session_regenerate_id(true); // evita fijación de sesión
        $_SESSION['usuario'] = [
            'id'     => (int)$u['id'],
            'nombre' => $u['nombre'],
            'correo' => $u['correo'],
            'rol'    => $u['rol']
        ];
        return true;
    }
    return false;
}

function cerrarSesion() {
    $_SESSION = [];
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