/**
 * @brief Obtiene los valores de las filas con checkbox seleccionado
 * @fecha 16/03/2026
 * @returns {Array} Array de objetos con los datos de las filas seleccionadas
 */
function obtenerFilasSeleccionadas() {
    const filasSeleccionadas = [];
    const checkboxes = document.querySelectorAll('.check-importar-item:checked');

    //De las filas con el input en checked se cogen los nombres de los archivos y la tabla, aparte de eso se transforma el nombre de .csv a .json 
    //para poder entrar a la configuracion guardada
    //por el usuario que contiene la estructura de los campos del csv y los de la tabla .
    checkboxes.forEach(checkbox => {
        const fila = checkbox.closest('tr');
        const celdas = fila.querySelectorAll('td');

        filasSeleccionadas.push({
            idArchivo: checkbox.dataset.idArchivo,
            nombre: celdas[1]?.textContent.trim() || '',
            nombreJson: celdas[1]?.textContent.trim().replace(/\.csv$/i, '.json'),
            tabla: celdas[2]?.textContent.trim() || '-'
        });
    });

    return filasSeleccionadas;
}

/**
 * @brief Renderiza la tabla de archivos del cliente seleccionado.
 * @fecha 04/03/2026
 * @param {Array<Object>} archivos
 * @param {Object|null} cliente
 */
function renderizarTablaArchivosCliente(archivos, cliente = null) {
    clienteActual = cliente;
    const contenedor = document.getElementById('tablaArchivosClientes');
    const divImportar = document.getElementById('pantallaImportar');
    const divConversor = document.getElementById('conversorArchivo');

    if (!contenedor){ 
        toastr.error('No se a encontrado el contenedor de la tabla de archivos.')
        return
    };

    contenedor.innerHTML = '';

    if (!Array.isArray(archivos) || archivos.length === 0) {
        toastr.info('No hay archivos para este cliente.');
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
        tdNombre.id = archivo.nombre

        const tdTabla = document.createElement('td');
        tdTabla.textContent = archivo.tabla || archivo.nombreTabla || archivo.tabla_asociada || '-';
        tdTabla.id = archivo.tabla

        const tdAccion = document.createElement('td');
        tdAccion.className = 'text-end';
        const boton = document.createElement('button');
        boton.type = 'button';
        boton.className = 'btn btn-sm btn-primary';
        boton.textContent = 'Ver';
        /**
         * @brief Esta accion de click mostrara la pagina de configuracion de tabla con los datos de el archivo .csv en el que se haya clicado este boton.
         * @fecha 10/03/2026
         * @return html
         */
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


                    //Comprobamos el contenido de configuracionGuardada que podra guardar el contenido de esta pagina en un .json anteriormente guardado por el usuario.
                    //Si no contiene informacion se mostrara la pagina "predeterminada", si no cargara los campos guardados dentro de este archivo asi como los inputs y los selects anteriores.
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
                

                    //Se muestra la pagina con el contenido del .json cargado
                    if(conf && conf.ok !== false && clavesColumnas.length > 0){
                        console.log(res.configuracionGuardada)

                        divImportar.classList.remove('d-flex');
                        divImportar.classList.add('d-none');

                        divConversor.classList.remove('d-none');
                        divConversor.classList.add('d-flex');

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
                            const filaExpInicial = contenedorExpresion.querySelector('.fila-expresion input');
                            clavesColumnas.forEach((clave, idx) => {
                                if (idx === 0 && filaExpInicial) {
                                    filaExpInicial.value = clave;
                                } else {
                                    añadirFilaExpresionConf(clave);
                                }
                            });
                            const contenedorCamposTabla = document.getElementById('divCamposTabla');
                            
                            if (contenedorCamposTabla) {
                                
                                const filaBase = contenedorCamposTabla.querySelector('.fila-campo');
                                contenedorCamposTabla.innerHTML = '';
                                if (filaBase) {
                                    const baseClon = filaBase.cloneNode(true);
                                    const selBase = baseClon.querySelector('select');
                                    if (selBase) {
                                        selBase.value = '';
                                        delete selBase.dataset.confInit;
                                    }
                                    contenedorCamposTabla.appendChild(baseClon);
                                }
                                contadorFilas = 1;

                                valoresColumnas.forEach((valor) => {
                                    añadirFilaCampoConf(valor);
                                });
                            }
                           
                        }

                        const selectTabla = document.getElementById('tablas');
                        selectTabla.innerHTML = '';
                        if (selectTabla) {
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

                    //Se muestra la pagina "predeterminada"
                    }else{
                        console.log('Mostrando el panel por defecto, configuracionGuardada esta: ', res.configuracionGuardada)

                        divImportar.classList.remove('d-flex');
                        divImportar.classList.add('d-none');

                        divConversor.classList.remove('d-none');
                        divConversor.classList.add('d-flex');
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
                        select.innerHTML = ''

                        const selectTabla = document.getElementById('tablas');
                        selectTabla.innerHTML = '';
                        res.campoTabla.forEach((campo) =>{

                            const option = document.createElement('option')
                            option.value = campo
                            option.textContent = campo

                            select.appendChild(option)
                        })

                    }


                    

                },error: function(xhr, status, error) {
                    toastr.error('Error al cargar la vista importar', error, status, xhr);
                    
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
            toastr.error('Error al cargar los archivos del cliente en importar', error, status, xhr);
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
        clienteActual = clienteSeleccionado;
        obtenerDatosClientes(idCliente, clienteSeleccionado);
    });
}


