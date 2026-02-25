/**
 * @brief Esta funcion se encarga de hacer una peticion al backend para crear un nuevo cliente con los datos que se le pasan por data.
 * Si la respuesta es true cierra el modal y renderiza a todos los clientes que recoja de la base de datos. En caso de que sea false 
 * se muestra un mensaje de error con el mensaje que devuelva el backend.
 * @fecha 23/02/2026
 * @return true = listaClientes || false = mensaje de error
 */

function crearCarpetaCliente(){
    const nombreCliente = document.getElementById('nombreCliente').value
    const telefonoCliente = document.getElementById('telefonoCliente').value
    const mensajeError = document.getElementById('mensajeErrorCliente')
    const contenedorError = document.getElementById('errorCrearCliente')


    $.ajax({
        url:  'index.php',
        method: 'POST',
        dataType: 'json',
        data: {
            accion:'crearCarpetaCliente',
            nombreCliente: nombreCliente,
            telefonoCliente: telefonoCliente
        },
        success: function(res) {
            if(res.ok === true){
                const modal = bootstrap.Modal.getInstance(document.getElementById('modalCrearCliente'));
                modal.hide();
                renderizarClientes(res.clientes)
                
            }else{
                contenedorError.classList.remove('d-none');
                mensajeError.textContent = res.mensaje
            }

        },error: function(xhr, status, error) {
            console.error('Error al crear la carpeta del cliente ', error, status, xhr);
            
        }
    })
}

/**
 * @brief Creamos accion de click sobre el botonAñadirCarpeta que mostrara el modal para poder rellenar los datos y crear el nuevo cliente.
 * @fecha 23/02/2026
 * @return void
 */
$(document).on('click', '#botonAñadirCarpeta', function (e){
    e.preventDefault();
    // Abrir el modal de modificar usuario
    const modal = document.getElementById('modalCrearCliente');
    new bootstrap.Modal(modal).show();
})

/**
 * @brief Accion de click sobre el boton del modal para realizar la funcion que crea la carpeta del cliente y el cliente.
 * @fecha 23/02/2026
 * @return void 
 */
$(document).on('click', '#botonCrear', function (e){
    e.preventDefault();
    crearCarpetaCliente()
})

/**
 * @brief Accion de click sobre el boton cancelar del modal para cancelar la creacion del nuevo cliente.
 * @fecha 23/02/2026
 * @return void 
 */
$(document).on('click', '#botonCancelarCreacionCliente', function (e){
    e.preventDefault();
    const modal = bootstrap.Modal.getInstance(document.getElementById('modalCrearCliente'));
    const nombreCliente = document.getElementById('nombreCliente')
    const telefonoCliente = document.getElementById('telefonoCliente')
    const mensajeError = document.getElementById('mensajeErrorCliente')
    const contenedorError = document.getElementById('errorCrearCliente')

    nombreCliente.value = ''
    telefonoCliente.value = ''
    mensajeError.value = ''
    contenedorError.classList.add('d-none');
    modal.hide();

})


