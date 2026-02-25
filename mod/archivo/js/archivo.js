function crearFichero() {
    const input = document.getElementById('idFichero');
    const archivo = input?.files?.[0];
    const contenedorError = document.getElementById('errorCrearFichero');
    const mensajeError = document.getElementById('mensajeErrorFichero');

    if (!archivo) {
        contenedorError?.classList.remove('d-none');
        if (mensajeError) mensajeError.textContent = 'Selecciona un archivo .sql';
        return;
    }

    const fd = new FormData();
    fd.append('accion', 'generarTablas');
    fd.append('archivoSQL', archivo); 

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

                alert(res.msg || 'Tabla creada en BD');

                const modalEl = document.getElementById('modalCrearFichero');
                const modalInstance = modalEl ? bootstrap.Modal.getInstance(modalEl) : null;
                if (modalInstance) {
                    modalInstance.hide();
                }

                // Si la vista Archivo ya está pintada, renderizamos al instante.
                // Si no está, recargamos la vista Archivo y su click handler la renderizará.
                if (document.getElementById('divPadreFicheros')) {
                    renderizarFicheros(nombreTabla);
                } else {
                    $('#archivo').trigger('click');
                }
            } else {
                contenedorError?.classList.remove('d-none');
                if (mensajeError) mensajeError.textContent = res.msg || 'Error al procesar el archivo';
            }
        },
        error: function (xhr, status, error) {
            console.error('Error al crear tabla desde fichero', error, status, xhr);
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


function renderizarFicheros(nombreTabla) {
    const divGenerico = document.getElementById('divPadreFicheros');
    if (!divGenerico) return;

    const tabla = Array.isArray(nombreTabla) ? (nombreTabla[0] || '') : (nombreTabla || '');
    divGenerico.innerHTML = ''; // Clear existing content

    console.log('Rendering nombreTabla:', tabla); // Debug: Check the nombreTabla data

    if(tabla){

        const divPadre = document.createElement("button")
        divPadre.className = "d-flex flex-column align-items-center border-0 btn"

        const divUsuario = document.createElement("div");
        divUsuario.className = "d-flex  flex-wrap align-items-center justify-content-center rounded  shadow p-2 m-3 bg-primary";

        const logo = document.createElement("img")
        logo.className = "w-100 h-100 p-3  "
        logo.src =  'assets/images/file-certificate.svg';

        const nombreCliente = document.createElement("label")
        nombreCliente.className = "text-wrap fs-5"
        nombreCliente.textContent = tabla


        divPadre.addEventListener('click', (e)=>{
            e.preventDefault()

    
            $.ajax({
                url:  'index.php',
                method: 'POST',
                dataType: 'json',
                data: {
                    accion:'detalleTabla',
                    nombreTabla: tabla
                },
                success: function(res) {
                    $('#estaticos').html(res.estaticos);
                    $('#contenido').html(res.contenido);
                    renderizarTabla(res.datos, tabla);

                },error: function(xhr, status, error) {
                    console.error('Error al cargar la vista fuente', error, status, xhr);
                    
                }
            })
        })
        divUsuario.appendChild(logo)
        
        divPadre.appendChild(divUsuario)
        divPadre.appendChild(nombreCliente)

        divGenerico.appendChild(divPadre)
        

    }
       
        
};

function renderizarTabla(datos, nombreTabla) {
    const contenedor = document.getElementById('datosTabla');
    const tituloTabla = document.getElementById('nombreTabla');

    if (tituloTabla) {
        tituloTabla.textContent = nombreTabla ? `Tabla: ${nombreTabla}` : 'Tabla';
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