/**
 * @brief Esta funcion de click realiza una peticion al backend para exportar los archivos y la configuracion de la tabla diseÃƒÂ±ada por el cliente, pasandole la lista de las
 * filas de la tabla que estan seleccionadas pasandole el nombre del archivo y el nombre de la tabla o de la configuracion creada, junto al id del cliente y el nombre de la tabla o bd
 * de destino mas el prefijo que esta llevara.
 * @fecha 16/03/2026
 * @returns alerts
 */
$(document).on('click', '#exportarFicherosCliente', (e) => {
    e.preventDefault();
    const barraProgresiva = document.getElementById('barraProgresiva');
    const barraProgresivaBar = document.getElementById('barraProgresivaBar');

    const resetProgress = () => {
        if (!barraProgresiva || !barraProgresivaBar) return;
        barraProgresivaBar.style.width = '0%';
        barraProgresivaBar.setAttribute('aria-valuenow', '0');
        barraProgresivaBar.textContent = '';
    };

    const setProgress = (value) => {
        if (!barraProgresiva || !barraProgresivaBar) return;
        const pct = Math.min(100, Math.max(0, Math.round(value)));
        requestAnimationFrame(() => {
            barraProgresivaBar.style.width = `${pct}%`;
            barraProgresivaBar.setAttribute('aria-valuenow', String(pct));
        });
    };

    const setFilas = (procesadas, total) => {
        if (!barraProgresivaBar) return;
        const p = Number.isFinite(Number(procesadas)) ? Number(procesadas) : 0;
        const t = Number.isFinite(Number(total)) ? Number(total) : 0;
        if (t > 0) {
            barraProgresivaBar.textContent = `${p}/${t} filas`;
        } else {
            barraProgresivaBar.textContent = `${p} filas`;
        }
    };

    resetProgress();
    $.get('/practicas2026/app/aplicacionweb/mod/importar/includes/progreso.php?reset=1');
    setFilas(0, 0);

    if (barraProgresiva) barraProgresiva.classList.remove('visually-hidden');

    const boton = document.getElementById('exportarFicherosCliente');
    const textoOriginal = boton ? boton.textContent : '';
    if (boton) boton.textContent = 'Procesando...';

    const filasSeleccionadas = obtenerFilasSeleccionadas();
    
    if (filasSeleccionadas.length === 0) {
        if (boton) boton.textContent = textoOriginal;
        toastr.warning('Selecciona al menos un archivo para exportar');
        if (barraProgresiva) barraProgresiva.classList.add('visually-hidden');
        return;
    }

    const TablaDestino = document.getElementById('dbDestino')?.value || '';
    const prefijoTabla = document.getElementById('prefijoTabla')?.value || '';

    let monitorProgreso;

    $.ajax({
        url: 'index.php',
        method: 'POST',
        dataType: 'json',
        timeout: 0,
        data: {
            accion: 'exportarArchivosCliente',
            archivos: filasSeleccionadas,
            bdDestino: TablaDestino,
            prefijo: prefijoTabla,
            idCliente: clienteActual?.id || null
        },
        beforeSend: function() {
            monitorProgreso = setInterval(() => {
                $.ajax({
                    url: '/practicas2026/app/aplicacionweb/mod/importar/includes/progreso.php',
                    method: 'GET',
                    dataType: 'json',
                    cache: false,
                    success: function(res) {
                        if (typeof res?.porcentaje !== 'undefined') {
                            setProgress(res.porcentaje);
                        }
                        if (res?.filas) {
                            setFilas(res.filas.procesadas, res.filas.total);
                        }
                    }
                });
            }, 200);
        },
        success: function(res) {
            clearInterval(monitorProgreso);
            setProgress(100);
            if (res?.filas) {
                setFilas(res.filas.procesadas, res.filas.total);
            }
            setTimeout(() => {
                if (barraProgresiva) barraProgresiva.classList.add('visually-hidden');

                if (res.ok) {
                    toastr.success('Exportación completada');
                } else {
                    toastr.error(res.msg || 'Error al exportar');
                }

                resetProgress();
            }, 600);
        },
        error: function() {
            clearInterval(monitorProgreso);
            toastr.error('Error en el servidor al exportar');
            if (barraProgresiva) barraProgresiva.classList.add('visually-hidden');
            resetProgress();
        },
        complete: function() {
            if (boton) boton.textContent = textoOriginal;
        }
    });
});

