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
$nombreCampo = $_POST['nombreCampo'] ?? '';
$rutaCSV = $_POST['rutaCSV'] ?? '';
$configuracionCSV = $_POST['configuracionCSV'] ?? null;
$nombreArchivoCSV = $_POST['nombreArchivoCSV'] ?? '';

# Exportacion de archivos

$nombreACSV = $_POST['archivos'] ?? null;
$bdDestino = $_POST['bdDestino'] ?? '';
$prefijodb = $_POST['prefijo'] ?? '';


$recordarme = filter_var($recordarme, FILTER_VALIDATE_BOOLEAN);

// Solo ejecutar guard si NO es login, registro o logoutAutomatico
if ($accion !== 'login' && $accion !== 'registro' && $accion !== 'logoutAutomatico' && $accion !== 'cambiarContrasenia') {
    include _ROOT_.DW._HELP_.DW."guard.php";
}

$estaticos = '';
$contenido = '';

switch ($accion) {
    # Vistas
    // Se ejecuta al intentar iniciar sesion o al validar una sesion ya activa.
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

    // Se ejecuta cuando el usuario cierra sesion manualmente.
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

    // Se ejecuta al cerrar el navegador/pestana sin hacer logout manual.
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

    // Se ejecuta al registrar un nuevo usuario.
    case 'registro':

        $ok = registroUsuario($db, $usuario, $password, $email, $admin);

        
        if ($ok[0] === false) {
            debug("Registro fallido para usuario: $usuario, email: $email", "WARNING");
            $response = ['ok' => $ok[0], 'msg'=> $ok[1]];
            break;
        }

        $response = ['ok' => true, 'msg' => $ok[1]];

        break;

    // Carga la vista de perfil y devuelve los datos del usuario actual.
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

    // Carga la pantalla de origen/fuente y listado de clientes.
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

    // Carga la pantalla de importacion y listado de clientes.
    case 'importar':

        ob_start();
        ponerEstatico('importar');
        $estaticos = ob_get_clean();

        ob_start();
        cargarVista('importar');
        $contenido = ob_get_clean();

        $clientes = $clienteModel->obtenerClientes();

        $response = ['ok' => true, 'estaticos' => $estaticos, 'contenido' => $contenido, 'clientes' => $clientes];

        break;

    // Prepara la vista para mapear/convertir un CSV y su configuracion guardada.
    case 'conversorArchivoCSV':

        ob_start();
        ponerEstatico('conversorArchivoCSV');
        $estaticos = ob_get_clean();

        ob_start();
        cargarVista('conversorArchivoCSV');
        $contenido = ob_get_clean();

        $nombreBase = pathinfo($nombreArchivoCSV, PATHINFO_FILENAME);
        $rutaEspecifica = _ROOT_.DW._ASSETS_.DW._ARCHIVOSC_.DW."cliente_{$idCliente}".DW._CONFIG_.DW."{$nombreBase}"./*_{$nombreTablaDestino}*/".json";

        $lecturaConfJSON = leerJSON($rutaEspecifica);

        debug("Ruta configuración específica: $rutaEspecifica", "INFO");

        if (!$lecturaConfJSON['ok']) {
            $contenidoConf = ['ok' => false, 'msg' => 'No se ha encontrado una configuración previa para este archivo'];
        } else {
            $contenidoConf = $lecturaConfJSON['datos'] ?? [];
        }

        $o = new CSVImportar($db);
        $o->setFile($rutaCSV); // Usando uno de los CSV que creamos
        $o->setClass("mi-clase");
        $id = $o->getId();

        ob_start();
        $o->renderCSV($contenidoConf);
        $htmlCSV = ob_get_clean();

        $cacheTabla = leerJSON(_ROOT_.DW._ASSETS_.DW._CACHE_.DW."nombreBD.json");

        if (!empty($cacheTabla['ok']) && is_array($cacheTabla['datos'])) {
            $campoTabla = $cacheTabla['datos']['campoTabla'] ?? '';
        }

        debug("Id del cliente: $idCliente", "INFO");

        $response = [
            'ok' => true, 
            'estaticos' => $estaticos, 
            'contenido' => $contenido, 
            'html' => $htmlCSV, 
            'campoTabla' => $campoTabla,
            'configuracionGuardada' => $contenidoConf
        ];

        break;

    // Devuelve metadatos/campos de una tabla SQL seleccionada.
    case 'tiposTablas':

        $tipos = obtenerCamposPorTabla($db, $nombreTabla);

        $response = ['ok' => true, 'tipos' => $tipos];

        break;

    // Carga la vista de ficheros y lista los archivos de un cliente.
    case 'ficherosCliente':

        ob_start();
        ponerEstatico('ficherosCliente');
        $estaticos = ob_get_clean();

        ob_start();
        cargarVista('ficherosCliente');
        $contenido = ob_get_clean();

        $clienteId = $clienteModel->obtenerClienteNombre($nombreCliente);
        
        $archivos = $archivoModel->recogerArchivosCliente((int)$clienteId);

        $response = ['ok' => true, 'estaticos' => $estaticos, 'contenido' => $contenido, 'archivos' => $archivos];

        break;

    // Carga la vista de gestion de archivo y estado de la tabla cacheada.
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
            $campoTabla = $cacheTabla['datos']['campoTabla'] ?? '';
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
            'nombreTabla' => $nombreTabla,
            'campoTabla' => $campoTabla
        ];

        break;

    // Exportar archivos de cliente.
    case 'exportarArchivosCliente':

        $csvI = new CSVImportar($db);

        foreach ($nombreACSV as $archivo) {
            $ruta = _ROOT_.DW._ASSETS_.DW._ARCHIVOSC_.DW."cliente_$idCliente".DW."{$archivo['nombre']}";

            $csvI->setFile($ruta);

            $separador[$archivo['nombre']] = $csvI->detectarSeparador();

        }

        $exportacion = exportarCSVABD($idCliente, $bdDestino, $prefijodb, $db, $nombreACSV, $separador);

        $response = ['ok' => $exportacion['ok'], 'msg' => $exportacion['msg']];

        break;

    // Carga la vista de detalle de tabla y devuelve sus datos.
    case 'detalleTabla':
        
        ob_start();
        ponerEstatico('detalleTabla');
        $estaticos = ob_get_clean();

        ob_start();
        cargarVista('detalleTabla');
        $contenido = ob_get_clean();

        $ok = leerCampoTablaSQL($db, $nombreTabla, $nombreCampo);

        if (!$ok['ok']) {
            debug("Error al leer tabla SQL: $nombreTabla - {$ok['error']} - {$ok['msg']}", "ERROR");
            $response = ['ok' => false, 'msg' => $ok['msg'], 'error_code' => $ok['error']];
            break;
        }

        $response = ['ok' => true, 'estaticos' => $estaticos, 'contenido' => $contenido, 'datos' => $ok['datos']];
        
        break;

    // Carga la vista del detalle de un cliente por ID.
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

    // Carga la vista de usuarios y permite listado/filtrado por criterio.
    case 'usuarios':

        ob_start();
        ponerEstatico('usuarios');
        $estaticos = ob_get_clean();

        ob_start();
        cargarVista('usuarios');
        $contenido = ob_get_clean();

        switch ($filtro) {
            // Filtra por nombre de usuario.
            case 'nombre':
                $usuarios = $usuarioModel->obtenerListaUsuariosNombre($usuario);
                break;
            // Filtra por correo electronico.
            case 'correo':
                $usuarios = $usuarioModel->obtenerListaUsuariosCorreo($email);
                break;
            // Filtra por fecha de registro.
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
    // Se ejecuta al subir o actualizar la foto de perfil.
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

    // Se ejecuta al subir un archivo asociado a un cliente.
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

    // Devuelve el listado de archivos para un cliente concreto.
    case 'listarArchivosCliente':

        if (!isset($_SESSION['login']) || !$_SESSION['login']) {
            $response = ['ok' => false, 'msg' => 'Sesion expirada'];
            break;
        }

        if (empty($idCliente)) {
            $response = ['ok' => false, 'msg' => 'Cliente no valido'];
            break;
        }

        $archivos = $archivoModel->recogerArchivosCliente($idCliente);
        $response = ['ok' => true, 'archivos' => $archivos];

        break;

    // Procesa un .sql subido, crea tablas y guarda metadatos en cache.
    case 'generarTablas':

        $ok = guardarArchivoYCrearTabla($_FILES['archivoSQL'] ?? null);

        if($ok['error']){
            debug("Error al generar tablas desde archivo SQL: {$ok['error']} - {$ok['msg']}", "ERROR");
            $response = ['ok' => false, 'msg' => $ok['msg'], 'error_code' => $ok['error']];
        } else {
            $response = ['ok' => true, 'msg' => $ok['msg'], 'tablas' => $ok['tablas'] ?? [], 'campoTabla' => $ok['campoTabla'] ?? ''];
        }
        
        break;

    // Guarda la configuracion de mapeo de un CSV para un cliente.
    case 'guardarConfCSV':

        // Decodificar la configuración si viene como JSON string
        if (is_string($configuracionCSV)) {
            $configuracionCSV = json_decode($configuracionCSV, true);
        }

        $nombreArchivoCSV = $configuracionCSV['archivo'] ?? '';
        $nombreTabla = $configuracionCSV['tabla'] ?? '';

        $archivoModel->añadirTablaArchivo($nombreArchivoCSV, $nombreTabla);

        $ok = guardarConfiguracionCSV($configuracionCSV, $idCliente);

        if ($ok['ok'] === false) {
            debug("Error al guardar configuración CSV: " . $ok['msg'], "ERROR");
            $response = ['ok' => false, 'msg' => $ok['msg']];
        } else {
            $response = ['ok' => true, 'msg' => 'Configuración del CSV guardada correctamente'];
        }

        break;

    // Elimina todos los archivos asociados a un cliente.
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

    // Permite cambiar la contrasena del usuario.
    case 'cambiarContrasenia':

        if (cambiarContrasenia($db, $_SESSION['idUsuario'] ?? null, $_POST['contrasenia'] ?? '', $usuario)) {
            $response = ['ok' => true];
        } else {
            debug("Error al cambiar contraseña para usuario: $usuario", "ERROR");
            $response = ['ok' => false, 'msg' => 'Error al cambiar la contraseña'];
        }

        break;

    // Renueva el temporizador de sesion si aun no ha expirado.
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

    // Gestiona bloqueo/modificacion de datos de usuario (solo admin).
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
            // Libera el bloqueo de edicion de un usuario.
            case 'cancelarModificacion':

                $ok = $usuarioModel->noModificandoUsuario($idUsuario);

                if (!$ok) {
                    debug("Error al liberar el usuario de modificación - Usuario: $idUsuario, Admin: {$_SESSION['idUsuario']}", "ERROR");
                    $response = ['ok'=> false,'msg'=> 'Error: No se ha podido liberar el usuario'];
                    break;
                }

                $response = ['ok'=> true, 'msg'=> 'Usuario liberado para modificar'];

                break;

            // Aplica cambios de nombre y/o correo del usuario.
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

            // Marca al usuario como "en modificacion" para evitar conflictos.
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

    // Elimina un usuario y sus sesiones activas.
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

    // Crea un nuevo cliente (registro + estructura de carpeta asociada).
    case 'crearCarpetaCliente':

        $resultado = $clienteModel->agregarCliente($nombreCliente, $telefonoCliente);
        $ok = $resultado[0] ?? false;
        $mensaje = $resultado[1] ?? 'Error desconocido';

        $clientes = $clienteModel->obtenerClientes();

        if (!$ok) {
            debug("Error al crear carpeta para cliente: $nombreCliente", "ERROR");
            $response = ['ok'=> false,'mensaje'=> $mensaje];
            break;
        } else {
            debug('La carpeta del cliente se ha creado correctamente', 'INFO');
            $response = ['ok'=> true, 'mensaje'=> $mensaje, 'clientes' => $clientes];
        }

        break;

    // Elimina un cliente de la aplicacion.
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

    // Elimina un archivo concreto de un cliente.
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

    // Ping de sesion: valida que siga activa sin renovar temporizador.
    case 'verificarSesion':

        // Verifica que la sesión está activa (sin actualizar la actividad)
        // Esta acción es usada por el setInterval del JS para comprobar si la sesión sigue válida
        // sin reiniciar el temporizador de inactividad
        $response = ['ok' => true, 'sesionActiva' => true];

        break;

    // Actualiza el estado del usuario (conectado/pendiente/desconectado).
    case'modificarEstadoUsuario':

        actualizarEstadoUsuario($db, $idUsuario, $nuevoEstado);

        break;

    // Fallback cuando llega una accion no contemplada.
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
