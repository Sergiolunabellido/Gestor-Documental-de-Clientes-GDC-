
/**
* @brief Esta funcion realiza una peticion ajax al pulsar el enlace cerrar sesion de la lista desplegable en el home, esta peticion se 
* realiza al back-end(index.php) que llama al controller.php para cerrar la sesion que actualmente tiene el usuario y volver a mostrar 
* la pantalla del login para volver a loguearse o simplemente salir.
* @Fecha 20/01/2026
* @return la peticion devuelve un html que se inserta en el html del documento view.php en el div contenido.
*/ 

function logout(){

    $.ajax({
        url:  'index.php',
        method: 'POST',
        dataType: 'json',
        data:{accion: 'logout'},
        success: function(res) {
                $('#estaticos').html('');
                $('#contenido').html(res.contenido);
        },error: function(xhr, status, error) {
            console.error('Error al cargar la vista home', error, " estado: ", status, " xhr: ", xhr);
        }
    })

}
$(document).on('click', '#botonCerrarSesion' , (e) =>{
    e.preventDefault();
    logout()
})


/**
 * @brief Realizamos una peticion el archivo index.php el cual contiene a controller
 * para poder cambiar el contenido del home dependiendo de que boton se pulse.
 * Este se realizara al pulsar el boton home que se encuentra en la barra lateral de la 
 * pantalla del home.
 * @fecha 21/01/2026
 * @return html
 */
$(document).on('click', '#home', (e) => {

    e.preventDefault()

    $.ajax({
        url:  'index.php',
        method: 'POST',
        dataType: 'json',
        data: {
            accion:'login',
        },
        success: function(res) {
          $('#estaticos').html(res.estaticos);
            $('#contenido').html(res.contenido);


        },error: function(xhr, status, error) {
            console.error('Error al cargar la vista home', error, status, xhr);
            
        }
    })

})



/**
 * @brief Realizamos una peticion el archivo index.php el cual contiene a controller
 * para poder cambiar el contenido del home dependiendo de que boton se pulse.
 * Este se realizara al pulsar el boton usuarios que se encuentra en la barra lateral de la 
 * pantalla del home.
 * @fecha 21/01/2026
 * @return html
 */
$(document).on('click', '#usuarios', (e) => {

    e.preventDefault()

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
            renderizarUsuarios(res.usuarios);
        }, error: function(xhr, status, error) {
            console.error('Error al cargar la vista de usuarios', error, status, xhr);
        }
    });

})



/**
 * @brief Realizamos una peticion el archivo index.php el cual contiene a controller
 * para poder cambiar el contenido del home dependiendo de que boton se pulse.
 * Este se realizara al pulsar el boton fuente que se encuentra en la barra lateral de la 
 * pantalla del home.
 * @fecha 21/01/2026
 * @return html
 */
$(document).on('click', '#fuente', (e) => {

    e.preventDefault()

    $.ajax({
        url:  'index.php',
        method: 'POST',
        dataType: 'json',
        data: {
            accion:'fuente',
        },
        success: function(res) {
            $('#estaticos').html(res.estaticos);
            $('#contenido').html(res.contenido);
            renderizarClientes(res.clientes)
            

        },error: function(xhr, status, error) {
            console.error('Error al cargar la vista fuente', error, status, xhr);
            
        }
    })

})

/**
 * @brief Realizamos una peticion el archivo index.php el cual contiene a controller
 * para poder cambiar el contenido del home dependiendo de que boton se pulse.
 * Este se realizara al pulsar el boton fuente que se encuentra en la barra lateral de la 
 * pantalla del home.
 * @fecha 21/01/2026
 * @return html
 */
$(document).on('click', '#archivo', (e) => {

    e.preventDefault()
    const tabla = localStorage.getItem('nombreTabla') || '';

    $.ajax({
        url:  'index.php',
        method: 'POST',
        dataType: 'json',
        data: {
            accion:'archivo',
        },
        success: function(res) {
            $('#estaticos').html(res.estaticos);
            $('#contenido').html(res.contenido);

            if (res.existeTabla && res.nombreTabla) {
                renderizarFicheros(res.nombreTabla);
            }
            
            

        },error: function(xhr, status, error) {
            console.error('Error al cargar la vista fuente', error, status, xhr);
            
        }
    })

})



