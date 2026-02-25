<?php
$script = `<script src="./js/script.js" ></script>`;
?>
<main>
    <div id="inicioSesion" class="main-login d-flex flex-column justify-content-center align-items-center vh-100">
        <div id="padre" class="d-flex flex-column align-items-center justify-content-center">
            <h2 class="from-label mb-3">Inicia sesión</h2>
            <div id="formulario-login" class="d-flex flex-column  align-items-center justify-content-center gap-1" >
                <form class="d-flex flex-column align-items-center justify-content-center gap-2 needs-validation" novalidate >
                    <input id="nombreUsuario" type="text" class="form-control" name="usuario" placeholder="Nombre de usuario" required>
                    <input id="contraseniaUsuario" type="password" class="form-control" name="password" placeholder="Contraseña" required>
                </form>
            </div>
            <div id="contenedorError"  class="mt-3 d-none alert alert-danger text-center text-red">
                <p id="contenidoError" >
                </p>
            </div>
            <div class=" mt-2">
                <label><input id="checkRecordarme" type="checkbox" > Recuerdame </label>
            </div>
            
            <div id="botones" class="d-flex mt-2 gap-3">
                <button id="botonIniciar" type="button" class="btn text-center bg-primary text-white bg-opacity-75 " > Iniciar </button>
                <button id="botonCancelar" class="btn text-center bg-primary text-white bg-opacity-75" > Cancelar </button>
            </div>
            <button id="iniciarRegistro" class="p-2 mt-2 text-blue border border-white bg-white ">
                No tienes cuenta? <a href="#"> Registrate </a>
            </button>
            <button id="cambiarContraseña" class="p-2 text-blue border border-white bg-white ">
                olvidaste la contraseña? <a class="text-primary " style="cursor: pointer">Cambiala aqui!</a> 
            </button>
            
            <!-- cambiar contraseña -->
            <div id="modalCambiarContraseña" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="modalTitle" aria-hidden="true">
                <div class="modal-dialog modal-dialog-centered" role="document">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title" id="modalTitle">Cambiar Contraseña</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            <form id="formCambiarContraseña" class="d-flex flex-column gap-3">
                                <div>
                                    <label for="usuarioRecuperacion" class="form-label">Usuario:</label>
                                    <input id="usuarioRecuperacion" type="text" class="form-control" placeholder="Ingresa tu usuario" required>
                                </div>
                                <div>
                                    <label for="nuevaContraseña" class="form-label">Nueva Contraseña:</label>
                                    <input id="nuevaContraseña" type="password" class="form-control" placeholder="Ingresa tu nueva contraseña" required>
                                </div>
                                <div>
                                    <label for="confirmarContraseña" class="form-label">Confirmar Contraseña:</label>
                                    <input id="confirmarContraseña" type="password" class="form-control" placeholder="Confirma tu nueva contraseña" required>
                                </div>
                                <div id="errorCambio" class="alert alert-danger d-none" role="alert">
                                    <p id="mensajeErrorCambio"></p>
                                </div>
                            </form>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                            <button id="botonConfirmarCambio" type="button" class="btn btn-primary">Confirmar Cambio</button>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>
    <div id="registro" class="d-none main-login d-flex flex-column justify-content-center align-items-center vh-100">
        <div id="padre" class="d-flex flex-column align-items-center justify-content-center">
            <h2 class="from-label mb-3">Registrarse</h2>
            <div id="formulario-login" class="d-flex flex-column  align-items-center justify-content-center gap-1" >
                <form class="d-flex flex-column align-items-center justify-content-center gap-2 needs-validation" novalidate >
                    <input id="nombreUsuarioRegistro" type="text" class="form-control" name="text" placeholder="Introduce tu usuario" required>
                    <input id="correoElectronico" type="text" class="form-control" name="correo" placeholder="Introduce tu correo" required>
                    <input id="contraseniaRegistro" type="password" class="form-control" name="password" placeholder="Introduce tu contraseña" required>
                </form>
            </div>
            <div id="contenedorErrorRegistro"  class="mt-3 d-none alert alert-danger text-center text-red">
                <p id="contenidoErrorRegistro">
                </p>
            </div>
            <div class=" mt-2">
                <label><input id="checkAdministrador" type="checkbox" > admin </label>
            </div>
            <div id="botones" class="d-flex mt-2 gap-3">
                <button id="botonRegistrarse" type="button" class="btn text-center bg-primary text-white bg-opacity-75 " > Registrarse </button>
                <button id="botonCancelarRegistro" class="btn text-center bg-primary text-white bg-opacity-75" > Cancelar </button>
            </div>
             <button id="iniciarSesion" class="p-2 mt-2 text-blue border border-white bg-white ">
                Ya tienes cuenta? <label class="text-primary " style="cursor: pointer">Iniciar Sesion</label> 
            </button>
        </div>
    </div>
</main>


