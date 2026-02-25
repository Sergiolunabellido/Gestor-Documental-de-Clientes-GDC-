/**
 * @brief En este js lanzamos funciones de click a los botones/enlaces del login
 * y registro para poder mostrar uno u otro, pudiendo iniciar sesion o hacer un 
 * registro en la misma pagina.
 * @fecha 21/01/2026
 * @return void
 */
$(document).on('click', '#iniciarSesion', (e) =>{
    e.preventDefault()

    const inicioSesion = document.getElementById('inicioSesion');
    const registro = document.getElementById('registro');

    inicioSesion.classList.remove('d-none');
    registro.classList.add('d-none');
})

$(document).on('click', '#iniciarRegistro', (e) =>{
    e.preventDefault();

    const inicioSesion = document.getElementById('inicioSesion');
    const registro = document.getElementById('registro');

    inicioSesion.classList.add('d-none');
    registro.classList.remove('d-none');
})

/**
 * @brief Este apartado asigna una funcion al boton de registro para que realice
 * una peticion al backend para crear un usuario nuevo en la base de datos con los 
 * datos insertados en los respectivos campos.
 * @fecha 21/01/2026
 * @return boolean
 */

$(document).on('click', '#botonRegistrarse', (e)=>{
    e.preventDefault();


    usuario = document.getElementById('nombreUsuarioRegistro').value;
    correo = document.getElementById('correoElectronico').value;
    contrasenia = document.getElementById('contraseniaRegistro').value;
    admin = document.getElementById('checkAdministrador').checked;


    $.ajax({
        url:  'index.php',
        method: 'POST',
        dataType: 'json',
        data: {
            usuario: usuario,
            password: contrasenia,
            email: correo,
            esAdmin: admin,
            accion: 'registro'
        },
        success: function(res) {    
            if(res.ok === false){
                document.getElementById('contenidoErrorRegistro').textContent = res.msg;
                mostrarErrorRegistro();
                return;
            }

            const inicioSesion = document.getElementById('inicioSesion');
            const registro = document.getElementById('registro');

            inicioSesion.classList.remove('d-none');
            registro.classList.add('d-none');


        },error: function(xhr, status, error) {
            console.error('Error al registrar el usuario: ', error, xhr , status);
            
        }
    })
});


/**
 * @brief Esta funcion realiza una peticion ajax al 
 * back-end para realizar el cambio de vista de la 
 * pagina de Inicio de Sesion a la vista Home.
 * @Fecha de creación: 2026-01-12
 * @return Devuelve el HTML de la vista home y los inserta en el html dentro del div con id "contenido".
 */   


function home(vista){
    let nombreU = document.getElementById("nombreUsuario").value;
    let contrasenia = document.getElementById("contraseniaUsuario").value;
    let recordarme = document.getElementById("checkRecordarme");

    $.ajax({
        url:  'index.php',
        method: 'POST',
        dataType: 'json',
        data: {
            accion:vista,
            usuario:nombreU,
            password:contrasenia,
            recordarme: recordarme.checked
        },
        success: function(res) {
          

            if(res.estado === 'pendiente'){
                alert(res.msg);
            }else if(res.estado === 'conectado'){
                $('#estaticos').html(res.estaticos);
                $('#contenido').html(res.contenido);

                
                marcarSesionActiva(); // Marcar sesión activa en sessionStorage
                if (res.sessionId) {
                    localStorage.setItem('sessionId', res.sessionId);
                }
                if (res.usuarioId) {
                    localStorage.setItem('usuarioId', res.usuarioId);
                }
                manejarExpiracionSesion(res.expiraEn);
            }

            if(res.ok === false){
                document.getElementById('contenedorError').textContent = res.msg;
                mostrarError();
                return;
            }
            

        },error: function(xhr, status, error) {
            console.error('Error al cargar la vista home', error, status, xhr);
            
        }
    })
}



$(document).on('click','#botonIniciar',async (e) => {
        e.preventDefault();
        home('login');
})

