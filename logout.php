<?php
require_once __DIR__ . '/backend/auth.php';
cerrarSesion();
header('Location: index.php');
exit;
