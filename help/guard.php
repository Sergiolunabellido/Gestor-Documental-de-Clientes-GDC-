<?php

include _ROOT_.DW._MOD_.DW._PERFIL_.DW._INCLUDES_.DW."perfilA.php";

$usuarioId   = $_SESSION['idUsuario'] ?? null;
$sessionId   = session_id();

if ($_SESSION['eliminado'] == 1) {
    session_destroy();
    ob_start();
    cargarVista('login');
    $contenido = ob_get_clean();
    $estaticos = '';

    echo json_encode($response ?? [
        'estaticos' => $estaticos,
        'contenido' => $contenido,
    ], JSON_UNESCAPED_UNICODE);

    exit;
}

$sesionActiva = $sesionModel->sessionActiva($sessionId, $usuarioId);

if (!$sesionActiva) {
    $sesionModel->logoutForzoso($usuarioId);

    ob_start();
    cargarVista('login');
    $contenido = ob_get_clean();
    $estaticos = '';

    echo json_encode($response ?? [
        'estaticos' => $estaticos,
        'contenido' => $contenido,
    ], JSON_UNESCAPED_UNICODE);

    exit;
}

// Solo actualizar actividad si NO es verificarSesion
if (($_POST['accion'] ?? '') !== 'verificarSesion') {
    $sesionModel->actualizarActividad($sessionId, $usuarioId);
}

