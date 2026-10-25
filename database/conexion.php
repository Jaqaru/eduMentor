<?php
/**
 * EduMentor RAG - Conexión PDO a MySQL (XAMPP)
 * Aplicaciones Distribuidas I - Grupo 03
 *
 * Los valores por defecto sirven para XAMPP local (usuario root, sin clave).
 * En otro entorno se pueden sobrescribir con variables de entorno
 * (DB_HOST, DB_PORT, DB_NAME, DB_USER, DB_PASS) sin tocar el código.
 */

function obtenerConexionBD() {
    static $pdo = null;
    if ($pdo !== null) {
        return $pdo;
    }

    $host    = getenv('DB_HOST') ?: '127.0.0.1';
    $puerto  = getenv('DB_PORT') ?: '3306';
    $dbname  = getenv('DB_NAME') ?: 'edumentor';
    $usuario = getenv('DB_USER') ?: 'root';
    $clave   = getenv('DB_PASS') !== false ? getenv('DB_PASS') : '';

    try {
        $dsn = "mysql:host={$host};port={$puerto};dbname={$dbname};charset=utf8mb4";
        $pdo = new PDO($dsn, $usuario, $clave, [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
        ]);
        return $pdo;
    } catch (PDOException $e) {
        // No se muestran credenciales ni detalles internos al usuario.
        error_log('EduMentor - error de conexión MySQL: ' . $e->getMessage());
        http_response_code(500);
        $msg = 'No se pudo conectar con la base de datos. Verifica que MySQL esté iniciado en XAMPP y que exista la base "edumentor" (importa database/edumentor.sql).';
        foreach (headers_list() as $h) {
            if (stripos($h, 'application/json') !== false) {
                die(json_encode(['error' => $msg]));
            }
        }
        die('<p style="font-family:sans-serif;padding:20px;color:#a00">' . htmlspecialchars($msg) . '</p>');
    }
}