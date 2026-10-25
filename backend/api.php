<?php
/**
 * EduMentor RAG - API REST / JSON Endpoint (Backend PHP)
 * Permite la comunicación asíncrona para consultas RAG, valoraciones y visualización de fragmentos.
 */

header('Content-Type: application/json; charset=UTF-8');

require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/datos.php';

$action = isset($_GET['action']) ? $_GET['action'] : (isset($_POST['action']) ? $_POST['action'] : '');

// 0. Autenticación / Inicio de Sesión API
if ($action === 'login') {
    $correo = isset($_POST['correo']) ? trim($_POST['correo']) : '';
    $clave  = isset($_POST['clave']) ? trim($_POST['clave']) : '';

    if (empty($correo) || empty($clave)) {
        // Soporte si vino en php://input
        $input = json_decode(file_get_contents('php://input'), true);
        if (is_array($input)) {
            $correo = isset($input['correo']) ? trim($input['correo']) : '';
            $clave  = isset($input['clave']) ? trim($input['clave']) : '';
        }
    }

    if (empty($correo) || empty($clave)) {
        http_response_code(400);
        echo json_encode(['status' => 'error', 'mensaje' => 'Por favor ingresa tu correo y contraseña.']);
        exit;
    }

    if (iniciarSesion($correo, $clave)) {
        $u = obtenerUsuarioActual();
        // Seguridad: únicamente devolver id, nombre, correo y rol
        echo json_encode([
            'status'  => 'ok',
            'usuario' => [
                'id'     => (int)$u['id'],
                'nombre' => $u['nombre'],
                'correo' => $u['correo'],
                'rol'    => $u['rol']
            ]
        ]);
    } else {
        http_response_code(401);
        echo json_encode(['status' => 'error', 'mensaje' => 'Correo o contraseña incorrectos.']);
    }
    exit;
}

// 1. Consulta al Asistente RAG
if ($action === 'preguntar') {
    if (!estaAutenticado()) {
        echo json_encode(['error' => 'Debes iniciar sesión para consultar al asistente.']);
        exit;
    }

    $curso_id = isset($_POST['curso_id']) ? (int)$_POST['curso_id'] : 0;
    $pregunta = isset($_POST['pregunta']) ? trim($_POST['pregunta']) : '';

    if ($curso_id <= 0 || empty($pregunta)) {
        echo json_encode(['error' => 'Por favor selecciona un curso y escribe tu pregunta.']);
        exit;
    }

    $usuario = obtenerUsuarioActual();
    $estudiante_id = (isset($_POST['estudiante_id']) && (int)$_POST['estudiante_id'] > 0)
        ? (int)$_POST['estudiante_id']
        : (int)$usuario['id'];

    $resultado = procesarConsultaRAG($curso_id, $pregunta);

    // Registrar la consulta con sus fuentes en MySQL
    $consulta = guardarConsulta(
        $estudiante_id,
        $usuario['nombre'],
        $curso_id,
        $pregunta,
        $resultado['respuesta'],
        $resultado['fuentes']
    );

    echo json_encode([
        'status'      => 'ok',
        'consulta_id' => $consulta['id'],
        'encontrado'  => $resultado['encontrado'],
        'respuesta'   => $resultado['respuesta'],
        'fuentes'     => $resultado['fuentes']
    ]);
    exit;
}

// 2. Registrar Valoración o Reporte de Calidad
if ($action === 'valorar') {
    if (!estaAutenticado()) {
        echo json_encode(['error' => 'No autorizado.']);
        exit;
    }

    $consulta_id = isset($_POST['consulta_id']) ? (int)$_POST['consulta_id'] : 0;
    $utilidad = isset($_POST['utilidad']) ? trim($_POST['utilidad']) : null;
    $reporte = isset($_POST['reporte']) ? trim($_POST['reporte']) : null;

    if ($consulta_id <= 0) {
        echo json_encode(['error' => 'ID de consulta inválido.']);
        exit;
    }

    $exito = guardarValoracion($consulta_id, $utilidad, $reporte);

    echo json_encode([
        'status'  => $exito ? 'ok' : 'error',
        'mensaje' => $exito ? 'Valoración registrada con éxito.' : 'No se pudo actualizar la valoración.'
    ]);
    exit;
}

// 3. Obtener Fragmentos procesados de un material
if ($action === 'fragmentos') {
    $material_id = isset($_GET['material_id']) ? (int)$_GET['material_id'] : 0;
    $fragmentos = obtenerFragmentosPorMaterial($material_id);
    echo json_encode([
        'status'     => 'ok',
        'fragmentos' => $fragmentos
    ]);
    exit;
}

echo json_encode(['error' => 'Acción no reconocida.']);