//Se crea una variable para poder guardar en esta el cliente que se a seleccionado en el select del front.
let clienteActual = null;

//Inicializamos las funciones
window.inicializarImportar = inicializarImportar;
window.obtenerDatosClientes = obtenerDatosClientes;
window.renderizarTablaArchivosCliente = renderizarTablaArchivosCliente;
window.obtenerFilasSeleccionadas = obtenerFilasSeleccionadas;


//Contenido conversorArchivoCSV.

$(document).on('click', '#botonVolver', (e) => {
    e.preventDefault()
    const divImportar = document.getElementById('pantallaImportar');
    const divConversor = document.getElementById('conversorArchivo');
    divImportar.classList.add('d-flex');
    divImportar.classList.remove('d-none');

    divConversor.classList.add('d-none');
    divConversor.classList.remove('d-flex');

    // Al volver, recarga la tabla del cliente actual sin tocar el select
    if (clienteActual && clienteActual.id && typeof obtenerDatosClientes === 'function') {
        obtenerDatosClientes(clienteActual.id, clienteActual);
    }
})

let contadorFilas = 1;
let contadorExpresiones = 1;

function recogerCamposTabla(callback){
    const tabla = document.getElementById('tablas').value

    $.ajax({
        url:  'index.php',
        method: 'POST',
        dataType: 'json',
        data: {
            accion:'tiposTablas',
            nombreTabla: tabla
        },
        success: function(res) {
            console.log(res.tipos)

            const selects =  document.querySelectorAll('.camposTabla')
            const tipos = Array.isArray(res.tipos) ? res.tipos : [res.tipos]
            const tiposV = Array.isArray(res.tiposV) ? res.tiposV : [res.tiposV]

            selects.forEach((select) => {
                const valorActual = select.value
                select.innerHTML = ''

                tipos.forEach((tipo, index) =>{
                    const option = document.createElement('option')
                    const tipoValor = tiposV[index] || ''

                    option.value = tipo
                    option.textContent = `${tipo}: ${tipoValor}`

                    if (tipo === valorActual) {
                        option.selected = true
                    }

                    select.appendChild(option)
                })
            })

            if (typeof callback === 'function') { callback(); }

        },error: function(xhr, status, error) {
            toastr.error('Error al cargar la vista importar', error, status, xhr);

        }
    })
}

