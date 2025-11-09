<?php
// config/utils.php
require_once __DIR__ . '/db_config.php';

/**
 * Obtiene siguiente código seguro (usa transacción y tabla Secuencias).
 * $prefix como 'PRE' o 'HERR'
 */
function next_code($mysqli, $seq_name, $prefix, $digits = 4) {
    $mysqli->begin_transaction();
    $sel = $mysqli->prepare("SELECT ultimo FROM Secuencias WHERE nombre = ? FOR UPDATE");
    if (!$sel) { $mysqli->rollback(); throw new Exception("No se pudo preparar select Secuencias: " . $mysqli->error); }
    $sel->bind_param("s", $seq_name);
    $sel->execute();
    $res = $sel->get_result();
    $row = $res->fetch_assoc();
    $ultimo = (int)($row['ultimo'] ?? 0);
    $nuevo = $ultimo + 1;
    $upd = $mysqli->prepare("UPDATE Secuencias SET ultimo = ? WHERE nombre = ?");
    if (!$upd) { $mysqli->rollback(); throw new Exception("No se pudo preparar update Secuencias: " . $mysqli->error); }
    $upd->bind_param("is", $nuevo, $seq_name);
    $upd->execute();
    $mysqli->commit();
    return sprintf("%s-%0{$digits}d", $prefix, $nuevo);
}

/**
 * Calcula estado/dias/mensaje del préstamo (row must have Fecha_Limite, Fecha_Devolucion)
 */
function calcular_estado_prestamo(array $row) {
    $now = new DateTime('now');
    $limite = !empty($row['Fecha_Limite']) ? new DateTime($row['Fecha_Limite']) : null;
    $devolucion = !empty($row['Fecha_Devolucion']) ? new DateTime($row['Fecha_Devolucion']) : null;

    if (!is_null($devolucion)) {
        return ['estado' => 'Completado', 'dias' => 0, 'mensaje' => 'Devuelto'];
    }
    if ($limite) {
        $diff = $now->diff($limite);
        $dias = (int)$diff->format('%r%a'); // puede ser negativo
        if ($dias < 0) {
            return ['estado' => 'Retrasado', 'dias' => abs($dias), 'mensaje' => 'Vencido hace ' . abs($dias) . ' días'];
        } else {
            return ['estado' => 'Activo', 'dias' => $dias, 'mensaje' => $dias . ' días restantes'];
        }
    }
    return ['estado' => $row['Estado'] ?? 'Activo', 'dias' => null, 'mensaje' => 'Sin fecha límite'];
}
