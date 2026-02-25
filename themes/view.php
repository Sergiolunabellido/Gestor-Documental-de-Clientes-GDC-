<!DOCTYPE html>
<html lang="es">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <base href="<?php echo _URI_ ?>" />
        <title>Aplicacion web</title>
        <link rel="stylesheet" href="mod/detalleCliente/css/styles.css?v=1">
        <link href="bootstrap-5.3.8-dist/css/bootstrap.min.css" rel="stylesheet">
        <link rel="icon" type="image/svg+xml" href="<?php echo _URI_; ?>favicon.svg">
    </head>
    <body>
        <div>
            <div id="estaticos">
                <?php
                    if (!empty($_SESSION['login'])) {
                        ponerEstatico('home');
                    }
                ?>
                
            </div>
            <div id="contenido">
                <?php

                    if (!empty($_SESSION['login'])) {
                        cargarVista('home');
                    } else {
                        cargarVista('login');
                    }
                ?>
            </div>
        </div>

        <?php renderModalesBase(); ?>
        
        <script src="bootstrap-5.3.8-dist/js/jquery-3.7.1.min.js"></script>
        <script src="bootstrap-5.3.8-dist/js/bootstrap.bundle.min.js"></script>
        <script type="module" src="js/app.js"></script>
        <script src="mod/estatico/js/functions.js"></script>
        <script src="mod/login/js/script.js"></script>
        <script src="mod/perfil/js/funciones.js"></script>
        <script src="mod/usuarios/js/funciones.js"></script>
        <script src="mod/fuente/js/fuente.js"></script>
        <script src="mod/detalleCliente/js/detalleCliente.js"></script>
        <script src="mod/archivo/js/archivo.js"></script>
    </body>
</html>