/**
 * @brief Al pulsar en el perfil se hce una peticion al backend que devuelve los datos del usuario y los mostramos, creando dentro del div todos los elementos para mostrar los datos.
 */

$(document).on('click', '#botonPerfil', (e)=>{
    $.ajax({
        url:  'index.php',
        method: 'POST',
        dataType: 'json',
        data: {
            accion:'perfil',
        },
        success: function(res) {
          $('#estaticos').html(res.estaticos);
          $('#contenido').html(res.contenido);
            const divGenerico = document.getElementById('contenedorDatosPerfil')
            divGenerico.innerHTML = '';

            res.usuarios.forEach(usuarios =>{
            
                const labelNombre = document.createElement("label");
                labelNombre.textContent = usuarios.nombre;

                const labelCorreo = document.createElement("label");
                labelCorreo.textContent = usuarios.email;

                const labelContraseña = document.createElement("label");
                labelContraseña.textContent = "******";

                
                const divButtons = document.createElement("div")
                divButtons.className="d-flex flex-column align-items-center justify-content-around gap-3"

                const input = document.createElement("input")
                input.type = "file"
                input.id ="selectorImagenPerfil"

                const button = document.createElement("button")
                button.type ="submit"
                button.id="botonCambiarImagen"
                button.className="btn btn-primary"
                button.textContent="Cambiar Imagen"


                divButtons.appendChild(input)
                divButtons.appendChild(button)
                divGenerico.appendChild(labelNombre)
                divGenerico.appendChild(labelCorreo)
                divGenerico.appendChild(labelContraseña)
                divGenerico.appendChild(divButtons)

                
                divGenerico.appendChild(divUsuario)

            })

        },error: function(xhr, status, error) {
            console.error('Error al cargar la vista perfil', error, status, xhr);
            
        }
    })
})

/**
 * @brief Renderiza la lista de usuarios en el contenedor #divs-usuarios. Cada usuario se muestra con su foto de perfil, nombre, correo y botones para modificar o eliminar.
 * @param {Array<Object>} users - Lista de objetos de usuario, cada uno con propiedades como id, nombre, email, foto_perfil y timestamp.
 * @fecha 2026-02-09
 * @return {void}
 */
