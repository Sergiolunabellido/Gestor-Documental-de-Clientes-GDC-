/** 
* @brief Este metodo se encarga de mostrar el div que contiene el mensaje de error 
* que se mostrara cuando las credenciales introducidas por el usuario en el login
* no se encuentren en la base de datos, haciendo saber al usuario que a introducido
* mal los datos o que no esta registrado.
* @fecha 21/01/2026
* @return void
*/

function mostrarError(id) {
    const error = document.getElementById(id);
    if (!error) return;
    error.classList.remove('d-none');
}


/**
 * @brief Este metodo se encara de habilitar y deshabilitar los console.log de proyecto, teniendo asi un modo debug y un modo produccion,
 * dependiendo de la variable de entorno que se le asigne a la variable global DEBUG, si esta variable es true, los console.log estaran 
 * habilitados, si es false, los console.log estaran deshabilitados.
 * @fecha 15/04/2026
 * @return void
 */
let avisos = console.log;
function habilitarDebug(valor = null) {

    if(valor !== null){
         
        if(valor === 'true' || valor === true){
            alert('Modo debug habilitado');
            console.log = avisos;
            return;
        }else{
            console.log = () => {};
            alert('Modo debug deshabilitado');
            return;
        }
    }
    else{
        $.ajax({
            url: 'index.php',
            method: 'POST',
            data: {
                accion : 'modoDebug'
            },
            async: false,
            dataType: 'json',
            success: function(res){
               
                const modoDebug = res.variables.find(variable => variable.nombre === '_MODO_DEBUG_');

                if(modoDebug && modoDebug.valor === 'true'|| modoDebug.valor === true){
                    alert('Modo debug habilitado');
                    console.log = avisos;
                }else{
                    console.log = () => {};
                    alert('Modo debug deshabilitado');
                    
                }
               
                
            }
        })
    }
}

function habilitarDebugNoAdmin(valor = null) {

    if(valor !== null){
         
        if(valor === 'true' || valor === true){
            console.log = avisos;
            return;
        }else{
            console.log = () => {};
            return;
        }
    }
    else{
        $.ajax({
            url: 'index.php',
            method: 'POST',
            data: {
                accion : 'modoDebug'
            },
            async: false,
            dataType: 'json',
            success: function(res){
                const modoDebug = res.variables.find(variable => variable.nombre === '_MODO_DEBUG_');
                if(modoDebug && modoDebug.valor === 'true'|| modoDebug.valor === true){
                    console.log = avisos;
                }else{
                    console.log = () => {};
                }
            }
        })
    }
}

window.addEventListener('load', () => {

    $.ajax({
        url: 'index.php',
        method: 'POST',
        data: {
            accion : 'comprobarAdmin'
        },
        dataType: 'json',
        success: function(res){
            if(res.isAdmin){
                habilitarDebug();
            }else{
               console.log = () => {};
            }
        }
    })
});

