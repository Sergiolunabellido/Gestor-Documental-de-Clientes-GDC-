
$(document).on('click', '#botonVolver', (e) => {

    e.preventDefault()
    const tabla = localStorage.getItem('nombreTabla') || '';

    $.ajax({
        url:  'index.php',
        method: 'POST',
        dataType: 'json',
        data: {
            accion:'importar',
        },
        success: function(res) {
            $('#estaticos').html(res.estaticos);
            $('#contenido').html(res.contenido);

            const clientes = Array.isArray(res.clientes) ? res.clientes : [];
            const selectClientes = document.getElementById('listaClientes');

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
            }

            $(document).off('change', '#listaClientes').on('change', '#listaClientes', function () {
                const idCliente = this.value;
                const clienteSeleccionado = clientes.find((c) => String(c.id) === String(idCliente)) || null;

                if (typeof window.obtenerDatosClientes === 'function') {
                    window.obtenerDatosClientes(idCliente, clienteSeleccionado);
                }
            });

            if (typeof window.renderizarTablaArchivosCliente === 'function') {
                window.renderizarTablaArchivosCliente([]);
            }

        },error: function(xhr, status, error) {
            console.error('Error al cargar la vista importar', error, status, xhr);
            
        }
    })

})

let contadorFilas = 1;
let contadorExpresiones = 1;

function recogerCamposTabla(){
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

            selects.forEach((select) => {
                const valorActual = select.value
                select.innerHTML = ''

                tipos.forEach((tipo) =>{
                    const option = document.createElement('option')
                    option.value = tipo
                    option.textContent = tipo

                    if (tipo === valorActual) {
                        option.selected = true
                    }

                    select.appendChild(option)
                })
            })

        },error: function(xhr, status, error) {
            console.error('Error al cargar la vista importar', error, status, xhr);

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

function eliminarFilaCampo(boton) {
    const contenedor = document.getElementById('divCamposTabla');
    const filas = contenedor.querySelectorAll('.fila-campo');

    if (filas.length > 1) {
        const fila = boton.closest('.fila-campo');
        fila.remove();
    } else {
        alert('Debe haber al menos un campo');
    }
}

$(document).on('click', '.botonAñadirCampo', function(e) {
    e.preventDefault();
    añadirFilaCampo();
});

$(document).on('click', '.botonEliminarCampo', function(e) {
    e.preventDefault();
    eliminarFilaCampo(this);
});

/**
 * @brief Esta funcion permite añadir un nuevo input para seguir añadiendo expresiones con el contenido del CSV en la tabla de la BD, clonando el input existente.
 * @param {*} boton 
 * @returns 
 */

function añadirFilaExpresion(boton) {
    contadorExpresiones++;
    const filaActual = boton.closest('.fila-expresion');
    if (!filaActual) return;

    const nuevaFila = filaActual.cloneNode(true);

    nuevaFila.dataset.id = contadorExpresiones;
    const nuevoInput = nuevaFila.querySelector('input');
    nuevoInput.id = 'expresion_' + contadorExpresiones;
    nuevoInput.value = '';

    filaActual.after(nuevaFila);
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

    const contenedor = fila.closest('.expressions.csv-inputs-container');
    if (!contenedor) return;

    const filas = contenedor.querySelectorAll('.fila-expresion');

    if (filas.length > 1) {
        fila.remove();
    } else {
        const input = document.querySelector('input')
        if(input){
            input.value = ""
             alert('Debe haber al menos una expresión');
        }
       
        
    }
}

$(document).on('click', '.botonAñadirExpresion', function(e) {
    e.preventDefault();
    añadirFilaExpresion(this);
});

$(document).on('click', '.botonEliminarExpresion', function(e) {
    e.preventDefault();
    
    eliminarFilaExpresion(this);
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

    const nombreCliente = document.getElementById('nombreCliente').textContent

    //Del contenedor de los inputs creamos un array con el valor de todos estos
    const contenedorInputs = document.querySelector('.expressions.csv-inputs-container')
    const inputs = contenedorInputs.querySelectorAll('.fila-expresion input')
    const valoresInputs = Array.from(inputs).map(input => input.value)

    //Campturamos el id guardado en un parrafo oculto para pasarlo al backend.
    const idObjeto = contenedorInputs.querySelector('p').textContent

    //Realizamos la misma operacion que en el de inputs pero con el contenido de los selects que referencian los campos de la tabla.
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
            valores.set(valoresInputs[i],valoresSelects[i])
    }

    //Creamos un nuevo Map para guardar todo el contenido y pasarlo al back-end.
    let mapJSON = {
        //Indicamos los valores y claves del map.
        "archivo": nombreArchivo,
        "id": idObjeto,
        "tabla": tablaReferenciada,
        "columnas": Object.fromEntries( valores)
    }

    

    console.log(mapJSON)

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
                        $('#estaticos').html(res.estaticos);
                        $('#contenido').html(res.contenido);

                        const clientes = Array.isArray(res.clientes) ? res.clientes : [];
                        const selectClientes = document.getElementById('listaClientes');

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
                        }

                        $(document).off('change', '#listaClientes').on('change', '#listaClientes', function () {
                            const idCliente = this.value;
                            const clienteSeleccionado = clientes.find((c) => String(c.id) === String(idCliente)) || null;

                            if (typeof window.obtenerDatosClientes === 'function') {
                                window.obtenerDatosClientes(idCliente, clienteSeleccionado);
                            }
                        });

                        if (typeof window.renderizarTablaArchivosCliente === 'function') {
                            window.renderizarTablaArchivosCliente([]);
                        }

                    },error: function(xhr, status, error) {
                        console.error('Error al cargar la vista importar', error, status, xhr);
                        
                    }
                })
                alert('El contenido de configuracion se ha guardado exitosamente.')
           }else{
            alert('No se a podido guardar la configuracion de este archivo.')
           }

        },error: function(xhr, status, error) {
            console.error('Error al guardar la configuracion de este archivo', error, status, xhr);

        }
    })


})