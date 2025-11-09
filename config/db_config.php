<?php
// config/db_config.php
define('DB_HOST', '127.0.0.1');
define('DB_PORT', 3306);
define('DB_USER', 'root');
define('DB_PASS', ''); // si tienes contraseña, ponla aquí
define('DB_NAME', 'handyhubdb');

mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

try {
    $conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME, DB_PORT);
    $conn->set_charset('utf8mb4');
} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode([
        'error' => true,
        'message' => 'Error en la conexión a la base de datos.',
        'detail' => $e->getMessage()
    ], JSON_UNESCAPED_UNICODE|JSON_PRETTY_PRINT);
    exit;
}

/**
 * Devuelve la conexión mysqli
 */
function get_db_connection() {
    global $conn;
    return $conn;
}
