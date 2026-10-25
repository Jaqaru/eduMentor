<?php
/**
 * EduMentor RAG - Configuración y Conexión PDO a MySQL
 * Aplicaciones Distribuidas I - Grupo 03
 */

$host = '127.0.0.1';
$puerto = '3306';
$dbname = 'edumentor';
$usuario = 'root';
$clave = '';

function obtenerConexionBD() {
    global $host, $puerto, $dbname, $usuario, $clave;
    
    try {
        $dsn = "mysql:host={$host};port={$puerto};dbname={$dbname};charset=utf8mb4";
        $opciones = [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
        ];
        return new PDO($dsn, $usuario, $clave, $opciones);
    } catch (PDOException $e) {
        // En caso de que MySQL aún no esté en ejecución durante la fase de prototipo
        return null;
    }
}
