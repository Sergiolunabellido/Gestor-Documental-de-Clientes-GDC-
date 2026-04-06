/**
 * @brief Permite subir un archivo .sql y crear la tabla que este contenga dentro de el y poder posterior mente renderizar el contenido de esta.
 * @fecha 24/02/2026
 * @returns alert | html | errores.
 */
function crearFichero() {
    const input = document.getElementById('idFichero');
    const archivo = input?.files?.[0];
    const contenedorError = document.getElementById('errorCrearFichero');
    const mensajeError = document.getElementById('mensajeErrorFichero');
    const btnCrear = $('#botonCrearFichero');

    if (!archivo) {
        contenedorError?.classList.remove('d-none');
        if (mensajeError) mensajeError.textContent = 'Selecciona un archivo .sql';
        return;
    }

    const fd = new FormData();
    fd.append('accion', 'generarTablas');
    fd.append('archivoSQL', archivo);

    const activarCarga = () => {
        const textoOriginal = btnCrear.data('original-text') ?? btnCrear.text();
        btnCrear.data('original-text', textoOriginal);
        btnCrear.prop('disabled', true).html(
            '<span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>Subiendo...'
        );
        input?.setAttribute('disabled', 'disabled');
    };

    const desactivarCarga = () => {
        const textoOriginal = btnCrear.data('original-text') || 'Crear';
        btnCrear.prop('disabled', false).html(textoOriginal);
        input?.removeAttribute('disabled');
    };

    activarCarga();

    $.ajax({
        url: 'index.php',
        method: 'POST',
        dataType: 'json',
        data: fd,
        processData: false,
        contentType: false,
        success: function (res) {
            if (res.ok) {
                const nombreTabla = Array.isArray(res.tablas) ? (res.tablas[0] || '') : (res.tablas || '');
                if (!nombreTabla) {
                    contenedorError?.classList.remove('d-none');
                    if (mensajeError) mensajeError.textContent = 'No se devolvio el nombre de la tabla';
                    return;
                }

                toastr.success(res.msg || 'Tabla creada en BD');

                const modalEl = document.getElementById('modalCrearFichero');
                const modalInstance = modalEl ? bootstrap.Modal.getInstance(modalEl) : null;
                if (modalInstance) {
                    modalInstance.hide();
                }

                if (document.getElementById('divPadreFicheros')) {
                    renderizarFicheros(res.campoTabla, nombreTabla);
                } else {
                    $('#archivo').trigger('click');
                }
            } else {
                contenedorError?.classList.remove('d-none');
                toastr.error(res.msg || 'Error al procesar el archivo');
                if (mensajeError) mensajeError.textContent = res.msg || 'Error al procesar el archivo';
            }
        },
        error: function (xhr, status, error) {
            toastr.error('Error al crear tabla desde fichero', error, status, xhr);
        },
        complete: function () {
            desactivarCarga();
        }
    });
}

$(document).on('click', '#botonAñadirFichero', function (e){
    e.preventDefault();
    // Abrir el modal de modificar usuario
    const modal = document.getElementById('modalCrearFichero');
    new bootstrap.Modal(modal).show();
})

$(document).on('click', '#botonCrearFichero', (e) => {
    e.preventDefault()
    crearFichero()

})

/**
 * @brief Esta accion de click se encarga de eliminar la tabla del fichero.sql que se ha subido.
 * @fecha 06/04/2026
 * @return void
 */

$(document).on('click', '#botonEliminarFichero', (e) => {
    e.preventDefault()
    const modal = document.getElementById('modalEliminarFicheroBD');
    new bootstrap.Modal(modal).show();

})
$(document).on('click', '#botonCancelarEliminacionFichero', (e) => {
    e.preventDefault()
    const modal = document.getElementById('modalEliminarFicheroBD');
    modal ? bootstrap.Modal.getInstance(modal)?.hide() : null;
})



$(document).on('click', '#botonEliminarFicheroBD', (e) => {
    e.preventDefault()
    const nombreTabla = localStorage.getItem('nombreTabla');
    const btnEliminar = $('#botonEliminarFicheroBD');
    const btnCancelar = $('#botonCancelarEliminacionFichero');
    const modal = document.getElementById('modalEliminarFicheroBD');
    console.log('Nombre de la tabla a eliminar:', nombreTabla);

    if (!nombreTabla) {
        toastr.error('No se encontró el nombre de la tabla a eliminar');
        return;
    }


    const activarCarga = () => {
        const textoOriginal = btnEliminar.data('original-text') ?? btnEliminar.text();
        btnEliminar.data('original-text', textoOriginal);
        btnEliminar.prop('disabled', true).html(
            '<span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>Eliminando...'
        );
        btnCancelar.prop('disabled', true);
    };

    const desactivarCarga = () => {
        const textoOriginal = btnEliminar.data('original-text') || 'Eliminar';
        btnEliminar.prop('disabled', false).html(textoOriginal);
        btnCancelar.prop('disabled', false);
    };

    activarCarga();

    $.ajax({url: 'index.php',
        method: 'POST',
        dataType: 'json',
        data: {
            accion: 'eliminarTablaBD',
            nombreTabla: nombreTabla
        },
        success: function(res) {
            if (res.ok) {
                toastr.success(res.msg || 'Tabla eliminada correctamente');
                $('#archivo').trigger('click');
                modal ? bootstrap.Modal.getInstance(modal)?.hide() : null;
            } else {
                toastr.error(res.msg || 'Error al eliminar la tabla');
            }
        },
        error: function(xhr, status, error) {
            toastr.error('Error al eliminar la tabla', error, status, xhr);
        },
        complete: () => desactivarCarga()
    })
})

