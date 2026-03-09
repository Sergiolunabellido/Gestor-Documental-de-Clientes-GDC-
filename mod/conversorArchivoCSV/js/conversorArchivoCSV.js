
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
 $(document).on('change','#tablas', ()=>{
    recogerCamposTabla()
 })

$(document).on('change', '.camposTabla', function(e) {
    const idSelect = this.id;
    const valorSeleccionado = this.value;
    console.log('Select cambiado:', idSelect, 'Valor:', valorSeleccionado);

})


    