function renderizarUsuarios(users) {
    const divGenerico = document.getElementById('divs-usuarios');
    if (!divGenerico) {
        console.warn('Container #divs-usuarios not found.');
        return;
    }
    divGenerico.innerHTML = ''; // Clear existing content

    console.log('Rendering users:', users); // Debug: Check the users data
    users.forEach(user => {
        const divUsuario = document.createElement("div");
        divUsuario.className = "d-flex align-items-center justify-content-between rounded w-100 h-25 shadow p-2 m-3 ";
        divUsuario.id = "usuario-" + user.id;
        divUsuario.dataset.idUsuario = user.id;

        const divDatos = document.createElement("div");
        divDatos.className = "d-flex align-items-center justify-content-start m-2 gap-2";

        const imagenDatos = document.createElement("img");
        imagenDatos.className = "w-25 h-25 rounded hover"; // Se usa h-50 para consistencia con el filtro
        const fotoPorDefecto = "assets/images/istockphoto-824860820-612x612.jpg";
        const fotoBase = (typeof user.foto_perfil === "string" && user.foto_perfil.trim() !== "")
            ? user.foto_perfil.trim()
            : fotoPorDefecto;
        imagenDatos.src = fotoBase.includes('?v=') ? fotoBase : `${fotoBase}?v=${new Date().getTime()}`;
        imagenDatos.alt = "Foto de perfil";
        imagenDatos.onerror = function () {
            this.onerror = null;
            this.src = fotoPorDefecto;
        };

        const divTextoDatos = document.createElement("div");
        divTextoDatos.className = "d-flex flex-column justify-content-between";

        const contenedor = document.createElement("div");
        contenedor.style.display = "flex";        // para alinear horizontalmente
        contenedor.style.alignItems = "center";   // alineación vertical
        contenedor.style.gap = "6px"; 

        const imgIcono = document.createElement("img");
        imgIcono.src = "assets/images/estado.png";
        imgIcono.style.width = "20px";

        const labelEstado = document.createElement("label");
        labelEstado.textContent = user.estado;
        labelEstado.classList = "labelEstadoUsuario";
        labelEstado.dataset.idUsuario = user.id;


        const labelNombre = document.createElement("label");
        labelNombre.textContent = user.nombre;
        labelNombre.classList = "labelNombreUsuarioActual";
        labelNombre.dataset.idUsuario = user.id;

        const labelCorreo = document.createElement("label");
        labelCorreo.textContent = user.email;
        const labelContraseña = document.createElement("label");
        labelContraseña.textContent = "******";

        const divFecha = document.createElement("div");
        divFecha.className = "d-flex justify-content-center align-items-start ";
        const labelFecha = document.createElement("label");
        labelFecha.textContent = user.timestamp;

        const divButtons = document.createElement("div");
        divButtons.className = "d-flex flex-column align-items-center justify-content-center gap-2 w-25";
        const botonModificar = document.createElement("button");
        botonModificar.className = "btn btn-primary w-50 botonModificarUsuario";
        botonModificar.textContent = "Modificar";
        botonModificar.dataset.idUsuario = user.id;
        const botonEliminar = document.createElement("button");
        botonEliminar.className = "btn btn-danger w-50 botonEliminarUsuario";
        botonEliminar.textContent = "Eliminar";
        botonEliminar.dataset.idUsuario = user.id;

        const botonAlta = document.createElement("button");
        botonAlta.className = "btn btn-success w-50 ";
        botonAlta.textContent = "Dar alta";
        botonAlta.dataset.idUsuario = user.id;

        botonAlta.disabled = !(user.estado === "pendiente");

        botonAlta.addEventListener("click", () => {
            modificarEstadoUsuario('conectado', user.id);
            botonAlta.disabled = true;
        })

        divFecha.appendChild(labelFecha);
        
        divTextoDatos.appendChild(labelNombre);
        divTextoDatos.appendChild(labelCorreo);
        divTextoDatos.appendChild(labelContraseña);
        contenedor.appendChild(imgIcono);
        contenedor.appendChild(labelEstado);
        divTextoDatos.appendChild(contenedor);
        divTextoDatos.appendChild(divFecha);
        divDatos.appendChild(imagenDatos);
        divDatos.appendChild(divTextoDatos);
        divButtons.appendChild(botonModificar);
        divButtons.appendChild(botonEliminar);
        divButtons.appendChild(botonAlta);
        divUsuario.appendChild(divDatos);
        divUsuario.appendChild(divButtons);

        divGenerico.appendChild(divUsuario);
    });
}

/**
 * @brief Renderizamos los clientes y asignamos funciones al divPadre tanto al hacer click izquierdo que mostraria la pagina de detalle de ese cliente, como 
 * click derecho que nos mostrara un menu contextual con la opcion eliminar que lanzaria la funcion de eliminar al cliente.
 * @fecha 16/02/2026
 * @returns html
 */

