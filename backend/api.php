<?php
/**
 * EduMentor RAG - API REST / JSON Endpoint (Backend PHP)
 * Permite la comunicación asíncrona para consultas RAG, valoraciones y visualización de fragmentos.
 */

header('Content-Type: application/json; charset=UTF-8');

require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/datos.php';

$action = isset($_GET['action']) ? $_GET['action'] : (isset($_POST['action']) ? $_POST['action'] : '');

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
    $resultado = procesarConsultaRAG($curso_id, $pregunta);

    // Registrar la consulta con sus fuentes
    $consulta = guardarConsulta(
        $usuario['id'],
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
