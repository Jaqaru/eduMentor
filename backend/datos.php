<?php
/**
 * EduMentor RAG - Capa de Datos y Lógica de Negocio (Backend PHP + MySQL)
 * Todas las consultas usan PDO con sentencias preparadas (protección contra SQL injection).
 * Mantiene los mismos nombres de funciones que la versión en sesión,
 * por lo que las páginas existentes no necesitan cambios.
 */

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../database/conexion.php';

const SEPARADOR_OPCIONES = '||';

// ========================= USUARIOS =========================
function obtenerUsuarios() {
    return obtenerConexionBD()->query('SELECT id, nombre, correo, rol FROM usuarios ORDER BY id')->fetchAll();
}

function obtenerUsuarioPorId($id) {
    $st = obtenerConexionBD()->prepare('SELECT id, nombre, correo, rol FROM usuarios WHERE id = ?');
    $st->execute([(int)$id]);
    return $st->fetch() ?: null;
}

// Incluye el hash de la clave: usar solo en el login
function obtenerUsuarioPorCorreo($correo) {
    $st = obtenerConexionBD()->prepare('SELECT id, nombre, correo, clave, rol FROM usuarios WHERE correo = ? LIMIT 1');
    $st->execute([trim(strtolower($correo))]);
    return $st->fetch() ?: null;
}

// ========================= CURSOS =========================
function obtenerCursos() {
    return obtenerConexionBD()->query('SELECT * FROM cursos ORDER BY id')->fetchAll();
}

function obtenerCursoPorId($id) {
    $st = obtenerConexionBD()->prepare('SELECT * FROM cursos WHERE id = ?');
    $st->execute([(int)$id]);
    return $st->fetch() ?: null;
}

function guardarCurso($docente_id, $codigo, $nombre, $ciclo, $descripcion) {
    $pdo = obtenerConexionBD();
    $st = $pdo->prepare('INSERT INTO cursos (docente_id, codigo, nombre, ciclo, descripcion) VALUES (?, ?, ?, ?, ?)');
    $st->execute([(int)$docente_id, trim($codigo), trim($nombre), (int)$ciclo, trim($descripcion)]);
    return [
        'id'          => (int)$pdo->lastInsertId(),
        'docente_id'  => (int)$docente_id,
        'codigo'      => trim($codigo),
        'nombre'      => trim($nombre),
        'ciclo'       => (int)$ciclo,
        'descripcion' => trim($descripcion)
    ];
}

// ========================= MATERIALES Y FRAGMENTOS =========================
function obtenerMateriales($curso_id = null) {
    $pdo = obtenerConexionBD();
    if ($curso_id !== null && $curso_id > 0) {
        $st = $pdo->prepare('SELECT * FROM materiales WHERE curso_id = ? ORDER BY id');
        $st->execute([(int)$curso_id]);
        return $st->fetchAll();
    }
    return $pdo->query('SELECT * FROM materiales ORDER BY id')->fetchAll();
}

function obtenerFragmentosPorMaterial($material_id) {
    $st = obtenerConexionBD()->prepare('SELECT * FROM fragmentos WHERE material_id = ? ORDER BY pagina, id');
    $st->execute([(int)$material_id]);
    return $st->fetchAll();
}

function guardarMaterial($curso_id, $titulo, $unidad, $tipo, $archivo, $contenido_resumen = '') {
    $pdo = obtenerConexionBD();
    $pdo->beginTransaction();
    try {
        $st = $pdo->prepare('INSERT INTO materiales (curso_id, titulo, unidad, tipo, archivo) VALUES (?, ?, ?, ?, ?)');
        $st->execute([(int)$curso_id, trim($titulo), trim($unidad), trim($tipo), trim($archivo)]);
        $materialId = (int)$pdo->lastInsertId();

        // Procesamiento documental: un fragmento por párrafo
        $stFrag = $pdo->prepare('INSERT INTO fragmentos (material_id, pagina, texto) VALUES (?, ?, ?)');
        $contenido = trim($contenido_resumen);
        if ($contenido !== '') {
            $pagina = 1;
            foreach (preg_split('/\n\s*\n/', $contenido) as $p) {
                $p = trim($p);
                if ($p !== '') {
                    $stFrag->execute([$materialId, $pagina++, $p]);
                }
            }
        } else {
            $stFrag->execute([$materialId, 1, "Contenido autorizado sobre: {$titulo} para {$unidad}."]);
        }

        $pdo->commit();
    } catch (Exception $e) {
        $pdo->rollBack();
        throw $e;
    }

    return [
        'id'        => $materialId,
        'curso_id'  => (int)$curso_id,
        'titulo'    => trim($titulo),
        'unidad'    => trim($unidad),
        'tipo'      => trim($tipo),
        'archivo'   => trim($archivo),
        'creado_en' => date('Y-m-d H:i:s')
    ];
}