/** 
* @brief Este metodo se encarga de mostrar el div que contiene el mensaje de error 
* que se mostrara cuando las credenciales introducidas por el usuario en el login
* no se encuentren en la base de datos, haciendo saber al usuario que a introducido
* mal los datos o que no esta registrado.
* @fecha 21/01/2026
* @return void
*/

function mostrarError() {
    const error = document.getElementById('contenedorError');
    if (!error) return;
    error.classList.remove('d-none');
}

function mostrarErrorRegistro() {
    const error = document.getElementById('contenedorErrorRegistro');
    if (!error) return;
    error.classList.remove('d-none');
}

/**
 * @brief Esta parte del codigo permite al pulsar el boton cancelar borrar
 *  todo contenido de los inputs y el error posiblemente mostrado al introducir 
 *  los datos malamente para borrar todos los datos.
 * @fecha 21/01/2026
 * @return void
 */

$(document).on('click', '#botonCancelar', (e) => {
    e.preventDefault();
    const nombre = document.getElementById("nombreUsuario");
    const contrasenia = document.getElementById("contraseniaUsuario");
    const error = document.getElementById('contenedorError');
    const recuerdame = document.getElementById('checkRecordarme')
    error.classList.add('d-none')
    nombre.value = ''
    contrasenia.value = ''
    recuerdame.checked = false;
})

/**
 * @brief Esta parte del codigo permite al pulsar el boton cancelar del registro borrar
 *  todo contenido de los inputs y el error posiblemente mostrado al introducir 
 *  los datos malamente para borrar todos los datos.
 * @fecha 21/01/2026
 * @return void
 */

$(document).on('click', '#botonCancelarRegistro', (e) => {
    e.preventDefault();
    const nombre = document.getElementById("nombreUsuarioRegistro");
    const correo = document.getElementById("correoElectronico");
    const contrasenia = document.getElementById("contraseniaRegistro");
    const error = document.getElementById('contenedorErrorRegistro');
    const admin = document.getElementById('checkAdministrador')
    error.classList.add('d-none')
    nombre.value = ''
    correo.value = ''
    contrasenia.value = ''
    admin.checked = false;
})


/**
 * @brief Esta peticion nos permite modificar la contraseña de cualquier usuario en la base de datos, permitiendo
 * que si a este se le olvida cual era pueda cambiarla sin problema.
 * @fecha 26/01/2026
 * @return void
 */

$(document).on('click', '#cambiarContraseña', (e) =>{
    e.preventDefault();
    
    // Abrir el modal de cambiar contraseña
    const modal = new bootstrap.Modal(document.getElementById('modalCambiarContraseña'));
    modal.show();
})



/**
 * @brief Evento para confirmar el cambio de contraseña
 * @fecha 26/01/2026
 * @return void
 */

$(document).on('click', '#botonConfirmarCambio', (e) =>{
    e.preventDefault();
    
    const usuario = document.getElementById('usuarioRecuperacion').value;
    const nuevaContrasenia = document.getElementById('nuevaContraseña').value;
    const confirmarContrasenia = document.getElementById('confirmarContraseña').value;
    const errorDiv = document.getElementById('errorCambio');
    const mensajeError = document.getElementById('mensajeErrorCambio');
    
    // Validar que las contraseñas coincidan
    if(nuevaContrasenia !== confirmarContrasenia){
        mensajeError.textContent = 'Las contraseñas no coinciden';
        errorDiv.classList.remove('d-none');
        return;
    }
    
    // Enviar petición al servidor
    $.ajax({
        url: 'index.php',
        method: 'POST',
        dataType: 'json',
        data: {
            accion:'cambiarContrasenia',
            usuario: usuario,
            contrasenia: nuevaContrasenia
        },
        success: function(res) {
            if(res.ok === true){
                // Limpiar el formulario
                document.getElementById('formCambiarContraseña').reset();
                errorDiv.classList.add('d-none');
                
                // Cerrar el modal
                const modal = bootstrap.Modal.getInstance(document.getElementById('modalCambiarContraseña'));
                modal.hide();
                
                alert('Se ha cambiado tu contraseña correctamente');
            } else {
                mensajeError.textContent = res.msg || 'Ha habido un error al modificar tu contraseña.';
                errorDiv.classList.remove('d-none');
                console.log(res.msg)
            }
        },
        error: function(xhr, status, error) {
            console.error('Error al cambiar contraseña:', error, status, xhr);
            mensajeError.textContent = 'Error de conexión al cambiar la contraseña';
            errorDiv.classList.remove('d-none');
        }
    });
})



