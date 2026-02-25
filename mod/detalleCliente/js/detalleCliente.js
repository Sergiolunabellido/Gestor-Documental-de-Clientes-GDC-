/**
 * @brief Esta funcion crea una promesa por cada ejecucion de esta misma haciendo que la aplicacion pueda seguir funcionando mientras que la accion se termina de realizar.
 * Esta lanza una peticion al backend con la accion subirArchivo que creara un nuevo registro en la tabla archivo y subira el archivo original a la carpeta con nombre del cliente.
 * Segun la respuesta se devuelve una cosa u otra, si la respuesta es satisfactoria el el resolve devolvera el resultado de la peticion, y si es erronea reject devolvera el error.
 * @param {*} file 
 * @param {*} idCliente 
 * @returns array
 * @fecha 18/02/2026
 */
function subirArchivoCliente(file, idCliente) {
    return new Promise((resolve, reject) => {
        const fd = new FormData();
        fd.append('accion', 'subirArchivo');
        fd.append('idCliente', idCliente);
        fd.append('archivoCliente', file);

        $.ajax({
            url: 'index.php',
            method: 'POST',
            dataType: 'json',
            processData: false,
            contentType: false,
            data: fd,
            success: function(res) {
                if (res.ok === true) {
                    resolve(res);
                } else {
                    reject(res);
                }
            },
            error: function(xhr, status, error) {
                console.error('Error al subir archivo', error, status, xhr);
                reject({ msg: 'Error de red al subir archivo' });
            }
        });
    });
}

/**
 * @brief CrearArchivo se encarga de recoger el archivo seleccionado en el input y el id del cliente con el que se interactua, y lanza la funcion subirArchivo pasandole los datos por 
 * parametro. Al devolver una promesa se utiliza el .then para realizar una accion cuando esta ya haya terminado, en este caso renderiza de nuevo los archivos de este cliente para 
 * mostrarlos actualizados.
 * @returns 
 * @fecha 18/02/2026
 */
function crearArchivo() {
    const archivoInput = document.getElementById('inputArchivo');
    const archivos = Array.from(archivoInput?.files || []);
    const idCliente = document.getElementById('nombreClienteDetalle')?.dataset.idUsuario;

    if (!idCliente) {
        alert('No se encontro el cliente');
        return;
    }

    if (archivos.length === 0) {
        alert('Selecciona uno o varios archivos antes de subir');
        return;
    }

    const subidas = archivos.map((file) => subirArchivoCliente(file, idCliente));

    Promise.allSettled(subidas).then((resultados) => {
        const okItems = resultados.filter((r) => r.status === 'fulfilled');
        const failItems = resultados.filter((r) => r.status === 'rejected');

        const ok = okItems.length;
        const fail = failItems.length;

        renderizarArchivosClientes(idCliente);
        archivoInput.value = '';

        const mensajesError = failItems
            .map((r) => r.reason?.msg || r.reason?.error_code || 'Error al subir archivo')
            .filter(Boolean);

        if (fail === 0) {
            alert(`Se subieron ${ok} archivo(s).`);
        } else {
            alert(`Subidos: ${ok}. Fallidos: ${fail}.\n${mensajesError.join('\n')}`);
        }
    });
}


/**
 * @brief Esta accion de click ejecuta la funcion crearArchivo() al pulsar el botonAñadirArchivo, para dar comienzo a la creacion, subida y renderizacion del archivo subido al cliente
 * @fecha 18/02/2026
 */
$(document).on('change', '#inputArchivo', function () {
    if (this.files && this.files.length > 0) {
        crearArchivo();
    }
});




/**
 * @brief Para poder realizar el drag and drop primero tenemos que indicarle al div donde se van a arrastras estos archivos que lo permita, para esto a este div le indicamos
 * tanto que es la zona donde se va a poder ejecutar el arrastrar y que escuche mientras esta accion sucede sobre el, y tambien le indiciamos que no suba esto a los padres del div 
 * con e.stopPropagation();
 * @fecha 18/02/2026
 */

$(document).on('dragenter dragover', '#ficherosCliente', function(e) {
    e.preventDefault();
    e.stopPropagation();
  
    const divArchivos = document.getElementById('ficherosCliente')

    divArchivos.classList.add('drag-active');
});