function añadirFilaCampo() {
    contadorFilas++;
    const contenedor = document.getElementById('divCamposTabla');
    const ultimaFila = contenedor.querySelector('.fila-campo:last-child');
    const nuevaFila = ultimaFila.cloneNode(true);

    nuevaFila.dataset.id = contadorFilas;
    const nuevoSelect = nuevaFila.querySelector('select');
    nuevoSelect.id = 'camposTabla_' + contadorFilas;
    nuevoSelect.value = '';

    contenedor.appendChild(nuevaFila);
}

function añadirFilaCampoConf(valorSelect) {
    const contenedor = document.getElementById('divCamposTabla');
    if (!contenedor) return;

    const filas = contenedor.querySelectorAll('.fila-campo');
    if (filas.length === 0) return;

    const filaBase = filas[0];
    const selectBase = filaBase.querySelector('select');

    if (filas.length === 1 && selectBase && !selectBase.dataset.confInit) {
        selectBase.value = valorSelect;
        selectBase.dataset.confInit = '1';
        return;
    }

    contadorFilas++;
    const nuevaFila = filaBase.cloneNode(true);
    nuevaFila.dataset.id = contadorFilas;

    const nuevoSelect = nuevaFila.querySelector('select');
    if (nuevoSelect) {
        nuevoSelect.id = 'camposTabla_' + contadorFilas;
        nuevoSelect.value = valorSelect;
    }

    contenedor.appendChild(nuevaFila);
}

function eliminarFilaCampo(boton) {
    const contenedor = document.getElementById('divCamposTabla');
    const filas = contenedor.querySelectorAll('.fila-campo');

    if (filas.length > 1) {
        const fila = boton.closest('.fila-campo');
        const index = Array.from(filas).indexOf(fila);
        fila.remove();
        toastr.info('Se a eliminado un campo de la lista')

        // Eliminar la fila de expresión correspondiente por índice
        const contenedorExp = document.getElementById('contenedorExpresion');
        if (contenedorExp) {
            const filasExp = contenedorExp.querySelectorAll('.fila-expresion');
            if (filasExp.length > index) {
                filasExp[index].remove();
                
            }
        }
    } else {
        toastr.error('Debe haber al menos un campo');
    }
}

/**
 * @brief Esta funcion permite añadir un nuevo input para seguir añadiendo expresiones con el contenido del CSV en la tabla de la BD, clonando el input existente.
 * @param {*} boton 
 * @returns 
 */



function añadirFilaExpresionConf(valorInput) {
    const contenedor = document.getElementById('contenedorExpresion');
    if (!contenedor) return;

    const filas = contenedor.querySelectorAll('.fila-expresion');
    if (filas.length === 0) {
        contadorExpresiones++;
        const nuevaFila = document.createElement('div');
        nuevaFila.className = 'fila-expresion';
        nuevaFila.dataset.id = contadorExpresiones;
        nuevaFila.innerHTML = `
            <input type="text" id="expresion_${contadorExpresiones}" class="form-control" placeholder="Expresión" value="${valorInput}">
        `;
        contenedor.appendChild(nuevaFila);
        return;
    }

    const filaBase = filas[0];

    contadorExpresiones++;
    const nuevaFila = filaBase.cloneNode(true);
    nuevaFila.dataset.id = contadorExpresiones;

    const nuevoInput = nuevaFila.querySelector('input');
    if (nuevoInput) {
        nuevoInput.id = 'expresion_' + contadorExpresiones;
        nuevoInput.value = valorInput;
    }

    contenedor.appendChild(nuevaFila);
}