// ========================= CONSULTAS Y VALORACIONES =========================
function obtenerConsultas($estudiante_id = null, $curso_id = null) {
    $pdo = obtenerConexionBD();

    $sql = 'SELECT c.id, c.estudiante_id, u.nombre AS estudiante_nombre, c.curso_id,
                   c.pregunta, c.respuesta, c.creado_en, v.utilidad, v.reporte
            FROM consultas c
            JOIN usuarios u ON u.id = c.estudiante_id
            LEFT JOIN valoraciones v ON v.consulta_id = c.id
            WHERE 1=1';
    $params = [];
    if ($estudiante_id !== null) {
        $sql .= ' AND c.estudiante_id = ?';
        $params[] = (int)$estudiante_id;
    }
    if ($curso_id !== null && $curso_id > 0) {
        $sql .= ' AND c.curso_id = ?';
        $params[] = (int)$curso_id;
    }
    $sql .= ' ORDER BY c.creado_en DESC, c.id DESC';

    $st = $pdo->prepare($sql);
    $st->execute($params);
    $consultas = $st->fetchAll();
    if (!$consultas) {
        return [];
    }

    // Fuentes (fragmentos usados) de todas las consultas en una sola query
    $ids = array_column($consultas, 'id');
    $marcas = implode(',', array_fill(0, count($ids), '?'));
    $stF = $pdo->prepare("SELECT cf.consulta_id, m.titulo AS documento, m.unidad, f.pagina, f.texto
                          FROM consulta_fuentes cf
                          JOIN fragmentos f ON f.id = cf.fragmento_id
                          JOIN materiales m ON m.id = f.material_id
                          WHERE cf.consulta_id IN ($marcas)");
    $stF->execute($ids);
    $fuentesPorConsulta = [];
    foreach ($stF->fetchAll() as $f) {
        $cid = $f['consulta_id'];
        unset($f['consulta_id']);
        $fuentesPorConsulta[$cid][] = $f;
    }

    foreach ($consultas as &$c) {
        $c['fuentes'] = $fuentesPorConsulta[$c['id']] ?? [];
    }
    unset($c);
    return $consultas;
}

function guardarConsulta($estudiante_id, $estudiante_nombre, $curso_id, $pregunta, $respuesta, $fuentes) {
    $pdo = obtenerConexionBD();
    $pdo->beginTransaction();
    try {
        $st = $pdo->prepare('INSERT INTO consultas (estudiante_id, curso_id, pregunta, respuesta) VALUES (?, ?, ?, ?)');
        $st->execute([(int)$estudiante_id, (int)$curso_id, trim($pregunta), $respuesta]);
        $consultaId = (int)$pdo->lastInsertId();

        $stF = $pdo->prepare('INSERT IGNORE INTO consulta_fuentes (consulta_id, fragmento_id) VALUES (?, ?)');
        foreach ($fuentes as $f) {
            if (!empty($f['fragmento_id'])) {
                $stF->execute([$consultaId, (int)$f['fragmento_id']]);
            }
        }
        $pdo->commit();
    } catch (Exception $e) {
        $pdo->rollBack();
        throw $e;
    }

    return [
        'id'                => $consultaId,
        'estudiante_id'     => (int)$estudiante_id,
        'estudiante_nombre' => $estudiante_nombre,
        'curso_id'          => (int)$curso_id,
        'pregunta'          => trim($pregunta),
        'respuesta'         => $respuesta,
        'fuentes'           => $fuentes,
        'creado_en'         => date('Y-m-d H:i:s'),
        'utilidad'          => null,
        'reporte'           => null
    ];
}

// La utilidad y el reporte pueden llegar en llamadas separadas: se actualiza una sola fila por consulta
function guardarValoracion($consulta_id, $utilidad, $reporte = null) {
    $utilidadesOk = ['util', 'parcial', 'no_util'];
    $reportesOk   = ['incorrecta', 'incompleta', 'fuente_no_relacionada', 'no_sustentada'];
    $utilidad = in_array($utilidad, $utilidadesOk, true) ? $utilidad : null;
    $reporte  = in_array($reporte, $reportesOk, true) ? $reporte : null;
    if ($utilidad === null && $reporte === null) {
        return false;
    }

    $pdo = obtenerConexionBD();
    $st = $pdo->prepare('SELECT 1 FROM consultas WHERE id = ?');
    $st->execute([(int)$consulta_id]);
    if (!$st->fetchColumn()) {
        return false;
    }

    $st = $pdo->prepare('INSERT INTO valoraciones (consulta_id, utilidad, reporte) VALUES (?, ?, ?)
                         ON DUPLICATE KEY UPDATE utilidad = COALESCE(VALUES(utilidad), utilidad),
                                                 reporte  = COALESCE(VALUES(reporte), reporte)');
    return $st->execute([(int)$consulta_id, $utilidad, $reporte]);
}

// ========================= MOTOR RAG (línea base: recuperación por palabras clave) =========================
function procesarConsultaRAG($curso_id, $pregunta) {
    $curso = obtenerCursoPorId($curso_id);

    $palabras = preg_split('/[\s,\.¿\?\!]+/u', mb_strtolower($pregunta, 'UTF-8'));
    $stopwords = ['que', 'cual', 'cuales', 'como', 'para', 'sirve', 'una', 'uno', 'los', 'las', 'del', 'por', 'con', 'entre', 'sobre'];
    $keywords = array_filter($palabras, function($w) use ($stopwords) {
        return mb_strlen($w) >= 3 && !in_array($w, $stopwords);
    });

    // Solo fragmentos de materiales del curso consultado (contenido autorizado)
    $st = obtenerConexionBD()->prepare('SELECT f.id AS fragmento_id, f.pagina, f.texto, m.titulo, m.unidad
                                        FROM fragmentos f
                                        JOIN materiales m ON m.id = f.material_id
                                        WHERE m.curso_id = ?');
    $st->execute([(int)$curso_id]);

    $candidatos = [];
    foreach ($st->fetchAll() as $frag) {
        $texto = mb_strtolower($frag['texto'], 'UTF-8');
        $coincidencias = 0;
        foreach ($keywords as $kw) {
            if (mb_strpos($texto, $kw) !== false) {
                $coincidencias++;
            }
        }
        if ($coincidencias > 0) {
            $candidatos[] = [
                'score'        => $coincidencias,
                'fragmento_id' => (int)$frag['fragmento_id'],
                'documento'    => $frag['titulo'],
                'unidad'       => $frag['unidad'],
                'pagina'       => $frag['pagina'],
                'texto'        => $frag['texto']
            ];
        }
    }

    if (!empty($candidatos)) {
        usort($candidatos, function($a, $b) {
            return $b['score'] <=> $a['score'];
        });
        $top = $candidatos[0];

        return [
            'encontrado' => true,
            'respuesta'  => "De acuerdo con el material oficial del curso ({$top['unidad']}): " . $top['texto'],
            'fuentes'    => [[
                'fragmento_id' => $top['fragmento_id'],
                'documento'    => $top['documento'],
                'unidad'       => $top['unidad'],
                'pagina'       => $top['pagina'],
                'texto'        => $top['texto']
            ]]
        ];
    }

    $nombreCurso = $curso ? $curso['nombre'] : 'este curso';
    return [
        'encontrado' => false,
        'respuesta'  => "No se encontró información suficiente en los materiales académicos autorizados para {$nombreCurso}. Como asistente EduMentor RAG, respondo exclusivamente sobre la base documental autorizada por el docente para evitar información no sustentada.",
        'fuentes'    => []
    ];
}

// ========================= BANCO DE PREGUNTAS Y EVALUACIONES =========================
function obtenerPreguntasBanco($curso_id = null) {
    $pdo = obtenerConexionBD();
    if ($curso_id !== null && $curso_id > 0) {
        $st = $pdo->prepare('SELECT * FROM preguntas_banco WHERE curso_id = ? ORDER BY id');
        $st->execute([(int)$curso_id]);
        $filas = $st->fetchAll();
    } else {
        $filas = $pdo->query('SELECT * FROM preguntas_banco ORDER BY id')->fetchAll();
    }
    foreach ($filas as &$p) {
        $p['opciones_incorrectas'] = array_values(array_filter(explode(SEPARADOR_OPCIONES, (string)$p['opciones_incorrectas']), 'strlen'));
        $p['explicacion'] = (string)$p['explicacion'];
    }
    unset($p);
    return $filas;
}

function guardarPreguntaBanco($curso_id, $enunciado, $opcion_correcta, $opciones_incorrectas, $explicacion = '') {
    $lista = is_array($opciones_incorrectas) ? array_values($opciones_incorrectas) : [trim($opciones_incorrectas)];
    $pdo = obtenerConexionBD();
    $st = $pdo->prepare('INSERT INTO preguntas_banco (curso_id, enunciado, opcion_correcta, opciones_incorrectas, explicacion) VALUES (?, ?, ?, ?, ?)');
    $st->execute([(int)$curso_id, trim($enunciado), trim($opcion_correcta), implode(SEPARADOR_OPCIONES, $lista), trim($explicacion)]);
    return [
        'id'                   => (int)$pdo->lastInsertId(),
        'curso_id'             => (int)$curso_id,
        'enunciado'            => trim($enunciado),
        'opcion_correcta'      => trim($opcion_correcta),
        'opciones_incorrectas' => $lista,
        'explicacion'          => trim($explicacion)
    ];
}

function evaluarRespuestasQuiz($curso_id, $respuestasUsuario) {
    $banco = obtenerPreguntasBanco($curso_id);
    $total = count($banco);
    if ($total === 0) {
        return ['total' => 0, 'correctas' => 0, 'nota' => 0, 'detalles' => []];
    }

    $correctas = 0;
    $detalles = [];
    foreach ($banco as $p) {
        $pid = $p['id'];
        $resp = isset($respuestasUsuario[$pid]) ? trim($respuestasUsuario[$pid]) : '';
        $esCorrecta = ($resp === $p['opcion_correcta']);
        if ($esCorrecta) {
            $correctas++;
        }
        $detalles[] = [
            'pregunta_id'     => $pid,
            'enunciado'       => $p['enunciado'],
            'respuesta_dada'  => $resp,
            'opcion_correcta' => $p['opcion_correcta'],
            'es_correcta'     => $esCorrecta,
            'explicacion'     => $p['explicacion']
        ];
    }

    return [
        'total'     => $total,
        'correctas' => $correctas,
        'nota'      => round(($correctas / $total) * 20, 1),
        'detalles'  => $detalles
    ];
}

// ========================= ESTADÍSTICAS =========================
function obtenerEstadisticas() {
    $pdo = obtenerConexionBD();
    $contar = function($tabla) use ($pdo) {
        return (int)$pdo->query("SELECT COUNT(*) FROM $tabla")->fetchColumn();
    };

    $utiles = $parciales = $noUtiles = 0;
    foreach ($pdo->query('SELECT utilidad, COUNT(*) AS n FROM valoraciones WHERE utilidad IS NOT NULL GROUP BY utilidad')->fetchAll() as $r) {
        if ($r['utilidad'] === 'util')    $utiles    = (int)$r['n'];
        if ($r['utilidad'] === 'parcial') $parciales = (int)$r['n'];
        if ($r['utilidad'] === 'no_util') $noUtiles  = (int)$r['n'];
    }

    $reportes = ['incorrecta' => 0, 'incompleta' => 0, 'fuente_no_relacionada' => 0, 'no_sustentada' => 0];
    foreach ($pdo->query('SELECT reporte, COUNT(*) AS n FROM valoraciones WHERE reporte IS NOT NULL GROUP BY reporte')->fetchAll() as $r) {
        $reportes[$r['reporte']] = (int)$r['n'];
    }

    $totalValoradas = $utiles + $parciales + $noUtiles;

    return [
        'total_consultas'       => $contar('consultas'),
        'total_cursos'          => $contar('cursos'),
        'total_materiales'      => $contar('materiales'),
        'total_fragmentos'      => $contar('fragmentos'),
        'total_banco_preguntas' => $contar('preguntas_banco'),
        'utiles'                => $utiles,
        'parciales'             => $parciales,
        'no_utiles'             => $noUtiles,
        'porcentaje_util'       => $totalValoradas > 0 ? round(($utiles / $totalValoradas) * 100) : 100,
        'reportes'              => $reportes
    ];
}
//Eliminar material
function eliminarMaterial($id) {
    $pdo = obtenerConexionBD();

    // Primero se lee el nombre del archivo para poder borrarlo del disco
    $st = $pdo->prepare('SELECT archivo FROM materiales WHERE id = ?');
    $st->execute([(int)$id]);
    $archivo = $st->fetchColumn();
    if ($archivo === false) {
        return false;
    }

    // Borra el material (sus fragmentos se borran solos por ON DELETE CASCADE)
    $pdo->prepare('DELETE FROM materiales WHERE id = ?')->execute([(int)$id]);

    // Borra el archivo físico de la carpeta uploads/
    $ruta = __DIR__ . '/../uploads/' . basename($archivo);
    if (is_file($ruta)) {
        unlink($ruta);
    }
    return true;
}
