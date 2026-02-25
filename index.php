<?php
session_start();

require "/var/www/practicas2026/app/aplicacionweb/configuracion/configuracion.php";
include _ROOT_.DW._INCLUDES_.DW."app.php";
include _ROOT_.DW._LIB_.DW."db.php";
include _ROOT_.DW._MOD_.DW._LOGIN_.DW._INCLUDES_.DW."logInA.php";
include _ROOT_.DW._MOD_.DW._LOGIN_.DW._INCLUDES_.DW."registroA.php";
include _ROOT_.DW._LIB_.DW."usuario.php";
include _ROOT_.DW._LIB_.DW."sesion.php";
include _ROOT_.DW._LIB_.DW."cliente.php";
include _ROOT_.DW._LIB_.DW."archivo.php";

# Zona horaria
date_default_timezone_set('Europe/Madrid');

if(empty($_SESSION['login'])){
    loginConCookie(getDB());
}

if($_SERVER['REQUEST_METHOD'] === 'POST'){
    include _CONTROLLERS_.DW."controller.php";
    exit;
}

# Pagina de carga de vista principal
include _ROOT_.DW._THEMES_.DW."view.php";
