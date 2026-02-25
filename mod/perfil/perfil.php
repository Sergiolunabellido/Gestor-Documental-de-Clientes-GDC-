<div class="vh-100">
    <div>
        <h1>  Perfil Usuario</h1>
        <div class=" d-flex  align-items-center justify-content-start gap-2">
            <div class=" d-flex flex-column align-items-center justify-content-center gap-3">
                
                <?php
                    $fotoPerfil = $_SESSION['foto'] ?? 'assets/images/istockphoto-824860820-612x612.jpg';
                    $fotoUrl = $fotoPerfil . (file_exists($fotoPerfil) ? '?v=' . filemtime($fotoPerfil) : 'assets/images/istockphoto-824860820-612x612.jpg');
                ?>

                <img src="<?php echo $fotoUrl; ?>" 
                alt="Foto de perfil" id="imagenPerfil" class="avatar w-75">
                
            </div>
            <div class=" d-flex flex-column align-items-start justify-content-around gap-3" id="contenedorDatosPerfil">
                
            </div>
            
        </div>
        <div id="listaUsuarios"></div>
    </div>
</div>