$(document).on('dragleave', '#ficherosCliente', function(e) {
    e.preventDefault();
    e.stopPropagation();
   
        const divArchivos = document.getElementById('ficherosCliente');
        divArchivos.classList.remove('drag-active');

});


/**
 * @brief Para realizar el drop de los archivos volvemos a indicar al div que pueda hacerlo y coger los archivos que se suelten en este.
 * Cuando esto sucede lanzamos tambien tantas funciones subirArchivoCliente como archivos hayan subido para esto:
 * 1- Cogemos los archivos arrastrados con: e.originalEver?.dataTransfer?.files; basicamente cogemos el evento original del documento/div y con dataTransfer.files los archivos transferidos.
 * 2- Comprobamos que la variable files donde se guardan estos, no este vacia.
 * 3- Creamos un array nuevo vcon todas las promesas individuales de cada archivo subido.
 * 4- De todos estas promesas esperamos a que terminen y cuando terminen en const ok guardamos los archivos que se han subido correctamente y en fail los que no se han podido subir.
 * 5- Volvemos a llamar a la funcion que renderiza la lista de archivos de los clientes.
 * 6- Y por ultimo lanzamos unas alertas para indicar al usuario cuantos archivos se han subido y cuantos no.
 * @fecha 18/02/2026
 */
$(document).on('drop', '#ficherosCliente', function(e) {
    e.preventDefault();
    e.stopPropagation();
    const idCliente = document.getElementById('nombreClienteDetalle')?.dataset.idUsuario;
    if (!idCliente) {
        alert('No se encontro el cliente');
        return;
    }

    const files = e.originalEvent?.dataTransfer?.files;
    if (!files || files.length === 0) {
        return;
    }

    const subidas = Array.from(files).map((file) => subirArchivoCliente(file, idCliente));

    Promise.allSettled(subidas).then((resultados) => {
        const okItems = resultados.filter((r) => r.status === 'fulfilled');
        const failItems = resultados.filter((r) => r.status === 'rejected');

        const ok = okItems.length;
        const fail = failItems.length;

        renderizarArchivosClientes(idCliente);

        //
        //Recogemos los valores del mensaje devuelto por el backend y si no contienen nada con filter boolean prevenimos los posibles saltos en blanco, los undefined,etc...
        //
        const mensajesOk = okItems
            .map((r) => r.value?.msg)
            .filter(Boolean);

        const mensajesError = failItems
            .map((r) => r.reason?.msg || r.reason?.error_code || 'Error al subir archivo')
            .filter(Boolean);

        if (fail === 0) {
            alert(`Se subieron ${ok} archivo(s).\n${mensajesOk.join('\n')}`);
        } else {
            alert(
                `Subidos: ${ok}. Fallidos: ${fail}.\n` +
                `${mensajesError.join('\n')}`
            );
        }
    });

    const divArchivos = document.getElementById('ficherosCliente');
    divArchivos.classList.remove('drag-active');

});


/**
 * @brief Esta funcion realiza una peticion al backend para filtrar la tabla de archivo por el id del cliente devolviendo asi todos los registros con ese id.
 * Segun la respuesta se mostraran diferentes mensajes dentro del div, desde que no a encontrado archivos o que no tiene archivos aun hasta mostrar todos los archivos disponibles.
 * Para mostrar los archivos creamos una lista y por cada registro que devuelve la peticion se crea un nuevo li con el nombre del archivo correspondiente. 
 * Para mostrar solo el nombre del archivo necesitamos quitar por decir asi toda la ruta y para ello usamos lo siguiente .split(/[\\/]/).pop().
 * Cada li contiene un enlace con la ruta del archivo para poder mostrarlo o abrirlo.
 * @param {*} cliente 
 * @returns html
 * @fecha 18/02/2026
 */