/**
 * @brief ESTAS VARIABLES SE USARAN EN LAS FUNCIONES QUE SE ESCRIBAN DEBAJO DE ESTAS.
 */
let expiracionSesion = null;
let intervalo = null; 
let avisoMostrado = false;

const AVISO_EXPIRACION = 30 * 1000;

/**
 * @brief Esta funcion inicia la cuenta atras de la sesion de cualquier usuario que se loguee
 * esta funcion se lanzara al realizar al accion de login para que comienze el tiempo nada mas iniciar sesion
 * @fecha 28/01/2026
 * @return void
 * @param tiempo
 */

function iniciarTemporizador(tiempo){
    expiracionSesion = tiempo * 1000;
    avisoMostrado = false;

    if(intervalo) clearInterval(intervalo);
    intervalo = setInterval(controlarSesion, 1000)
    console.log('Tiempo iniciado')
}


/**
 * @brief manejarExpiracionSesion guarda el valor que recivimos del back-end en el localStorage
 * y lanza la funcion que inicia el temporizador.
 * @param tiempo 
 * @fecha 28/01/2026
 * @returns function
 */
function manejarExpiracionSesion (tiempo){
    localStorage.setItem('expiraEn', tiempo);
    iniciarTemporizador(tiempo);
}

/**
 * @brief ControlarSesion se encarga de controlar el tiempo de la sesion haciendo que cuando 
 * queden 30 segundos/minutos... muestre la alerta de si quiere o no seguir con la sesion, 
 * o finalizar la sesion si este tiempo restante que queda entre el tiempo en que se inicio sesion
 * y en el que tiene que finalizar la sesion es menor que 0
 * @fecha 28/01/2026
 * @return functions
 */
function controlarSesion(){
    const ahora = Date.now();
    const restante = expiracionSesion - ahora;

    if(restante <= AVISO_EXPIRACION && !avisoMostrado){
        avisoMostrado = true;
        preguntarRenovacion();
    }

    if(restante <= 0){
        clearInterval(intervalo);
        cerrarSesion();
    }
}

function preguntarRenovacion(){

   const confirmacion = confirm('Desea continuar con la sesion?')
   if (confirmacion){
    renovarSesion();
   }else cerrarSesion();
}

/**
 * @brief Esta funcion permite renovar la sesion si el usuario indica en la alerta que quiere
 * seguir con la sesion iniciada antes de que se acabe el tiempo de la sesion.
 * @fecha 28/01/2026
 * @return function cerrarSesion() || function manejarEspiracionSesion()
 */
function renovarSesion(){
    $.ajax({
        url : "index.php",
        method : "POST",
        dataType: 'json',
        data:{
            accion: 'renovarSesion'
        },
        success: (res) => {
            if(!res.ok){
                cerrarSesion();
                console.log('no se inicia')
                location.reload();
                return;
            } else{
                if (res.sessionId) {
                    localStorage.setItem('sessionId', res.sessionId);
                }
                manejarExpiracionSesion(res.expiraEn) 
            } 
        },
        error: function(xhr, status, error) {
            console.error('Error al renovar la sesion en php:', error, status, xhr);
            cerrarSesion();
        }
    })
}

document.addEventListener('click', () => {
    if (localStorage.getItem('sessionId')) {
        renovarSesion();
    }
});