/**
 * @brief Esta funcion permite eliminar un input de la lista de inputs de las expresiones, eliminando el input ligado a ese boton de eliminar ( - ), si intentamos eliminar el ultimo este se limpiara
 * pero no nos permitira eliminarlo ya que al menos a de quedar un input para poder realizar la accion.
 * @param {*} boton 
 * @returns alert | html
 */

function eliminarFilaExpresion(boton){
    contenedorExpresion = contenedorExpresion -1;
    const fila = boton.closest('.fila-expresion');
    if (!fila) return;

    const contenedor = document.getElementById('contenedorExpresion');
    if (!contenedor) return;

    const filas = contenedor.querySelectorAll('.fila-expresion');

    if (filas.length > 1) {
        fila.remove();
    } else {
        const input = document.querySelector('input')
        if(input){
            input.value = ""
             toastr.warning('Debe haber al menos una expresión');
        }
       
        
    }
}

$(document).on('click', '.botonAñadirCampo', function(e) {
    e.preventDefault();
    añadirFilaExpresionConf('');
    añadirFilaCampo(this);
    
});

$(document).on('click', '.botonEliminarCampo', function(e) {
    e.preventDefault();
    eliminarFilaCampo(this);
});


$(document).on('change','#tablas', ()=>{
    recogerCamposTabla()
 })

$(document).on('change', '.camposTabla', function(e) {
    const idSelect = this.id;
    const valorSeleccionado = this.value;
    console.log('Select cambiado:', idSelect, 'Valor:', valorSeleccionado);

})


/**
 * @brief Esta accion de click inicia una peticion que envia al backend un Map con el contenido necesario de la pagina para que al volver a cargar esta se muestren dichos datos, inputs...
 * al guardar el contenido devuelve a la pagina anterior de importar dando por hecho que ya has terminado el trabajo.
 * @fecha 10/03/2026
 * @returns alert | html
 *  */  
