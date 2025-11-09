<?php
require_once 'db_config.php';

// Buscar todos los usuarios activos
$query = "SELECT ID_Usuario, Usuario_Login FROM Usuario WHERE Estado = 'Activo'";
$result = $mysqli->query($query);

if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $idUsuario = $row['ID_Usuario'];
        $usuarioLogin = $row['Usuario_Login'];

        // Generar contraseña única (ej: admin → adminpass)
        $passwordPlano = $usuarioLogin . 'pass';
        $hash = password_hash($passwordPlano, PASSWORD_DEFAULT);

        // Verificar si ya tiene credencial
        $check = $mysqli->prepare("SELECT ID_Credencial FROM Credencial WHERE ID_Usuario = ?");
        $check->bind_param("i", $idUsuario);
        $check->execute();
        $checkResult = $check->get_result();

        if ($checkResult->num_rows > 0) {
            // Actualizar hash existente
            $update = $mysqli->prepare("UPDATE Credencial SET Password_Hash = ?, Estado = 'Activo' WHERE ID_Usuario = ?");
            $update->bind_param("si", $hash, $idUsuario);
            $update->execute();
        } else {
            // Insertar nueva credencial
            $insert = $mysqli->prepare("INSERT INTO Credencial (ID_Usuario, Password_Hash, Estado) VALUES (?, ?, 'Activo')");
            $insert->bind_param("is", $idUsuario, $hash);
            $insert->execute();
        }

        echo "Usuario: $usuarioLogin → Contraseña: $passwordPlano<br>";
    }
} else {
    echo "No se encontraron usuarios activos.";
}
?>