/**
 * @brief Esta funcion nos permite eliminar la variable expiraEn del localStorage y realizar
 * una peticion al back-end para que cierre la sesion que se encuentra activa y volver al login
 * a no se que el usuario antes del tiempo indique que quiere seguir con la sesion activa.
 * @fecha 28/01/2026
 * @return html || bool
 */
function cerrarSesion(){
    localStorage.removeItem('expiraEn')
    localStorage.removeItem('sessionId')

    $.ajax({
        url : "index.php",
        method: 'POST',
        dataType: 'json',
        data:{
            accion: 'logout'
        },
        success: (res) =>{
            
            if(res.ok){
                alert('Sesion cerrada')
                clearInterval(intervalo)
                sessionStorage.removeItem('sesionActiva');
                $('#estaticos').html('');
                $('#contenido').html(res.contenido);
                location.reload()
            }
        },
        error: function(xhr, status, error) {
            console.error('Error al cerrar la sesion en php:', error, status, xhr);
        }

    })
}


/**
 * @brief Este codigo permite al navegador que cuando el navegador cierre y este vuelva a abrirse
 * y el contenido se cargue que retome el tiempo de sesion que habia cuando este cerro el navegador. 
 * Para esto guardamos el valor que recogemos del back-end en el localStorage del navegador y asi
 * al volver a cargar el contenido cogemos este mismo tiempo haciendo la cuenta atras siga igual saltando
 * la alerta en el tiempo real al igual que si no hubiera cerrado el navegador.
 * @fecha 28/01/2026
 * @return void
 */

window.addEventListener('DOMContentLoaded', () => {
   
    const navigationEntry = performance.getEntriesByType("navigation")[0];
    const navigationType = navigationEntry ? navigationEntry.type : 'navigate';
    

    // Si la página se está recargando, no hacemos nada para evitar el logout.
    if (navigationType === 'reload') {
        console.log('Recarga de página detectada, no se enviará el beacon de logout.');
        return;
    }

    
    const tieneToken = document.cookie.includes('remember=');
    const sesionActiva = sessionStorage.getItem('sesionActiva');
    const usuarioId = localStorage.getItem('usuarioId');
    if (!tieneToken) {
        console.log('No tiene token de "remember". Cerrando sesion y volviendo a login.');

        $.ajax({
            url: 'index.php',
            method: 'POST',
            dataType: 'json',
            data: { accion: 'logout' },
            success: function(res) {
                localStorage.removeItem('expiraEn');
                localStorage.removeItem('sessionId');
                sessionStorage.removeItem('sesionActiva');
                $('#estaticos').html('');
                $('#contenido').html(res.contenido);
            },
            error: function(xhr, status, error) {
                console.error('Error al cerrar sesion automatica', error, status, xhr);
            }
        });
    } else if (tieneToken && !sesionActiva) {
        console.log('Tiene token pero no hay sesión');
        const data = new FormData();
        data.append('accion', 'logoutAutomatico'); // solo este dispositivo
        data.append('usuarioId', usuarioId);

        navigator.sendBeacon('index.php', data);
        
        home('login')
    }

    
});
window.addEventListener('beforeunload', () => {
    const navigationEntry = performance.getEntriesByType("navigation")[0];
    const navigationType = navigationEntry ? navigationEntry.type : 'navigate';

    if (navigationType === 'reload') {
        console.log('Recarga de página detectada, no se enviará el beacon de logout.');
        return;
    }
    const tieneToken = document.cookie.includes('remember=');
    if (!tieneToken) {
        const data = new FormData();
        data.append('accion', 'logout'); // logout global
        navigator.sendBeacon('index.php', data);
    }
});

/**
 * @brief Marca la sesión como activa cuando el usuario hace login
 * Se usa sessionStorage que se borra solo al cerrar el navegador (no al recargar F5)
 */
function marcarSesionActiva() {
    sessionStorage.setItem('sesionActiva', 'true');
}
