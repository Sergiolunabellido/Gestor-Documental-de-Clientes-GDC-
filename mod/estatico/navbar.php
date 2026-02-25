<nav id="navbar" class=" navbar d-flex justify-content-end align-items-center p-3 bg-light bg-opacity-75 h-50 ">
        <div class=" d-flex align-items-center justify-content-end gap-2 p-0 rounded">
            <p class="lead">En linea</p>
            <button id="usuario" class="btn btn-light w-25 h-25" data-bs-toggle="dropdown" aria-expanded="false" >
                <ul class=" dropdown-menu dropdown-menu-end" aria-labelledby="usuario">
                    <li><a class="dropdown-item" href="#" id="botonPerfil">Perfil</a></li>
                    <li><a class="dropdown-item" href="#" id="botonConfiguracion">Configuración</a></li>
                    <li><hr class="dropdown-divider"></li>
                    <li><a class="dropdown-item" href="#" id="botonCerrarSesion">Cerrar sesión</a></li>
            
                </ul>
                
                <?php
                    $fotoPerfil = $_SESSION['foto'] ?? 'assets/images/istockphoto-824860820-612x612.jpg';
                    $fotoUrl = $fotoPerfil . (file_exists($fotoPerfil) ? '?v=' . filemtime($fotoPerfil) : 'assets/images/istockphoto-824860820-612x612.jpg');
                ?>

               <img src="<?php echo $fotoUrl; ?>" 
                alt="Foto de perfil" class=" w-50 h-50 ">
            </button>
        </div>    
</nav>