/**
 * @brief Renderiza los ficheros de cada tabla devuelta por el backend, estos hacen referencia a todos los mtable distintos de la tabla original.
 * @param {*} nombreTabla 
 * @fecha 24/02/2026
 * @returns 
 */
function renderizarFicheros(nombreCampo, nombreTabla) {
    localStorage.setItem('nombreTabla', nombreTabla);
    const divGenerico = document.getElementById('divPadreFicheros');
    if (!divGenerico) return;
    const nombreFicheroSubido = document.getElementById('nombreFicheroSubido');
    nombreFicheroSubido.textContent = nombreTabla ? `Archivo: ${nombreTabla}` : 'Archivo';

    const tablaOrigen = Array.isArray(nombreTabla) ? (nombreTabla[0] || '') : (nombreTabla || '');
    const tablas = Array.isArray(nombreCampo)
        ? nombreCampo.filter((tabla) => !!tabla)
        : (nombreCampo ? [nombreCampo] : []);
    divGenerico.innerHTML = '';


    if(tablas.length){
        tablas.forEach((tabla) => {
            const divPadre = document.createElement("button")
            divPadre.type = "button"
            divPadre.className = "d-flex flex-column align-items-center justify-content-start border-0 btn w-75 h-75 p-2"
            divPadre.style.minHeight = "190px"

            const divUsuario = document.createElement("div");
            divUsuario.className = "d-flex align-items-center justify-content-center rounded shadow bg-primary w-100";
            divUsuario.style.height = "120px"

            const logo = document.createElement("img")
            logo.className = "w-100 h-100 p-3"
            logo.src =  'assets/images/file-certificate.svg';

            const nombreCliente = document.createElement("label")
            nombreCliente.className = "fs-6 text-center w-100 mt-2"
            nombreCliente.textContent = tabla
            nombreCliente.style.whiteSpace = "nowrap"
            nombreCliente.style.overflow = "hidden"
            nombreCliente.style.textOverflow = "ellipsis"


            divPadre.addEventListener('click', (e)=>{
                e.preventDefault()
                
                
                const seleccionClasses = ["border", "border-3", "border-secondary"];
                const botones = divGenerico.querySelectorAll("button");
                botones.forEach((btn)=>{
                    btn.classList.remove(...seleccionClasses);
                    btn.classList.add("border-0");
                });

                divPadre.classList.remove("border-0");
                divPadre.classList.add(...seleccionClasses);

                $.ajax({
                    url:  'index.php',
                    method: 'POST',
                    dataType: 'json',
                    data: {
                        accion:'detalleTabla',
                        nombreTabla: tablaOrigen,
                        nombreCampo: tabla
                    },
                    success: function(res) {
                        
                        const detalleTabla = document.getElementById('detalleTabla');
                        const archivos = document.getElementById('archivos');
                        archivos.classList.add('d-none')
                        archivos.classList.remove('d-flex')
                        detalleTabla.classList.add('d-flex')
                        detalleTabla.classList.remove('d-none')

                        renderizarTabla(res.datos, tabla);
                        


                    },error: function(xhr, status, error) {
                        toastr.error('Error al cargar la vista fuente', error, status, xhr);
                        
                    }
                })
            })
            divUsuario.appendChild(logo)
            
            divPadre.appendChild(divUsuario)
            divPadre.appendChild(nombreCliente)

            divGenerico.appendChild(divPadre)
        })

    }
       
        
};



$(document).on('click', '#botonVolverArchivo', (e) => {

    e.preventDefault()

    const detalleTabla = document.getElementById('detalleTabla');
    const archivos = document.getElementById('archivos');
    archivos.classList.remove('d-none')
    archivos.classList.add('d-flex')
    detalleTabla.classList.remove('d-flex')
    detalleTabla.classList.add('d-none')

})


/**
 * @brief Renderiza la tabla completa que se le indique a travez de el nombre.
 * @param {*} datos 
 * @param {*} nombreTabla 
 * @fecha 24/02/2026
 * @returns html
 */
function renderizarTabla(datos, nombreTabla) {

    const contenedor = document.getElementById('datosTabla');
    const tituloTabla = document.getElementById('nombreTabla');

    if (tituloTabla) {
        tituloTabla.textContent =  nombreTabla ? `mtable: ${nombreTabla}` : 'mtable';
    }

    if (!contenedor) return;

    contenedor.innerHTML = '';

    const filas = Array.isArray(datos) ? datos : [];
    if (filas.length === 0) {
        contenedor.innerHTML = '<p class="m-3">La tabla no tiene datos para mostrar.</p>';
        return;
    }

    const columnas = Object.keys(filas[0] || {});

    const divPadreTabla = document.createElement('div');
    divPadreTabla.className = 'table-responsive m-3';

    const tabla = document.createElement('table');
    tabla.className = 'table table-striped table-bordered table-hover align-middle';

    const thead = document.createElement('thead');
    const headRow = document.createElement('tr');

    columnas.forEach((columna) => {
        const th = document.createElement('th');
        th.textContent = columna;
        headRow.appendChild(th);
    });

    thead.appendChild(headRow);
    tabla.appendChild(thead);

    const tbody = document.createElement('tbody');

    filas.forEach((fila) => {
        const tr = document.createElement('tr');

        columnas.forEach((columna) => {
            const td = document.createElement('td');
            const valor = fila[columna];
            td.textContent = valor === null || typeof valor === 'undefined' ? '' : String(valor);
            tr.appendChild(td);
        });

        tbody.appendChild(tr);
    });

    tabla.appendChild(tbody);
    divPadreTabla.appendChild(tabla);
    contenedor.appendChild(divPadreTabla);
}