function renderizarClientes(users) {
    const divGenerico = document.getElementById('divPadreCarpetas');
    divGenerico.innerHTML = ''; // Clear existing content

    console.log('Rendering users:', users); // Debug: Check the users data
    users.forEach(user => {

        const divPadre = document.createElement("button")
        divPadre.className = "d-flex flex-column align-items-center border-0 btn"
        divPadre.id = "usuario-" + user.id;
        divPadre.dataset.idUsuario = user.id;

        const divUsuario = document.createElement("div");
        divUsuario.className = "d-flex  flex-wrap align-items-center justify-content-center rounded  shadow p-2 m-3 bg-primary";
        divUsuario.id = "usuario-" + user.id;
        divUsuario.dataset.idUsuario = user.id;

        const logo = document.createElement("img")
        logo.className = "w-100 h-100 p-3  "
        logo.src =  'assets/images/file-certificate.svg';
        logo.dataset.idUsuario = user.id

        const nombreCliente = document.createElement("label")
        nombreCliente.className = "text-wrap fs-5"
        nombreCliente.textContent = user.nombre


        divPadre.addEventListener('click', (e)=>{
            e.preventDefault()

    
            $.ajax({
                url:  'index.php',
                method: 'POST',
                dataType: 'json',
                data: {
                    accion:'detalleCliente',
                    idCliente : user.id
                },
                success: function(res) {
                    $('#estaticos').html(res.estaticos);
                    $('#contenido').html(res.contenido);

                    const cliente = Array.isArray(res.cliente) ? res.cliente[0] : res.cliente;
                    const nombreClienteDetalle = document.getElementById('nombreClienteDetalle');
                    nombreClienteDetalle.dataset.idUsuario = cliente.id
                    const telefonoClienteDetalle = document.getElementById('telefonoClienteDetalle');
                    const idClienteActual = document.getElementById('nombreClienteDetalle')?.dataset.idUsuario
                    renderizarArchivosClientes(idClienteActual)
                    if (cliente && nombreClienteDetalle && telefonoClienteDetalle) {
                        nombreClienteDetalle.textContent = "Cliente: " + cliente.nombre ?? '';
                        telefonoClienteDetalle.textContent = "Telefono: " + cliente.telefono ?? '';
                    }

                },error: function(xhr, status, error) {
                    console.error('Error al cargar la vista fuente', error, status, xhr);
                    
                }
            })
        })

        divPadre.addEventListener('contextmenu', (e)=>{
            e.preventDefault();

             // Eliminar menú anterior si existe
            const menuExistente = document.getElementById("menu-contextual");
            if (menuExistente) {
                menuExistente.remove();
            }

            // Crear contenedor del menú
            const menu = document.createElement("ul");
            menu.id = "menu-contextual";
            menu.className = "list-group position-absolute";
            menu.style.top = `${e.pageY}px`;
            menu.style.left = `${e.pageX}px`;
            menu.style.zIndex = "1000";
            menu.style.width = "150px";

            // Opciones del menú
            const opciones = [
                { texto: "Eliminar", accion: () => eliminarCliente(user.id) }
            ];

            opciones.forEach(op => {
                const item = document.createElement("li");
                item.className = "list-group-item list-group-item-action cursor-none";
                item.textContent = op.texto;

                item.addEventListener("click", () => {
                    op.accion();
                    menu.remove();
                });

                menu.appendChild(item);
            });

            document.body.appendChild(menu);

            // Cerrar menú si se hace click fuera
            document.addEventListener("click", () => {
                menu.remove();
            }, { once: true });

        })
        divUsuario.appendChild(logo)
        
        divPadre.appendChild(divUsuario)
        divPadre.appendChild(nombreCliente)

        divGenerico.appendChild(divPadre)
        
        
    });
}

/**
 * @brief EliminarCliente permite eliminar un cliente (deleted = 1) a traves de su id. A su vez elimina el div que representa a dicho cliente.
 * @param {*} userId 
 * @fecha 18/02/2026
 */

function eliminarCliente(userId){
     $.ajax({
        url: 'index.php',
        method: 'POST',
        dataType: 'json',
        data: {
            accion:'eliminarCliente',
            idCliente: userId
        },
        success: function(res) {
            console.log(res.ok)
            if(res.ok > 0){
            
                // Eliminar el div del usuario de la lista
                const divUsuarioAEliminar = document.getElementById(`usuario-${userId}`);
                if (divUsuarioAEliminar) {
                    divUsuarioAEliminar.remove();
                }

            } else {
                alert(res.msg || 'Ha habido un error al eliminar el cliente.');
                console.log(res.msg)
            }
        },
        error: function(xhr, status, error) {
            console.error('Error al eliminar usuario:', error, status, xhr);
        }
    })
}




function modificarEstadoUsuario(estado, id){
    $.ajax({
                url:  'index.php',
                method: 'POST',
                dataType: 'json',
                data: {
                    accion:'modificarEstadoUsuario',
                    idUsuario: id,
                    nuevoEstado: estado
                },
                error: function(xhr, status, error) {
                    console.error('Error al cargar la vista perfil', error, status, xhr);
                    
                }
            })
           
}

