/**
 * @brief Renderiza la tabla de archivos del cliente seleccionado.
 * @fecha 04/03/2026
 * @param {Array<Object>} archivos
 * @param {Object|null} cliente
 */
function renderizarTablaArchivosCliente(archivos, cliente = null) {
    const contenedor = document.getElementById('tablaArchivosClientes');
    if (!contenedor) return;

    contenedor.innerHTML = '';

    if (!Array.isArray(archivos) || archivos.length === 0) {
        contenedor.innerHTML = '<p class="m-3">No hay archivos para este cliente.</p>';
        return;
    }

    const wrapper = document.createElement('div');
    wrapper.className = 'table-responsive';

    const tabla = document.createElement('table');
    tabla.className = 'table table-striped table-hover align-middle mb-0';

    const thead = document.createElement('thead');
    const trHead = document.createElement('tr');

    const thCheck = document.createElement('th');
    thCheck.className = 'text-center';
    const checkAll = document.createElement('input');
    checkAll.type = 'checkbox';
    checkAll.id = 'checkAllImportar';
    thCheck.appendChild(checkAll);

    const thNombre = document.createElement('th');
    thNombre.textContent = 'Nombre fichero';

    const thTabla = document.createElement('th');
    thTabla.textContent = 'Tabla';

    const thAccion = document.createElement('th');
    thAccion.textContent = '';

    trHead.appendChild(thCheck);
    trHead.appendChild(thNombre);
    trHead.appendChild(thTabla);
    trHead.appendChild(thAccion);
    thead.appendChild(trHead);

    const tbody = document.createElement('tbody');

    const getCheckboxes = () => Array.from(tbody.querySelectorAll('.check-importar-item'));
    const actualizarMasterCheckbox = () => {
        const checkboxes = getCheckboxes();
        const marcados = checkboxes.filter((cb) => cb.checked).length;
        checkAll.checked = checkboxes.length > 0 && marcados === checkboxes.length;
        checkAll.indeterminate = marcados > 0 && marcados < checkboxes.length;
    };

    archivos.forEach((archivo) => {
        const tr = document.createElement('tr');

        const tdCheck = document.createElement('td');
        tdCheck.className = 'text-center';
        const check = document.createElement('input');
        check.type = 'checkbox';
        check.className = 'check-importar-item';
        check.dataset.idArchivo = archivo.id ?? '';
        tdCheck.appendChild(check);

        const tdNombre = document.createElement('td');
        tdNombre.textContent = archivo.nombre || (archivo.ruta ? String(archivo.ruta).split(/[\\/]/).pop() : 'Sin nombre');

        const tdTabla = document.createElement('td');
        tdTabla.textContent = archivo.tabla || archivo.nombreTabla || archivo.tabla_asociada || '-';

        const tdAccion = document.createElement('td');
        tdAccion.className = 'text-end';
        const boton = document.createElement('button');
        boton.type = 'button';
        boton.className = 'btn btn-sm btn-primary';
        boton.textContent = 'Ver';
        boton.addEventListener('click', () => {
            $.ajax({
                url:  'index.php',
                method: 'POST',
                dataType: 'json',
                data: {
                    accion:'conversorArchivoCSV',
                    rutaCSV: archivo.ruta,
                    idCliente: cliente.id,
                    nombreArchivoCSV: archivo.nombre
                },
                success: function(res) {

                    const conf = res.configuracionGuardada;
                    let columnasRaw = null;
                    if (conf && typeof conf === 'object') {
                        if (conf.columnas !== undefined) {
                            columnasRaw = conf.columnas;
                        } else if (Array.isArray(conf) && conf[0] && typeof conf[0] === 'object') {
                            columnasRaw = conf[0].columnas;
                        }
                    }
                    let columnas = columnasRaw;
                    if (typeof columnasRaw === "string") {
                        try { columnas = JSON.parse(columnasRaw); } catch (e) { columnas = null; }
                    }
                    const clavesColumnas = columnas && typeof columnas === 'object' ? Object.keys(columnas) : [];
                    const valoresColumnas = columnas && typeof columnas === 'object' ? (Object.values ? Object.values(columnas) : clavesColumnas.map((k) => columnas[k])) : [];
                

                    if(conf && conf.ok !== false && clavesColumnas.length > 0){
                        console.log(res.configuracionGuardada)

                        $('#estaticos').html(res.estaticos);
                        $('#contenido').html(res.contenido);
                        const nombreCliente = (cliente && cliente.nombre) ? cliente.nombre : '';
                        let nombreArchivo = archivo.nombre;
                        let nombreTabla = '';
                        if (conf && typeof conf === 'object') {
                            if (conf.tabla) {
                                nombreTabla = conf.tabla;
                            } else if (Array.isArray(conf) && conf[0] && conf[0].tabla) {
                                nombreTabla = conf[0].tabla;
                            }
                        }
                        
                        localStorage.setItem('idCliente', cliente.id)
                        
                        const contenedorExpresion = document.getElementById('contenedorExpresion');
                        contenedorExpresion.innerHTML = ''
                        if (contenedorExpresion) {
                            contenedorExpresion.innerHTML = res.html || '';

                            console.log(columnas);
                            clavesColumnas.forEach((clave) => {
                                añadirFilaExpresionConf(clave);
                            });
                            const contenedorCamposTabla = document.getElementById('divCamposTabla');
                            if (contenedorCamposTabla) {
                                valoresColumnas.forEach((valor) => {
                                    añadirFilaCampoConf(valor);
                                });
                            }
                           
                        }

                        const selectTabla = document.getElementById('tablas');
                        if (selectTabla) {
                            selectTabla.innerHTML = '';
                            res.campoTabla.forEach((campo) =>{
                                const option = document.createElement('option');
                                option.value = campo;
                                option.textContent = campo;
                                selectTabla.appendChild(option);
                            });
                            if (nombreTabla) {
                                selectTabla.value = nombreTabla;
                                if (typeof recogerCamposTabla === 'function') {
                                    recogerCamposTabla(function () {
                                        const filasCampo = document.querySelectorAll('.fila-campo');
                                        for (let i = 0; i < valoresColumnas.length; i++) {
                                            const fila = filasCampo[i];
                                            if (!fila) continue;
                                            const sel = fila.querySelector('select');
                                            if (sel) { sel.value = valoresColumnas[i]; }
                                        }
                                    });
                                }
                            }
                        }

                        $('#nombreCliente').text(nombreCliente);
                        $('#nombreArchivo').text(nombreArchivo);

                    }else{
                        console.log('Mostrando el panel por defecto, configuracionGuardada esta: ', res.configuracionGuardada)

                        $('#estaticos').html(res.estaticos);
                        $('#contenido').html(res.contenido);
                        const nombreCliente = (cliente && cliente.nombre) ? cliente.nombre : '';
                        let nombreArchivo = archivo.nombre;
                        
                        localStorage.setItem('idCliente', cliente.id)
                        
                        const contenedorExpresion = document.getElementById('contenedorExpresion');
                        contenedorExpresion.innerHTML = ''
                        if (contenedorExpresion) {
                            contenedorExpresion.innerHTML = res.html || '';
                        }

                        $('#nombreCliente').text(nombreCliente);
                        $('#nombreArchivo').text(nombreArchivo);

                        const select =  document.getElementById('tablas')
                        

                        res.campoTabla.forEach((campo) =>{

                            const option = document.createElement('option')
                            option.value = campo
                            option.textContent = campo

                            select.appendChild(option)
                        })
                    
                    }
                    

                    

                },error: function(xhr, status, error) {
                    console.error('Error al cargar la vista importar', error, status, xhr);
                    
                }
            })
        });
        tdAccion.appendChild(boton);

        tr.appendChild(tdCheck);
        tr.appendChild(tdNombre);
        tr.appendChild(tdTabla);
        tr.appendChild(tdAccion);
        tbody.appendChild(tr);

        check.addEventListener('change', actualizarMasterCheckbox);
    });

    checkAll.addEventListener('change', function () {
        const activo = this.checked;
        getCheckboxes().forEach((cb) => {
            cb.checked = activo;
        });
        this.indeterminate = false;
    });

    tabla.appendChild(thead);
    tabla.appendChild(tbody);
    wrapper.appendChild(tabla);
    contenedor.appendChild(wrapper);
}

