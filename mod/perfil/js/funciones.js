/**
 * @brief Realizamos una peticion a controller para poder cambiar la imagen de perfil del trabajador.
 * @fecha 26/01/2026
 * @return string
 */

$(document).on('click', '#botonCambiarImagen', (e)=>{
    e.preventDefault();

    const fileInput = document.getElementById('selectorImagenPerfil');
    const file = fileInput.files[0];
    
    if (!file) {
        console.error('No se ha seleccionado ninguna imagen');
        alert('Por favor, selecciona una imagen primero');
        return;
    }

    const formData = new FormData();
    formData.append('accion', 'cambiarFoto');
    formData.append('selectorImagenPerfil', file);

    $.ajax({
        url: 'index.php',
        method: 'POST',
        data: formData,
        processData: false,
        contentType: false,
        dataType: 'json',
        success: function(res) {
            console.log('=== RESPUESTA COMPLETA ===');
            console.log(res);
            
            if (res.ok) {
                console.log('Imagen cambiada correctamente');
                if (res.foto) {
                    $('img.avatar').attr('src', res.foto);
                    // Si la lista de usuarios está actualmente visible, refrescarla
                    if (document.getElementById('divs-usuarios')) {
                        cargarUsuariosDesdeBackend(); 
                    }
                }
                alert('Imagen cambiada correctamente');
            } else {
                console.error('Error:', res.msg);
                if (res.error_code) {
                    console.error('Código de error:', res.error_code);
                }
                alert('Error: ' + (res.msg || 'Error desconocido'));
            }
        },
        error: function(xhr, status, error) {
            console.error('=== ERROR AJAX ===');
            console.error('Status:', status);
            console.error('Error:', error);
            console.error('Response:', xhr.responseText);
            alert('Error al cambiar la imagen');
        }
    })

})