function renderizarArchivosClientes(cliente) {
    const contenedor = document.getElementById('ficherosCliente');
    if (!contenedor) return;

    const idCliente = (cliente && typeof cliente === 'object') ? cliente.id : cliente;

    if (!idCliente) {
        contenedor.innerHTML = '<p class="m-3">Cliente no valido</p>';
        return;
    }

    $.ajax({
        url: 'index.php',
        method: 'POST',
        dataType: 'json',
        data: {
            accion: 'listarArchivosCliente',
            idCliente: idCliente
        },
        success: function(res) {
            contenedor.innerHTML = '';

            if (!res.ok) {
                contenedor.innerHTML = `<p class="m-3">${res.msg || 'No se pudieron cargar los archivos'}</p>`;
                return;
            }

            const archivos = Array.isArray(res.archivos) ? res.archivos : [];

            if (archivos.length === 0) {
                contenedor.innerHTML = '<p class="m-3">Este cliente todavia no tiene archivos.</p>';
                return;
            }

            const lista = document.createElement('ul');
            lista.className = 'list-group m-3 ';
            lista.id = "listaArchivos"

            archivos.forEach((archivoItem) => {
                const item = document.createElement('li');
                item.className = 'list-group-item d-flex flex-wrap justify-content-between align-items-center';
                item.id = "archivo-" + archivoItem.id

                const divEnlace = document.createElement('div')
                divEnlace.classList = 'd-flex align-items-center m-2'

                const archivo = (archivoItem.ruta || '').split(/[\\/]/).pop() || 'Archivo';
                const enlace = document.createElement('a');
                enlace.href = archivoItem.ruta;
                enlace.textContent = archivo

                const extension = (archivo.split('.').pop() || '').toLowerCase();
                const icon = document.createElement('img')
                icon.src = `themes/icons/${extension}.png`
                icon.alt = extension
                icon.width = 60
                icon.height = 60
                


                const eliminar = document.createElement('button')
                eliminar.classList = 'btn btn-danger'
                eliminar.textContent = "Eliminar"

                eliminar.addEventListener('click', (e) => {
                    const modal = document.getElementById('modalEliminarArchivo');
                    const btnConfirmar = document.getElementById('botonEliminarArchivo');

                    btnConfirmar.dataset.idArchivo = archivoItem.id;
                    btnConfirmar.dataset.idCliente = idCliente;

                    new bootstrap.Modal(modal).show();
                    
                });
               

                divEnlace.appendChild(icon)
                divEnlace.appendChild(enlace)
                
                item.appendChild(divEnlace);
                item.appendChild(eliminar);
                lista.appendChild(item);
                
            });

            contenedor.appendChild(lista);
        },
        error: function(xhr, status, error) {
            console.error('Error al cargar archivos del cliente', error, status, xhr);
            contenedor.innerHTML = '<p class="m-3">Error al cargar los archivos.</p>';
        }
    });
}

/**
 * @brief Esta accion de click permite eliminar el archivo de la lista y de la BD de ese usuario.
 * @fecha 23/02/2026
 * @return html
 */

$(document).on('click', '#botonEliminarArchivo', function (e){
    const idArchivo = this.dataset.idArchivo;
    const idCliente = this.dataset.idCliente;

    $.ajax({
        url: 'index.php',
        method: 'POST',
        dataType: 'json',
        data: {
            accion:'eliminarArchivoCliente',
            idArchivo: idArchivo,
            idCliente: idCliente
        },
        success: function(respuesta) {
            if(respuesta.ok){
                renderizarArchivosClientes(idCliente);
                const modal = bootstrap.Modal.getInstance(document.getElementById('modalEliminarArchivo'));
                modal.hide();
            } else {
                alert(respuesta.msg || 'Ha habido un error al eliminar el archivo.');
                console.log(res.msg)
            }
        },
        error: function(xhr, status, error) {
            console.error('Error al eliminar el archivo:', error, status, xhr);
        }
    })

});

$(document).on('click', '#botonEliminarTodos', function (e){
        const idCliente = document.getElementById('nombreClienteDetalle')?.dataset.idUsuario;

    $.ajax({
        url: 'index.php',
        method: 'POST',
        dataType: 'json',
        data: {
            accion:'eliminarArchivosCliente',
            idCliente: idCliente
        },
        success: function(respuesta) {
            if(respuesta.ok){
                renderizarArchivosClientes(idCliente);
                
            } else {
                alert(respuesta.msg || 'Ha habido un error al eliminar el archivo.');
                console.log(res.msg)
            }
        },
        error: function(xhr, status, error) {
            console.error('Error al eliminar el archivo:', error, status, xhr);
        }
    })

});