/**
 * @brief Pide al backend los archivos ligados al cliente seleccionado.
 * @fecha 04/03/2026
 * @param {string|number} idCliente
 * @param {Object|null} cliente
 */
function obtenerDatosClientes(idCliente, cliente = null) {
    if (!idCliente) return;

    $.ajax({
        url: 'index.php',
        method: 'POST',
        dataType: 'json',
        data: {
            accion: 'listarArchivosCliente',
            idCliente: idCliente
        },
        success: function (res) {
            if (!res?.ok) {
                renderizarTablaArchivosCliente([], cliente);
                return;
            }
            renderizarTablaArchivosCliente(Array.isArray(res.archivos) ? res.archivos : [], cliente);
        },
        error: function (xhr, status, error) {
            console.error('Error al cargar los archivos del cliente en importar', error, status, xhr);
        }
    });
}

/**
 * @brief Inicializa la pantalla de importar: combo de clientes + tabla.
 * @fecha 04/03/2026
 * @param {Array<Object>} clientes
 */
function inicializarImportar(clientes = []) {
    const select = document.getElementById('listaClientes');
    if (!select) return;

    select.innerHTML = '';

    const placeholder = document.createElement('option');
    placeholder.value = '';
    placeholder.textContent = 'Selecciona un cliente';
    placeholder.selected = true;
    placeholder.disabled = true;
    select.appendChild(placeholder);

    clientes.forEach((cliente) => {
        const option = document.createElement('option');
        option.value = cliente.id;
        option.textContent = cliente.nombre;
        option.dataset.nombreCliente = cliente.nombre;
        select.appendChild(option);
    });

    renderizarTablaArchivosCliente([]);

    $(document).off('change', '#listaClientes').on('change', '#listaClientes', function () {
        const idCliente = this.value;
        const clienteSeleccionado = clientes.find((c) => String(c.id) === String(idCliente)) || null;
        obtenerDatosClientes(idCliente, clienteSeleccionado);
    });
}

window.inicializarImportar = inicializarImportar;
window.obtenerDatosClientes = obtenerDatosClientes;
window.renderizarTablaArchivosCliente = renderizarTablaArchivosCliente;















