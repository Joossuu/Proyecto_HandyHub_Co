<?php
// index.php - Login corregido
session_start();

// Mostrar errores en desarrollo (quitar en producción)
ini_set('display_errors', 1);
error_reporting(E_ALL);

// incluir configuración DB (asegúrate que define $conn)
require_once __DIR__ . '/config/db_config.php';

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // Validaciones básicas
    $usuario = isset($_POST['usuario']) ? trim($_POST['usuario']) : '';
    $password = isset($_POST['password']) ? $_POST['password'] : '';

    if ($usuario === '' || $password === '') {
        $error = "Por favor ingresa usuario y contraseña.";
    } else {
        // Comprueba que la conexión $conn exista
        if (!isset($conn) || !($conn instanceof mysqli)) {
            $error = "Error en la conexión a la base de datos.";
        } else {
            // Consulta: obtenemos credencial y datos de usuario
            $query = "SELECT u.ID_Usuario, u.Usuario_Login, u.ID_Rol, r.Nombre_Rol, c.Password_Hash
                      FROM Usuario u
                      JOIN Rol r ON u.ID_Rol = r.ID_Rol
                      JOIN Credencial c ON u.ID_Usuario = c.ID_Usuario
                      WHERE u.Usuario_Login = ? AND u.Estado = 'Activo' AND c.Estado = 'Activo'
                      LIMIT 1";

            if ($stmt = $conn->prepare($query)) {
                $stmt->bind_param("s", $usuario);
                if ($stmt->execute()) {
                    $result = $stmt->get_result();
                    if ($result && $result->num_rows === 1) {
                        $row = $result->fetch_assoc();
                        // Verificar contraseña (asume password_hash)
                        if (password_verify($password, $row['Password_Hash'])) {
                            // Login exitoso: regenerar sesión
                            session_regenerate_id(true);

                            // Guardar estructura de sesión estándar
                            $_SESSION['user'] = [
                                'ID_Usuario' => (int)$row['ID_Usuario'],
                                'Usuario_Login' => $row['Usuario_Login'],
                                'ID_Rol' => (int)$row['ID_Rol'],
                                'Nombre_Rol' => $row['Nombre_Rol']
                            ];

                            // Registrar en bitácora (si falla no detiene el login)
                            try {
                                $accion = "Inicio de sesión";
                                $ip = $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
                                $ins = $conn->prepare("INSERT INTO Bitacora (ID_Usuario, Accion, IP_Origen) VALUES (?, ?, ?)");
                                if ($ins) {
                                    $ins->bind_param("iss", $row['ID_Usuario'], $accion, $ip);
                                    $ins->execute();
                                    $ins->close();
                                }
                            } catch (Throwable $e) {
                                error_log("Bitacora insert failed: " . $e->getMessage());
                            }

                            // Redirigir al dashboard
                            header("Location: views/dashboard.php");
                            exit;
                        } else {
                            $error = "Contraseña incorrecta.";
                        }
                    } else {
                        $error = "Usuario no encontrado o inactivo.";
                    }
                    $stmt->close();
                } else {
                    $error = "Error al ejecutar consulta: " . htmlspecialchars($stmt->error);
                }
            } else {
                $error = "Error en la consulta (prepare): " . htmlspecialchars($conn->error);
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <title>Login - HandyHub</title>
  <link rel="stylesheet" href="assets/login.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body class="login-page">
  <div class="login-container">
    <div class="login-box">
      <h2>Iniciar sesión</h2>
      <?php if (!empty($error)): ?>
        <div class="error"><?= htmlspecialchars($error) ?></div>
      <?php endif; ?>
      <form method="POST" autocomplete="off">
        <div class="input-group">
          <i class="fas fa-user"></i>
          <input type="text" name="usuario" placeholder="Usuario" required value="<?= isset($usuario) ? htmlspecialchars($usuario) : '' ?>">
        </div>
        <div class="input-group">
          <i class="fas fa-lock"></i>
          <input type="password" name="password" placeholder="Contraseña" required>
        </div>
        <button type="submit">Ingresar</button>
      </form>
      <div class="links">
        <a href="#">¿Olvidaste tu contraseña?</a>
      </div>
    </div>
  </div>
</body>
</html>
