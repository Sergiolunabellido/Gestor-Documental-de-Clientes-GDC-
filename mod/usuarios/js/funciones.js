
/**
 * @brief Esta peticion nos permite modificar la contraseña de cualquier usuario en la base de datos, permitiendo
 * que si a este se le olvida cual era pueda cambiarla sin problema.
 * @fecha 26/01/2026
 * @return void
 */

let timeOut;

$(document).on('click', '.botonModificarUsuario', function (e){
    e.preventDefault();
    const idUsuario = this.dataset.idUsuario;
    

    // Enviar petición al servidor
    $.ajax({
        url: 'index.php',
        method: 'POST',
        dataType: 'json',
        data:{
            accion:'modificarUsuario',
            idUsuario: idUsuario,
        },
        success: function(res) {
            if(res.ok === true){

               // Abrir el modal de modificar usuario
                const modal = document.getElementById('modalModificarUsuario');
                modal.dataset.idUsuario = idUsuario;
                new bootstrap.Modal(modal).show();
                
                
            } else {
               alert(res.msg || 'Error al abrir el modal');
            }
        },
        error: function(xhr, status, error) {
            console.error('Error al abrir el modal:', error, status, xhr);
            alert('Error de conexión al mostrar el modal');
        }
    });
  
})


/**
 * @brief Estos eventos controlan el modal de modificar usuario para gestionar el tiempo de inactividad
 * y liberar el usuario en caso de que se cierre el modal sin pulsar ningun boton o expire el tiempo.
 * @fecha 03/02/2026
 * @return void
 */

const modal = document.getElementById('modalModificarUsuario');

modal.addEventListener('shown.bs.modal', function () {
    document.getElementById('formModificarUsuario').reset();
    document.getElementById('errorModificar').classList.add('d-none');
});

modal.addEventListener('hide.bs.modal', function () {
    if (document.activeElement) {
        document.activeElement.blur();
    }
});

modal.addEventListener('hidden.bs.modal', function (e) {
    const modal = document.getElementById('modalModificarUsuario');
    const idUsuario = modal.dataset.idUsuario;
    clearTimeout(timeOut);
    console.log(timeOut)
    $.ajax({
        url: 'index.php',
        method: 'POST',
        dataType: 'json',
        data:{
            accion:'modificarUsuario',
            idUsuario: idUsuario,
            filtro: 'cancelarModificacion'
        },
        success: function(res) {
            if(res.ok === false) {
            alert('Error al cancelar modificación del usuario');
            }else console.log('Usuario liberado correctamente')
        },
        error: function(xhr, status, error) {
            console.error('Error al cancelar modificación del usuario:', error, status, xhr);
            alert('Error de conexión al cancelar modificación del usuario');
        }
    });
})

window.addEventListener('pagehide', function () {
    const modal = document.getElementById('modalModificarUsuario');
    if (!modal) return;

    const idUsuario = modal.dataset.idUsuario;
    if (!idUsuario) return;

    const data = new FormData();
    data.append('accion', 'modificarUsuario');
    data.append('idUsuario', idUsuario);
    data.append('filtro', 'cancelarModificacion');

    navigator.sendBeacon('index.php', data);
});



/**
 * @brief Evento para confirmar la modificación del usuario
 * @fecha 02/02/2026
 * @return void
 */


$(document).on('click', '#botonModificar', (e) =>{
    e.preventDefault();

    const modal = document.getElementById('modalModificarUsuario');
    const idUsuario = modal.dataset.idUsuario;
    const divUsuario = document.getElementById(`usuario-${idUsuario}`);
    console.log('Usuario actual:', divUsuario);
    
    const nuevoNombre = document.getElementById('nuevoNombre').value;
    const nuevoEmail = document.getElementById('nuevoEmail').value;
    const nombreUsuarioActual = divUsuario.querySelector('.labelNombreUsuarioActual').textContent;
   


    // Enviar petición al servidor
    $.ajax({
        url: 'index.php',
        method: 'POST',
        dataType: 'json',
        data:{
            accion:'modificarUsuario',
            usuario: nombreUsuarioActual,
            nuevoUsuario: nuevoNombre,
            email: nuevoEmail,
            filtro: 'actualizarUsuario',
            idUsuario: idUsuario
        },
        success: function(res) {
            if(res.ok === true){

                document.getElementById('formModificarUsuario').reset();
                document.getElementById('errorModificar').classList.add('d-none');

                // Cerrar el modal
                const modal = bootstrap.Modal.getInstance(document.getElementById('modalModificarUsuario'));
                modal.hide();
                $.ajax({
                    url: 'index.php',
                    method: 'POST',
                    dataType: 'json',
                    data:{
                        accion:'modificarUsuario',
                        idUsuario: idUsuario,
                        filtro: 'cancelarModificacion'
                    },
                    success: function(res) {
                        if(res.ok === false) {
                        alert('Error al cancelar modificación del usuario');
                        }else console.log('Usuario liberado correctamente')
                    },
                    error: function(xhr, status, error) {
                        console.error('Error al cancelar modificación del usuario:', error, status, xhr);
                        alert('Error de conexión al cancelar modificación del usuario');
                    }
                });
                
                
                
               
            } else {
                document.getElementById('errorModificar').classList.remove('d-none');
                document.getElementById('mensajeErrorModificar').textContent = res.msg || 'Ha habido un error al modificar el usuario.';
                console.log(res.msg)
            }
        },
        error: function(xhr, status, error) {
            console.error('Error al modificar usuario:', error, status, xhr);
            alert('Error de conexión al modificar el usuario');
        }
    });

});




