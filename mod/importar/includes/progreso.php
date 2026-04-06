<?php
session_start();
if (isset($_GET['reset'])) {
    $_SESSION['progreso_export'] = 0;
    $_SESSION['progreso_export_filas'] = ["procesadas" => 0, "total" => 0];
    session_write_close();
    if (ob_get_length()) ob_clean();
    header('Content-Type: application/json');
    echo json_encode([
        "porcentaje" => 0,
        "filas" => ["procesadas" => 0, "total" => 0]
    ]);
    exit;
}
if (ob_get_length()) ob_clean(); 
header('Content-Type: application/json');
$p = isset($_SESSION['progreso_export']) ? (int)$_SESSION['progreso_export'] : 0;
$p = max(0, min(100, $p));
$filas = $_SESSION['progreso_export_filas'] ?? ["procesadas" => 0, "total" => 0];
$filas['procesadas'] = (int)($filas['procesadas'] ?? 0);
$filas['total'] = (int)($filas['total'] ?? 0);
echo json_encode(["porcentaje" => $p, "filas" => $filas]);
exit;