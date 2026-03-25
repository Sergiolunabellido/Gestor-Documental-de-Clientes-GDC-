<?php
session_start();
if (isset($_GET['reset'])) {
    $_SESSION['progreso_export'] = 0;
    session_write_close();
    exit;
}
if (ob_get_length()) ob_clean(); 
header('Content-Type: application/json');
$p = isset($_SESSION['progreso_export']) ? (int)$_SESSION['progreso_export'] : 0;
echo json_encode(["porcentaje" => $p]);
exit;