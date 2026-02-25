/** 
* @brief Este metodo se encarga de mostrar el div que contiene el mensaje de error 
* que se mostrara cuando las credenciales introducidas por el usuario en el login
* no se encuentren en la base de datos, haciendo saber al usuario que a introducido
* mal los datos o que no esta registrado.
* @fecha 21/01/2026
* @return void
*/

export function mostrarError(id) {
    const error = document.getElementById(id);
    if (!error) return;
    error.classList.remove('d-none');
}

