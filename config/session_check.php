<?php
// config/session_check.php
if (session_status() === PHP_SESSION_NONE) session_start();

// Si no existe usuario logueado, redirigir a login (ajusta la ruta)
if (empty($_SESSION['id_usuario'])) {
    // Si la petición es ajax/api, devolvemos 401 JSON
    if (isset($_SERVER['HTTP_X_REQUESTED_WITH']) || strpos($_SERVER['REQUEST_URI'], '/api/') !== false) {
        header('Content-Type: application/json; charset=utf-8', true, 401);
        echo json_encode(['error' => 'No autorizado']);
        exit;
    } else {
        header('Location: ../index.php'); // o la ruta de tu login
        exit;
    }
}