$(document).on('click', '#botonGuardar', (e)=>{
    e.preventDefault()

    const divImportar = document.getElementById('pantallaImportar');
    const divConversor = document.getElementById('conversorArchivo');

    //Del contenedor de los inputs creamos un array con el valor de todos estos
    const inputs = document.querySelectorAll('.fila-expresion input')
    const valoresInputs = Array.from(inputs).map(input => input.value)

    //Realizamos la misma operacion que con los inputs pero con el contenido de los selects que referencian los campos de la tabla.
    const filasCampo = document.querySelectorAll('.fila-campo')
    const valoresSelects = Array.from(filasCampo).map(fila => {
        const select = fila.querySelector('select')
        return select ? select.value : ''
    })

    //Recogemos la tabla que estamos usando para referenciar los campos como ejemplo.
    const tablaReferenciada = document.getElementById('tablas').value
    
    //Recogemos el nombre del archivo del cual estamos guardando la configuracion.
    const nombreArchivo = document.getElementById('nombreArchivo').textContent

    //Creamos un Map para guardar clave valor de los inputs y los select que hacen referencia al valor y el nombre de la columna en la cual se guardara el valor del input.
    let valores = new Map();

    //Obtenemos la lonjitud mas corta de los dos arrays para poder realizar el map correctamente sin que se quede ningun campo suelto.
    const longitud = Math.min(valoresInputs.length, valoresSelects.length)

    //Creamos un bucle y rellenamos el map con los valores de cada posicion de los arrays.
    for(let i = 0; i < longitud; i++){
            const clave = (valoresInputs[i] || '').trim()
            const valor = (valoresSelects[i] || '').trim()

            if (clave !== '' && valor !== '') {
                valores.set(clave, valor)
            }
    }

    if (valores.size === 0) {
        toastr.warning('Debes rellenar al menos una columna origen y su campo destino.')
        return;
    }

    //Creamos un nuevo Map para guardar todo el contenido y pasarlo al back-end.
    let mapJSON = {
        //Indicamos los valores y claves del map.
        "archivo": nombreArchivo,
        "tabla": tablaReferenciada,
        "columnas": Object.fromEntries( valores)
    }

    

    console.log(mapJSON)

    if(tablaReferenciada === "Selecciona cualquier tabla:"){
        toastr.warning('Si te digo que eligas una tabla es por algo...')
        return;
    }

    $.ajax({
        url:  'index.php',
        method: 'POST',
        dataType: 'json',
        data: {
            accion:'guardarConfCSV',
            configuracionCSV: mapJSON,
            idCliente: localStorage.getItem('idCliente')

        },
        success: function(res) {
            //Si la respuesta es correcta se vuelve a la pagina anterior. Si no se muestra una alerta de error.
           if(res.ok === true){
                
                 $.ajax({
                    url:  'index.php',
                    method: 'POST',
                    dataType: 'json',
                    data: {
                        accion:'importar',
                    },
                    success: function(res) {
                        divImportar.classList.add('d-flex');
                        divImportar.classList.remove('d-none');
                        const clientes = Array.isArray(res.clientes) ? res.clientes : [];
                        const selectClientes = document.getElementById('listaClientes');
                        const idClienteActual = clienteActual?.id || localStorage.getItem('idCliente');

                        if (selectClientes) {
                            selectClientes.innerHTML = '';

                            const optionDefault = document.createElement('option');
                            optionDefault.value = '';
                            optionDefault.textContent = 'Selecciona un cliente';
                            optionDefault.selected = true;
                            optionDefault.disabled = true;
                            selectClientes.appendChild(optionDefault);

                            clientes.forEach((cliente) => {
                                const option = document.createElement('option');
                                option.value = cliente.id;
                                option.textContent = cliente.nombre;
                                selectClientes.appendChild(option);
                            });

                            // Re-selecciona el cliente activo antes de guardar
                            if (idClienteActual) {
                                selectClientes.value = idClienteActual;
                            }
                        }

                        $(document).off('change', '#listaClientes').on('change', '#listaClientes', function () {
                            const idCliente = this.value;
                            const clienteSeleccionado = clientes.find((c) => String(c.id) === String(idCliente)) || null;

                            if (typeof window.obtenerDatosClientes === 'function') {
                                window.obtenerDatosClientes(idCliente, clienteSeleccionado);
                            }
                        });

                        // Si habia cliente seleccionado, recarga sus archivos sin limpiar la seleccion
                        if (idClienteActual && typeof window.obtenerDatosClientes === 'function') {
                            const clienteSeleccionado = clientes.find((c) => String(c.id) === String(idClienteActual)) || null;
                            window.obtenerDatosClientes(idClienteActual, clienteSeleccionado);
                        } else if (typeof window.renderizarTablaArchivosCliente === 'function') {
                            window.renderizarTablaArchivosCliente([]);
                        }
                        
                        const idCliente = this.value;
                        const clienteSeleccionado = clientes.find((c) => String(c.id) === String(idCliente)) || null;

                        if (typeof window.obtenerDatosClientes === 'function') {
                            window.obtenerDatosClientes(idCliente, clienteSeleccionado);
                        }
                        
                        if (typeof window.renderizarTablaArchivosCliente === 'function') {
                            window.renderizarTablaArchivosCliente([]);
                        }

                    },error: function(xhr, status, error) {
                        toastr.error('Error al cargar la vista importar', error, status, xhr);
                        
                    }
                })
                    
                toastr.success('El contenido de configuracion se ha guardado exitosamente.')
           }else{
            toastr.error(res.msg || 'No se a podido guardar la configuracion de este archivo.')
           }

        },error: function(xhr, status, error) {
            toastr.error('Error al guardar la configuracion de este archivo', error, status, xhr);

        }
    })
})







