<?php

$db = getDB();
$usuarioModel = new Usuario($db);
$sesionModel = new Sesion($db);
$clienteModel = new Cliente($db);
$archivoModel = new Archivo($db);

$accion   = $_POST['accion'] ?? '';
$filtro = $_POST['filtro'] ??'';
$fecha = null;
if (!empty($_POST['fecha'])) {
    $fecha = new DateTime($_POST['fecha']);
}
$usuario  = $_POST['usuario'] ?? '';
$nuevoUsuario = $_POST['nuevoUsuario'] ?? '';
$password = $_POST['password'] ?? '';
$email = $_POST['email'] ??'';
$adminRaw = $_POST['esAdmin'] ?? null;

$admin = filter_var($adminRaw, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE);
$admin = $admin ? 1 : 0;

$recordarme = $_POST['recordarme'] ?? false;
$idUsuario = $_POST['idUsuario'] ?? null;
$idCliente = $_POST['idCliente'] ?? null;
$idArchivo = $_POST['idArchivo'] ?? null;
$nuevoEstado = $_POST['nuevoEstado'] ?? null;
$nombreCliente = $_POST['nombreCliente'] ?? '';
$telefonoCliente = $_POST['telefonoCliente'] ?? '';
$nombreTabla = $_POST['nombreTabla'] ?? '';

$recordarme = filter_var($recordarme, FILTER_VALIDATE_BOOLEAN);

// Solo ejecutar guard si NO es login, registro o logoutAutomatico
if ($accion !== 'login' && $accion !== 'registro' && $accion !== 'logoutAutomatico' && $accion !== 'cambiarContrasenia') {
    include _ROOT_.DW._HELP_.DW."guard.php";
}

$estaticos = '';
$contenido = '';

