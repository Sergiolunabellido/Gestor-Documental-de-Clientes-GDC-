
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

