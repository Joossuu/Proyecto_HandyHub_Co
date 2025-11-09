<?php
// config/require_role.php
// Requiere session_check.php que inicie sesión y defina $_SESSION['rol'] y $_SESSION['id_usuario']
require_once __DIR__ . '/session_check.php';

/**
 * require_role(array $rolesAllowed)
 */
function require_role(array $rolesAllowed = []) {
    if (!isset($_SESSION['id_usuario'])) {
        header('Location: /index.php'); exit;
    }
    if (!empty($rolesAllowed) && !in_array($_SESSION['rol'], $rolesAllowed)) {
        // Puedes ajustar las redirecciones por rol si quieres landing pages distintas
        header('Location: /views/dashboard.php'); exit;
    }
}

/**
 * simple helper para comprobar si usuario tiene rol
 */
function has_role($roleName) {
    return isset($_SESSION['rol']) && $_SESSION['rol'] === $roleName;
}