switch ($accion) {
    # Vistas
    case 'login':

        if (isset($_SESSION['login']) && $_SESSION['login'] === true) {
            ob_start();
            ponerEstatico('home');
            $estaticos = ob_get_clean();

            ob_start();
            cargarVista('home');
            $contenido = ob_get_clean();
            $response = ['ok' => true, 'estaticos' => $estaticos, 'contenido' => $contenido];
            break;
        }

        $user['id'] = $usuarioModel->obtenerIdUsuarioPorNombre($usuario);

        // Eliminar cualquier sesión anterior de este usuario 
        // que lleve inactiva más de 30 minutos (forzar nueva sesión)
        $sesionExistente = $sesionModel->obtenerSesionUsuario($user['id']);
        if ($sesionExistente) {
            $last = strtotime($sesionExistente['last_activity']);

            if ($last !== false && ($last + 1800) < time()) {
                debug("Usuario '$usuario' ya tiene una sesión activa. Eliminando sesión anterior.", "INFO");
                $sesionModel->eliminarSesionPorUltimaActividad($sesionExistente['last_activity']);
            }
        }

        $user = loginUsuario($db, $usuario, $password);
        session_regenerate_id(true);
        $isAdmin = comprobarAdmin($db, $user['nombre']);
        
        if ($user['deleted'] == 1) {
            debug("Login: usuario eliminado - $usuario", "WARNING");
            $response = ['ok' => false, 'msg' => 'Error: Usuario eliminado'];
            break;
        }

        if ($user and $user['estado'] == 'desconectado'){
            
            $estado = actualizarEstadoUsuario($db, $user['id'], 'pendiente');
            if (!$estado) {
                debug("Error al actualizar estado de usuario a activo - Usuario: $usuario", "ERROR");
                $response = ['ok' => false, 'msg' => 'Error al iniciar sesión'];
                break;
            }
            $response = ['estado' => 'pendiente', 'msg' => 'Tu cuenta está pendiente de activación. Por favor, espera a que un administrador la active.'];

        }else if ($user && $user['estado'] == 'conectado') {
            
            // Crear nueva sesión
            if ($sesionModel->crearSesion($user['id'], session_id()) === true) {
                
                // Forzar commit inmediato actualizando la actividad
                $sesionModel->actualizarActividad(session_id(), $user['id']);

                $_SESSION['login']   = true;    
                $_SESSION['usuario'] = $user['nombre'];
                $_SESSION['admin']   = $isAdmin;
                $_SESSION['foto']    = $user['foto_perfil'] ?? null;
                $_SESSION['idUsuario'] = $user['id'];
                $_SESSION['eliminado'] = $user['deleted'] ?? 1;

                $tiempoSesion = $user['temporizador_sesion'] ?? 1800;

                $_SESSION['tiempo_sesion'] = $tiempoSesion;
                $_SESSION['inicio_sesion'] = time();

                if ($recordarme) {
                    crearRecuerdameToken($db, $user['nombre']);
                }

                ob_start();
                ponerEstatico('home');
                $estaticos = ob_get_clean();

                ob_start();
                cargarVista('home');
                $contenido = ob_get_clean();

                $expiraEn = $_SESSION['inicio_sesion'] + ($_SESSION['tiempo_sesion']);

                $response = [
                    'ok' => true,
                    'estaticos' => $estaticos,
                    'contenido' => $contenido,
                    'expiraEn' => $expiraEn,
                    'sessionId' => session_id(),
                    'usuarioId' => $user['id'],
                    'estado' => $user['estado']
                ];
            } else {
                debug("Error al registrar sesión para usuario: $usuario", "ERROR");
                $response = ['ok' => false, 'msg' => 'Error: Sesion iniciada en otro dispositivo'];
            }

        }else if($user && $user['estado'] == 'pendiente'){  
            $response = ['estado'=> 'pendiente', 'msg' => 'Tu cuenta está pendiente de activación. Por favor, espera a que un administrador la active.'];
        } 
        else {
            debug("Login fallido para usuario: $usuario", "WARNING");
            $response = ['ok' => false, 'msg' => 'Error: Usuario o contraseña incorrectos'];
        }

        break;

    case 'logout':

        if ($_SESSION['admin'] == 0){
            actualizarEstadoUsuario($db, $_SESSION['idUsuario'], 'desconectado');
        } else  actualizarEstadoUsuario($db, $_SESSION['idUsuario'], 'conectado');

        $usuarioModel->eliminarTokenRecordarme($_SESSION['idUsuario'] ?? null);

        $sesionModel->eliminarSesion($_SESSION['idUsuario']);

        setcookie('remember', '', time() - 3600, '/');

        session_unset();
        session_destroy();

        ob_start();
        cargarVista('login');
        $contenido = ob_get_clean();

        $response = ['ok' => true, 'contenido' => $contenido, 'estaticos' => $estaticos];

        break;

    case 'logoutAutomatico':
        
        // Logout automático cuando el usuario cierra el navegador sin hacer logout explícito
        // Se usa el usuarioId para cerrar todas las sesiones del usuario
        $usuarioId = $_POST['usuarioId'] ?? $_SESSION['idUsuario'] ?? null;
        
        if ($usuarioId) {
            $sesionModel->eliminarSesionesPorUsuario($usuarioId);
        }
        
        session_unset();
        session_destroy();
        
        break;

    case 'registro':

        $ok = registroUsuario($db, $usuario, $password, $email, $admin);

        
        if ($ok[0] === false) {
            debug("Registro fallido para usuario: $usuario, email: $email", "WARNING");
            $response = ['ok' => $ok[0], 'msg'=> $ok[1]];
            break;
        }

        $response = ['ok' => true, 'msg' => $ok[1]];

        break;

    case 'perfil':

        ob_start();
        ponerEstatico('');
        $estaticos = ob_get_clean();

        ob_start();
        cargarVista('perfil');
        $contenido = ob_get_clean();

        // Obtener la lista de usuarios
        $usuarios = $usuarioModel->obtenerListaUsuariosNombre($_SESSION['usuario']);

        $response = ['ok' => true, 'estaticos' => $estaticos, 'contenido' => $contenido, 'usuarios' => $usuarios];

        break;

    case 'fuente':

        ob_start();
        ponerEstatico('fuente');
        $estaticos = ob_get_clean();

        ob_start();
        cargarVista('fuente');
        $contenido = ob_get_clean();

        $clientes = $clienteModel->obtenerClientes();

        $response = ['ok' => true, 'estaticos' => $estaticos, 'contenido' => $contenido, 'clientes' => $clientes];

        break;

    case 'archivo':

        ob_start();
        ponerEstatico('archivo');
        $estaticos = ob_get_clean();

        ob_start();
        cargarVista('archivo');
        $contenido = ob_get_clean();

        $cacheTabla = leerJSON(_ROOT_.DW._ASSETS_.DW._CACHE_.DW."nombreBD.json");
        $nombreTabla = '';

        if (!empty($cacheTabla['ok']) && is_array($cacheTabla['datos'])) {
            $nombreTabla = $cacheTabla['datos']['nombre'] ?? '';
        }

        // Compatibilidad con valores antiguos guardados como nombre de fichero .sql
        if (is_string($nombreTabla) && strtolower(pathinfo($nombreTabla, PATHINFO_EXTENSION)) === 'sql') {
            $nombreTabla = pathinfo($nombreTabla, PATHINFO_FILENAME);
        }

        $existeTabla = $nombreTabla !== '' ? existeTablaSQL($db, $nombreTabla) : false;

        $response = [
            'ok' => true,
            'estaticos' => $estaticos,
            'contenido' => $contenido,
            'existeTabla' => $existeTabla,
            'nombreTabla' => $nombreTabla
        ];

        break;

    case 'detalleTabla':
        
        ob_start();
        ponerEstatico('detalleTabla');
        $estaticos = ob_get_clean();

        ob_start();
        cargarVista('detalleTabla');
        $contenido = ob_get_clean();

        $ok = leertablaSQL($db, $nombreTabla);

        if (!$ok['ok']) {
            debug("Error al leer tabla SQL: $nombreTabla - {$ok['error']} - {$ok['msg']}", "ERROR");
            $response = ['ok' => false, 'msg' => $ok['msg'], 'error_code' => $ok['error']];
            break;
        }

        $response = ['ok' => true, 'estaticos' => $estaticos, 'contenido' => $contenido, 'datos' => $ok['datos']];
        
        break;

    case 'detalleCliente':

        ob_start();
        ponerEstatico('detalleCliente');
        $estaticos = ob_get_clean();

        ob_start();
        cargarVista('detalleCliente');
        $contenido = ob_get_clean();

        $clienteLista = $clienteModel->obtenerClienteId((int)$idCliente);
        $cliente = $clienteLista[0] ?? null;

        $response = ['ok' => true, 'estaticos' => $estaticos, 'contenido' => $contenido, 'cliente' => $cliente];

        break;

    case 'usuarios':

        ob_start();
        ponerEstatico('usuarios');
        $estaticos = ob_get_clean();

        ob_start();
        cargarVista('usuarios');
        $contenido = ob_get_clean();

        switch ($filtro) {
            case 'nombre':
                $usuarios = $usuarioModel->obtenerListaUsuariosNombre($usuario);
                break;
            case 'correo':
                $usuarios = $usuarioModel->obtenerListaUsuariosCorreo($email);
                break;
            case 'fecha':
                $usuarios = $usuarioModel->obtenerListaUsuariosFecha($fecha);
                break;
        }     

        if ($filtro === 'nombre' || $filtro === 'correo' || $filtro === 'fecha') {
            // Agregar timestamp a las fotos de perfil
            foreach ($usuarios as &$usuario) {
                if (!empty($usuario['foto_perfil']) && file_exists($usuario['foto_perfil'])) {
                    $usuario['foto_perfil'] .= '?v=' . filemtime($usuario['foto_perfil']);
                }
            }
            $response = ['ok' => true, 'estaticos' => $estaticos, 'contenido' => $contenido, 'usuarios' => $usuarios];
            break;
        }
            
        $usuarios = $usuarioModel->obtenerListaUsuarios();
        
        // Agregar timestamp a las fotos de perfil para evitar caché
        foreach ($usuarios as &$usuario) {
            // Usa la foto del usuario si existe, si no, una por defecto
            $fotoPerfil = !empty($usuario['foto_perfil']) && file_exists($usuario['foto_perfil'])
                ? $usuario['foto_perfil']
                : ($usuario['foto_perfil'] ?? 'assets/images/istockphoto-824860820-612x612.jpg');

            // Añade ?v=timestamp si el archivo existe para forzar recarga del navegador
            $usuario['foto_perfil'] = file_exists($fotoPerfil) 
                ? $fotoPerfil . '?v=' . filemtime($fotoPerfil)
                : $fotoPerfil;
        }

        $response = ['ok' => true, 'estaticos' => $estaticos, 'contenido' => $contenido, 'usuarios' => $usuarios];

        break;
    
    # Funciones
    case 'cambiarFoto':
        
        if (isset($_SESSION['login']) && $_SESSION['login'] && isset($_FILES['selectorImagenPerfil']) && $_FILES['selectorImagenPerfil']['error'] == 0) {
            $idUsuario = $_SESSION['idUsuario'] ?? null;
            
            if ($idUsuario) {
                $resultado = cambiarFotoPerfil($db, $idUsuario, $_FILES['selectorImagenPerfil']);
                
                // Si es un array, es un error
                if (is_array($resultado)) {
                    debug("Error cambio foto usuario $idUsuario: {$resultado['error']} - {$resultado['msg']}", "ERROR");
                    $response = ['ok' => false, 'msg' => $resultado['msg'], 'error_code' => $resultado['error']];
                } else if ($resultado) {
                    // Si es string, es el path de la foto
                    $_SESSION['foto'] = $resultado;
                    $response = ['ok' => true, 'foto' => $resultado];
                } else {
                    $response = ['ok' => false, 'msg' => 'Error desconocido al procesar la foto'];
                }
            } else {
                debug("Cambio foto sin ID de usuario en sesión", "ERROR");
                $response = ['ok' => false, 'msg' => 'No se encontró el ID de usuario'];
            }
        } else {
            debug("Intento de cambio foto sin archivo válido. Error: " . ($_FILES['selectorImagenPerfil']['error'] ?? 'no enviado'), "WARNING");
            $response = ['ok' => false, 'msg' => 'No se ha enviado ninguna foto válida'];
        }

        break;

    case 'subirArchivo':
        // Subir archivos de clientes, solo si el usuario está logueado y se ha enviado un archivo sin errores

        if (isset($_SESSION['login']) && $_SESSION['login'] && isset($_FILES['archivoCliente']) && $_FILES['archivoCliente']['error'] == 0) {

            $idUsuario = $_SESSION['idUsuario'] ?? null;
            
            if ($idUsuario && $idCliente) {
                $resultado = $archivoModel->agregarAchivo($idCliente, $_FILES['archivoCliente']);
                
                if (isset($resultado['error'])) {
                    debug("Error al subir archivo para cliente $idCliente: {$resultado['error']} - {$resultado['msg']}", "ERROR");
                    $response = ['ok' => false, 'msg' => $resultado['msg'], 'error_code' => $resultado['error']];
                } else if (isset($resultado['success']) && $resultado['success']) {
                    $response = ['ok' => true, 'msg' => $resultado['msg']];
                } else {
                    $response = ['ok' => false, 'msg' => 'Error desconocido al subir el archivo'];
                }
            } else {
                debug("Subir archivo sin ID de usuario o cliente en sesión", "ERROR");
                $response = ['ok' => false, 'msg' => 'No se encontró el ID de usuario o cliente'];
            }
        } else {
            $uploadError = $_FILES['archivoCliente']['error'] ?? null;
            debug("Intento de subir archivo sin archivo válido. Error: " . ($uploadError ?? 'no enviado'), "WARNING");

            $mensajes = [
                UPLOAD_ERR_INI_SIZE => 'El archivo supera el tamaño máximo permitido por el servidor',
                UPLOAD_ERR_FORM_SIZE => 'El archivo supera el tamaño máximo permitido por el formulario',
                UPLOAD_ERR_PARTIAL => 'El archivo se subió de forma parcial',
                UPLOAD_ERR_NO_FILE => 'No se ha enviado ningún archivo',
                UPLOAD_ERR_NO_TMP_DIR => 'Falta la carpeta temporal del servidor',
                UPLOAD_ERR_CANT_WRITE => 'No se pudo escribir el archivo en el servidor',
                UPLOAD_ERR_EXTENSION => 'Una extensión de PHP detuvo la subida del archivo'
            ];

            $msg = $mensajes[$uploadError] ?? 'No se ha enviado ningún archivo válido';
            $response = ['ok' => false, 'msg' => $msg, 'error_code' => $uploadError];
        }

        break;

    case 'listarArchivosCliente':

        if (!isset($_SESSION['login']) || !$_SESSION['login']) {
            $response = ['ok' => false, 'msg' => 'Sesion expirada'];
            break;
        }

        if (empty($idCliente)) {
            $response = ['ok' => false, 'msg' => 'Cliente no valido'];
            break;
        }

        $archivos = $archivoModel->regogerArchivosCliente($idCliente);
        $response = ['ok' => true, 'archivos' => $archivos];

        break;

    case 'generarTablas':

        $ok = guardarArchivoYCrearTabla($_FILES['archivoSQL'] ?? null);

        if($ok['error']){
            debug("Error al generar tablas desde archivo SQL: {$ok['error']} - {$ok['msg']}", "ERROR");
            $response = ['ok' => false, 'msg' => $ok['msg'], 'error_code' => $ok['error']];
        } else {
            $response = ['ok' => true, 'msg' => $ok['msg'], 'tablas' => $ok['tablas'] ?? []];
        }
        
        break;

    case 'eliminarArchivosCliente':

        if (!isset($_SESSION['login']) || !$_SESSION['login']) {
            $response = ['ok' => false, 'msg' => 'Sesion expirada'];
            break;
        }

        if (empty($idCliente)) {
            $response = ['ok' => false, 'msg' => 'Cliente no valido'];
            break;
        }

        $archivosEliminados[] = $archivoModel->eliminarArchivosCliente($idCliente);

        if ($archivosEliminados[0] === false) {
            debug("Error al eliminar archivos del cliente: $idCliente", "ERROR");
            $response = ['ok' => false, 'msg' => 'Error al eliminar los archivos del cliente'];
        } else {
            debug("Archivos eliminados correctamente para cliente: $idCliente", "INFO");
            $response = ['ok' => true, 'msg' => 'Los archivos del cliente se han eliminado correctamente'];
        }

        break;

    case 'cambiarContrasenia':

        if (cambiarContrasenia($db, $_SESSION['idUsuario'] ?? null, $_POST['contrasenia'] ?? '', $usuario)) {
            $response = ['ok' => true];
        } else {
            debug("Error al cambiar contraseña para usuario: $usuario", "ERROR");
            $response = ['ok' => false, 'msg' => 'Error al cambiar la contraseña'];
        }

        break;

    case 'renovarSesion':

        if (
            empty($_SESSION['login']) ||
            empty($_SESSION['inicio_sesion']) ||
            empty($_SESSION['tiempo_sesion'])
        ) {
            $response = ['ok' => false, 'expirada' => true];
            break;
        }

        if (time() > ($_SESSION['inicio_sesion'] + $_SESSION['tiempo_sesion'])) {
            session_unset();
            session_destroy();
            $response = ['ok' => false, 'expirada' => true];
            break;
        }

        $_SESSION['inicio_sesion'] = time();

        $expiraEn = $_SESSION['inicio_sesion'] + $_SESSION['tiempo_sesion'];

        $response = [
            'ok' => true,
            'expiraEn' => $expiraEn,
            'sessionId' => session_id(),
            'usuarioId' => $_SESSION['idUsuario'] ?? null,
        ];

        break;

    case 'modificarUsuario':

        if (empty($_SESSION['usuario'])) {
            debug("ModificarUsuario: sesión expirada", "WARNING");
            $response = ['ok' => false, 'msg' => 'Sesión expirada, inicia sesión de nuevo'];
            break;
        }

        $isAdmin = comprobarAdmin($db, $_SESSION['usuario']);

        if ($isAdmin == 0) {
            debug("ModificarUsuario: usuario sin permisos - {$_SESSION['usuario']}", "WARNING");
            $response = ['ok' => false, 'msg' => 'No tienes permisos'];
            break;
        }

        switch ($filtro) {
            case 'cancelarModificacion':

                $ok = $usuarioModel->noModificandoUsuario($idUsuario);

                if (!$ok) {
                    debug("Error al liberar el usuario de modificación - Usuario: $idUsuario, Admin: {$_SESSION['idUsuario']}", "ERROR");
                    $response = ['ok'=> false,'msg'=> 'Error: No se ha podido liberar el usuario'];
                    break;
                }

                $response = ['ok'=> true, 'msg'=> 'Usuario liberado para modificar'];

                break;

            case 'actualizarUsuario':
                
                if ($usuario === '') {
                    debug("ModificarUsuario: usuario no encontrado", "WARNING");
                    $response = ['ok' => false, 'msg' => 'Usuario no especificado'];
                    break;
                }

                $cambios = [];

                if ($nuevoUsuario !== '') {
                    $cambios['nombre'] = $nuevoUsuario;
                }

                if ($email !== '') {
                    $cambios['email'] = $email;
                }

                if (empty($cambios)) {
                    debug("ModificarUsuario: no hay cambios para aplicar para usuario - $usuario", "INFO");
                    $response = ['ok' => false, 'msg' => 'No hay cambios para aplicar'];
                    break;
                }
                
                $ok = $usuarioModel->modificarUsuarioPorNombre($usuario, $cambios);

                if (!$ok) {
                    debug("Error al modificar usuario: $usuario", "ERROR");
                    $response = ['ok'=> $ok,'msg'=> 'Error: no se ha podido modificar el usuario'];
                } else {
                    $response = ['ok'=> $ok, 'msg'=> 'Los cambios se han aplicado correctamente'];
                }

                break;

            default:

                $ok = $usuarioModel->modificandoUsuario($idUsuario);

                if (!$ok) {
                    debug("Error el usuario ya estaba siendo modificado", "ERROR");
                    $response = ['ok'=> $ok,'msg'=> 'Error: El usuario ya estaba siendo modificado'];
                    break;
                }

                $response = ['ok'=> $ok, 'msg'=> 'Usuario listo para modificar'];

                break;
        
        }

        break;

    case 'eliminarUsuarios' :

        $ok = $sesionModel->eliminarSesionesPorUsuario($idUsuario);

        $ok = $ok && $usuarioModel->eliminarUsuario($idUsuario);

        if (!$ok) {
            debug("Error al eliminar usuario: $idUsuario", "ERROR");
            $response = ['ok'=> false,'msg'=> 'Error: no se ha podido eliminar el usuario'];
            break;
        } else {
            debug('El usuario se ha eliminado correctamente', 'INFO');
            $response = ['ok'=> true, 'msg'=> 'El usuario se ha eliminado correctamente'];
        }

        break;

    case 'crearCarpetaCliente':

        $ok[] = $clienteModel->agregarCliente($nombreCliente, $telefonoCliente);

        $clientes = $clienteModel->obtenerClientes();

        if (!$ok[0]) {
            debug("Error al crear carpeta para cliente: $idCliente", "ERROR");
            $response = ['ok'=> false,'mensaje'=> $ok[1]];
            break;
        } else {
            debug('La carpeta del cliente se ha creado correctamente', 'INFO');
            $response = ['ok'=> true, 'mensaje'=> $ok[1], 'clientes' => $clientes];
        }

        break;

    case 'eliminarCliente':
        
        $ok = $clienteModel->eliminarCliente($idCliente);
        
        if(!$ok){
             debug("Error al eliminar cliente: $idCliente", "ERROR");
            $response = ['ok'=> false,'msg'=> 'Error: no se ha podido eliminar el usuario'];
            break;
        }else {
            debug('El cliente se ha eliminado correctamente', 'INFO');
            $response = ['ok'=> true, 'msg'=> 'El usuario se ha eliminado correctamente'];
        }
        
        break;

    case 'eliminarArchivoCliente':

        $ok = $archivoModel->eliminarArchivo($idArchivo, $idCliente);

        if (!$ok) {
            debug("Error al eliminar archivo: $idArchivo para cliente: $idCliente", "ERROR");
            $response = ['ok'=> false,'msg'=> 'Error: no se ha podido eliminar el archivo'];
            break;
        } else {
            debug("El archivo se ha eliminado correctamente: $idArchivo para cliente: $idCliente", "INFO");
            $response = ['ok'=> true, 'msg'=> 'El archivo se ha eliminado correctamente'];
        }

        break;

    case 'verificarSesion':

        // Verifica que la sesión está activa (sin actualizar la actividad)
        // Esta acción es usada por el setInterval del JS para comprobar si la sesión sigue válida
        // sin reiniciar el temporizador de inactividad
        $response = ['ok' => true, 'sesionActiva' => true];

        break;

    case'modificarEstadoUsuario':

        actualizarEstadoUsuario($db, $idUsuario, $nuevoEstado);

        break;

    default:

        debug("Acción no válida recibida: $accion", "WARNING");
        $response = ['ok' => false, 'msg' => 'Acción no válida recibida'];

        break;
}

echo json_encode($response ?? [
    'estaticos' => $estaticos,
    'contenido' => $contenido,
], JSON_UNESCAPED_UNICODE);

exit;