/**
 * @brief Eventos para eliminar un usuario
 * @fecha 03/02/2026
 * @return void
 */


$(document).on('click', '.botonEliminarUsuario', function (e){
    e.preventDefault();
    const idUsuario = this.dataset.idUsuario;

    // Abrir el modal de modificar usuario
    const modal = document.getElementById('modalEliminarUsuario');
    modal.dataset.idUsuario = idUsuario;
    new bootstrap.Modal(modal).show();
            
  
})


$(document).on('click', '#botonEliminar', function (e){
    e.preventDefault();

    const modal =  document.getElementById('modalEliminarUsuario');
    const idusuario =  modal.dataset.idUsuario;
    const idUsuarioAEliminar = idusuario; // ID del usuario que se está eliminando
    const currentLoggedInUserId = localStorage.getItem('usuarioId'); // ID del usuario actualmente logueado

      $.ajax({
        url: 'index.php',
        method: 'POST',
        dataType: 'json',
        data: {
            accion:'eliminarUsuarios',
            idUsuario: idUsuarioAEliminar
        },
        success: function(res) {
            if(res.ok === true){
                modificarEstadoUsuario('desconectado', idUsuarioAEliminar);
                // Cerrar el modal
                const modal = bootstrap.Modal.getInstance(document.getElementById('modalEliminarUsuario'));
                modal.hide();
                // Eliminar el div del usuario de la lista
                const divUsuarioAEliminar = document.getElementById(`usuario-${idUsuarioAEliminar}`);
                if (divUsuarioAEliminar) {
                    divUsuarioAEliminar.remove();
                }

                // Si el usuario eliminado es el usuario actualmente logueado, cerrar su sesión
                if (currentLoggedInUserId && currentLoggedInUserId == idUsuarioAEliminar) {
                    alert('Tu sesión ha sido cerrada porque tu usuario ha sido eliminado.');
                    logout(); // Llama a la función global de logout
                }
            } else {
                alert(res.msg || 'Ha habido un error al eliminar el usuario.');
                console.log(res.msg)
            }
        },
        error: function(xhr, status, error) {
            console.error('Error al eliminar usuario:', error, status, xhr);
        }
    });

});




/**
 * @brief Segun los campas que esten rellenos en el formulario de filtro de usuarios
 * se realizara una peticion ajax al back-end para filtrar los usuarios por nombre,
 * correo o fecha de creacion.
 * @fecha 22/01/2026
 * @return html
 */


$(document).on('input', '#nombreUserFiltro', (e) => {
    e.preventDefault()
    let nombreUserFiltro =  $('#nombreUserFiltro').val()
    $.ajax({
            url:  'index.php',
            method: 'POST',
            dataType: 'json',
            data: {
                accion:'usuarios',
                filtro: 'nombre',
                usuario: nombreUserFiltro
            },
            success: function(res) {

                renderizarUsuarios(res.usuarios);
            },error: function(xhr, status, error) {
                console.error('Error al cargar la vista home', error, status, xhr);
                
            }
        })

})

$(document).on('input', '#correoUserFiltro', (e) => {
    e.preventDefault()

    let correoUserFiltro =  $('#correoUserFiltro').val()
    $.ajax({
            url:  'index.php',
            method: 'POST',
            dataType: 'json',
            data: {
                accion:'usuarios',
                filtro: 'correo',
                email: correoUserFiltro
            },
            success: function(res) {

                renderizarUsuarios(res.usuarios);

            },error: function(xhr, status, error) {
                console.error('Error al cargar la vista home', error, status, xhr);
                
            }
        })

})

$(document).on('input', '#correoUserFiltro', (e) => {
    e.preventDefault()
    let fechaUserFiltro =  $('#fechaUserFiltro').val()
    
    
     $.ajax({
            url:  'index.php',
            method: 'POST',
            dataType: 'json',
            data: {
                accion:'usuarios',
                filtro: 'fecha',
                fecha: fechaUserFiltro
            },
            success: function(res) {
               renderizarUsuarios(res.usuarios);


            },error: function(xhr, status, error) {
                console.error('Error al cargar la vista home', error, status, xhr);
                
            }
        })

})




$(document).on('click', '#botonFiltrar', (e)=>{
    let nombreUserFiltro =  $('#nombreUserFiltro').val()
    let correoUserFiltro =  $('#correoUserFiltro').val()
    let fechaUserFiltro =  $('#fechaUserFiltro').val()
   e.preventDefault()
   

    if(nombreUserFiltro === '' && correoUserFiltro === '' && fechaUserFiltro ===''){
        $.ajax({
        url:  'index.php',
        method: 'POST',
        dataType: 'json',
        data: {
            accion:'usuarios',
        },
        success: function(res) {
            $('#estaticos').html(res.estaticos);
            $('#contenido').html(res.contenido);
            const divGenerico = document.getElementById('divs-usuarios')
            divGenerico.innerHTML = '';

            renderizarUsuarios(res.usuarios);

        },error: function(xhr, status, error) {
            console.error('Error al cargar la vista home', error, status, xhr);
            
        }
    })
        return;
    }else if(nombreUserFiltro === '' && correoUserFiltro === ''){
        $.ajax({
            url:  'index.php',
            method: 'POST',
            dataType: 'json',
            data: {
                accion:'usuarios',
                filtro: 'fecha',
                fecha: fechaUserFiltro
            },
            success: function(res) {
               renderizarUsuarios(res.usuarios);


            },error: function(xhr, status, error) {
                console.error('Error al cargar la vista home', error, status, xhr);
                
            }
        })
    }
})


