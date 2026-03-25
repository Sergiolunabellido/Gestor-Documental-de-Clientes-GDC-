<?php
/**
 * @brief Se encarga de cargar las vistas en la pagina principal
 * Fecha de creación: 2026-01-12
 * @return void
 */
function cargarVista($vista) {
    $archivoVista = _ROOT_.DW._MOD_.DW."$vista/$vista.php" ;
    if(file_exists($archivoVista)) {
        include $archivoVista;
    }else {
        echo "<h1> La vista no existe </h1>";
    }
}

/*
* @brief Incluye los elementos estaticos de la pagina (navbar y slider) si la vista no es login
 * Fecha de creación: 2026-06-10
 * @param string $vista Nombre de la vista actual
 * @return void
 */

function ponerEstatico($vista) {
     if ($vista != 'login') {

        $navbar = _ROOT_.DW._MOD_.DW._ESTATICO_.DW."navbar.php";
        $slider = _ROOT_.DW._MOD_.DW._ESTATICO_.DW."slider.php";
        $sliderAdmin = _ROOT_.DW._MOD_.DW._ESTATICO_.DW."sliderAdmin.php";

        if (file_exists($navbar)) {
            include $navbar;
        } else {
            echo "<p>Navbar no encontrada</p>";
        }
       
        if (file_exists($sliderAdmin) && $_SESSION['admin'] === 1) {
            include $sliderAdmin;

        } else if (file_exists($slider)) {
            include $slider;
        } else {
            echo "<p>Slider no encontrado</p>";
        }
         

    }
}

/**
 * @brief Escribe un mensaje en el log de la aplicación
 * Fecha de creación: 2026-01-13
 * @param string $msg Mensaje a escribir
 * @param string $nivel Nivel de log (INFO, WARNING, ERROR)
 * @return bool True si se escribió correctamente, false en caso contrario
 */
function debug($msg, $nivel = "INFO") {
    $hora = date("Y-m-d H:i:s");
    $fecha = date("Y-m-d");
    $linea = "[$hora][$nivel] $msg\n";

    $ruta = _ROOT_.DW._ASSETS_.DW._LOGS_.DW;
    
    // Crear directorio logs si no existe
    if (!is_dir($ruta)) {
        @mkdir($ruta, 0775, true);
    }
    
    $logFile = $ruta."app-$fecha.log";

    // Intentar escribir (con LOCK_EX, si falla, sin bloqueo)
    return @file_put_contents($logFile, $linea, FILE_APPEND | LOCK_EX);
}

/**
 * @brief Renderiza un modal de Bootstrap reutilizable
 * @param array $config Configuracion del modal
 * @return void
 */
function renderModal(array $config): void {
    $ariaLabelledBy = $config['ariaLabelledBy'] ?? ($config['id'] . 'Title');
    $ariaHidden = isset($config['ariaHidden']) ? ' aria-hidden="' . $config['ariaHidden'] . '"' : '';
    ?>
    <div id="<?php echo htmlspecialchars($config['id'], ENT_QUOTES, 'UTF-8'); ?>" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="<?php echo htmlspecialchars($ariaLabelledBy, ENT_QUOTES, 'UTF-8'); ?>"<?php echo $ariaHidden; ?>>
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="<?php echo htmlspecialchars($ariaLabelledBy, ENT_QUOTES, 'UTF-8'); ?>"><?php echo htmlspecialchars($config['title'], ENT_QUOTES, 'UTF-8'); ?></h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <?php echo $config['body']; ?>
                </div>
                <div class="modal-footer">
                    <?php foreach ($config['footerButtons'] as $button): ?>
                        <button
                            id="<?php echo htmlspecialchars($button['id'], ENT_QUOTES, 'UTF-8'); ?>"
                            type="<?php echo htmlspecialchars($button['type'] ?? 'button', ENT_QUOTES, 'UTF-8'); ?>"
                            class="<?php echo htmlspecialchars($button['class'], ENT_QUOTES, 'UTF-8'); ?>"
                            <?php echo !empty($button['dismiss']) ? 'data-bs-dismiss="modal"' : ''; ?>
                        >
                            <?php echo htmlspecialchars($button['label'], ENT_QUOTES, 'UTF-8'); ?>
                        </button>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </div>
    <?php
}

/**
 * @brief Renderiza los modales globales usados en la aplicacion
 * @return void
 */
function renderModalesBase(): void {
    renderModal([
        'id' => 'modalModificarUsuario',
        'title' => 'Modificar Usuario',
        'ariaLabelledBy' => 'modalModificarUsuarioTitle',
        'ariaHidden' => 'true',
        'body' => <<<'HTML'
            <form id="formModificarUsuario" class="d-flex flex-column gap-3">
                <div>
                    <label for="nuevoNombre" class="form-label">Nuevo nombre:</label>
                    <input id="nuevoNombre" type="text" class="form-control" placeholder="Ingrese su nuevo nombre de usuario" required>
                </div>
                <div>
                    <label for="nuevoEmail" class="form-label">Nueva Email:</label>
                    <input id="nuevoEmail" type="email" class="form-control" placeholder="Ingrese su nueva direccion de correo electronico" required>
                </div>
                <div id="errorModificar" class="alert alert-danger d-none" role="alert">
                    <p id="mensajeErrorModificar"></p>
                </div>
            </form>
        HTML,
        'footerButtons' => [
            [
                'id' => 'botonCancelarModificacion',
                'type' => 'button',
                'class' => 'btn btn-secondary',
                'label' => 'Cancelar',
                'dismiss' => true,
            ],
            [
                'id' => 'botonModificar',
                'type' => 'button',
                'class' => 'btn btn-primary',
                'label' => 'Confirmar Cambio',
            ],
        ],
    ]);

    renderModal([
        'id' => 'modalEliminarUsuario',
        'title' => 'Eliminar Usuario',
        'ariaLabelledBy' => 'modalEliminarUsuarioTitle',
        'ariaHidden' => 'true',
        'body' => <<<'HTML'
            <div class="d-flex align-items-center">
                <label for="nuevoNombre" class="form-label">Desea eliminar este usuario?</label>
            </div>
        HTML,
        'footerButtons' => [
            [
                'id' => 'botonCancelarEliminar',
                'type' => 'button',
                'class' => 'btn btn-secondary',
                'label' => 'Cancelar',
                'dismiss' => true,
            ],
            [
                'id' => 'botonEliminar',
                'type' => 'button',
                'class' => 'btn btn-primary',
                'label' => 'Aceptar',
            ],
        ],
    ]);

      renderModal([
        'id' => 'modalEliminarArchivo',
        'title' => 'Eliminar Archivo',
        'ariaLabelledBy' => 'modalEliminarArchivoTitle',
        'ariaHidden' => 'true',
        'body' => <<<'HTML'
            <div class="d-flex align-items-center">
                <label for="nuevoNombre" class="form-label">Desea eliminar este archivo?</label>
            </div>
        HTML,
        'footerButtons' => [
            [
                'id' => 'botonCancelarEliminar',
                'type' => 'button',
                'class' => 'btn btn-secondary',
                'label' => 'Cancelar',
                'dismiss' => true,
            ],
            [
                'id' => 'botonEliminarArchivo',
                'type' => 'button',
                'class' => 'btn btn-primary',
                'label' => 'Aceptar',
            ],
        ],
    ]);

    renderModal([
        'id' => 'modalEliminarArchivos',
        'title' => 'Eliminar Archivos',
        'ariaLabelledBy' => 'modalEliminarArchivosTitle',
        'ariaHidden' => 'true',
        'body' => <<<'HTML'
            <div class="d-flex align-items-center">
                <label for="nuevoNombre" class="form-label">Esta usted seguro de que desea eliminar todos los archivos?</label>
            </div>
        HTML,
        'footerButtons' => [
            [
                'id' => 'botonCancelarEliminar',
                'type' => 'button',
                'class' => 'btn btn-secondary',
                'label' => 'Cancelar',
                'dismiss' => true,
            ],
            [
                'id' => 'botonEliminarArchivos',
                'type' => 'button',
                'class' => 'btn btn-primary',
                'label' => 'Aceptar',
            ],
        ],
    ]);

    renderModal([
        'id' => 'modalCrearCliente',
        'title' => 'Crear Cliente',
        'ariaLabelledBy' => 'modalCrearClienteTitle',
        'body' => <<<'HTML'
            <form id="formModificarUsuario" class="d-flex flex-column gap-3">
                <div>
                    <label for="nombreCliente" class="form-label">Nomrbre Cliente:</label>
                    <input id="nombreCliente" type="text" class="form-control" placeholder="Ingrese el nombre del cliente" required>
                </div>
                <div>
                    <label for="telefonoCliente" class="form-label">Telefono Cliente:</label>
                    <input id="telefonoCliente" type="email" class="form-control" placeholder="Ingrese el telefono del cliente" required>
                </div>
                <div id="errorCrearCliente" class="alert alert-danger d-none" role="alert">
                    <p id="mensajeErrorCliente"></p>
                </div>
            </form>
        HTML,
        'footerButtons' => [
            [
                'id' => 'botonCancelarCreacionCliente',
                'type' => 'button',
                'class' => 'btn btn-secondary',
                'label' => 'Cancelar',
                'dismiss' => true,
            ],
            [
                'id' => 'botonCrear',
                'type' => 'button',
                'class' => 'btn btn-primary',
                'label' => 'Aceptar',
            ],
        ],
    ]);

    renderModal([
        'id' => 'modalCrearFichero',
        'title' => 'Subir Fichero',
        'ariaLabelledBy' => 'modalCrearFicheroTitle',
        'body' => <<<'HTML'
            <form id="formModificarUsuario" class="d-flex flex-column gap-3">
                <div>
                    <label for="idFichero" class="form-label">Archivo:</label>
                    <input id="idFichero" type="file" class="form-control" placeholder="Ingrese el nombre del cliente" required>
                </div>
                <div id="errorCrearFichero" class="alert alert-danger d-none" role="alert">
                    <p id="mensajeErrorFichero"></p>
                </div>
            </form>
        HTML,
        'footerButtons' => [
            [
                'id' => 'botonCancelarCreacionCliente',
                'type' => 'button',
                'class' => 'btn btn-secondary',
                'label' => 'Cancelar',
                'dismiss' => true,
            ],
            [
                'id' => 'botonCrearFichero',
                'type' => 'button',
                'class' => 'btn btn-primary',
                'label' => 'Aceptar',
            ],
        ],
    ]);

    
}

/**
 * @brief Guarda un archivo subido por el usuario y recarga solo la tabla principal vltfddb
 * @param array $archivo Información del archivo subido
 * @return array{error: string, msg: string|array{msg: string, success: bool}}
 * Fecha de creación: 2026-02-24
 */
function guardarArchivoYCrearTabla($archivo) {

    if (
        !is_array($archivo) ||
        !isset($archivo['error']) ||
        $archivo['error'] !== UPLOAD_ERR_OK ||
        empty($archivo['name']) ||
        empty($archivo['tmp_name'])
    ) {
        return ['error' => 'upload_invalido', 'msg' => 'No se recibio un archivo SQL valido'];
    }

    $baseDir = _ROOT_.DW._ASSETS_.DW._ARCHIVOSQL_.DW;

    if (!is_dir($baseDir)) {
        if (!mkdir($baseDir, 0755, true)) {
            debug("No se pudo crear el directorio base de archivos: $baseDir", "ERROR");
            return ['error' => 'mkdir_fallo', 'msg' => 'No se pudo crear el directorio: ' . $baseDir];
        }
    }

    if (!is_writable($baseDir)) {
        debug("El directorio base no es escribible: $baseDir", "ERROR");
        return ['error' => 'no_escribible', 'msg' => 'El directorio no es escribible: ' . $baseDir];
    }

    $nuevoNombre = basename($archivo['name']);
    $destino = $baseDir . $nuevoNombre;

    if (strtolower(pathinfo($nuevoNombre, PATHINFO_EXTENSION)) !== 'sql') {
        return ['error' => 'tipo_invalido', 'msg' => 'Solo se permiten archivos .sql'];
    }

    if (!move_uploaded_file($archivo['tmp_name'], $destino)) {
        debug("Error al mover el archivo subido: " . $archivo['name'], "ERROR");
        return ['error' => 'move_failed', 'msg' => 'No se pudo guardar el archivo'];
    }

    // Debug de seguridad: registrar el nombre del archivo y el usuario que lo subió
    debug("Archivo ".$archivo['name']." por ". $_SESSION['usuario'], "INFO");

    $contenido = file_get_contents($destino);
    if ($contenido === false) {
        return ['error' => 'read_failed', 'msg' => 'No se pudo leer el archivo'];
    }

    $tablaPrincipal = 'vltfddb';
    $contenidoSinComentarios = limpiarComentariosSQL($contenido);
    $nombreTabla = obtenerTablasDesdeSQL($contenidoSinComentarios);
    $nombreCampoTabla = obtenerCampoTablaSQL($contenidoSinComentarios, $nombreTabla[0] ?? $tablaPrincipal);
    $createTablaPrincipal = extraerCreateTablaSQL($contenidoSinComentarios, $nombreTabla[0] ?? $tablaPrincipal);
    $alterTablaPrincipal = extraerAlterTablaSQL($contenidoSinComentarios, $nombreTabla[0] ?? $tablaPrincipal);
    $insertTablaPrincipal = extraerInsertTablaSQL($contenidoSinComentarios, $nombreTabla[0] ?? $tablaPrincipal);
    escribirJSON(_ROOT_.DW._ASSETS_.DW._CACHE_.DW."nombreBD.json", $nombreTabla[0] ?? $tablaPrincipal, $nombreCampoTabla);

    try {
        $conexion = getDB();
        $conexion->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    } catch (PDOException $e) {
        debug("Error de conexión a la base de datos: " . $e->getMessage(), "ERROR");
        return ['error' => 'db_connection_failed', 'msg' => 'Error de conexión a la base de datos'];
    }

    try {
        $tablaExiste = existeTablaSQL($conexion, $nombreTabla[0] ?? $tablaPrincipal);

        // CREATE/ALTER en MySQL provocan commit implícito, por eso van fuera de la transacción de datos.
        if (!$tablaExiste) {
            if (!$createTablaPrincipal) {
                debug("No se encontró CREATE TABLE para '$tablaPrincipal' en el SQL.", "ERROR");
                throw new Exception("No se encontro CREATE TABLE para '$tablaPrincipal' en el SQL.");
            }

            $conexion->exec($createTablaPrincipal);

            foreach ($alterTablaPrincipal as $sqlAlter) {
                $conexion->exec($sqlAlter);
            }
        }

        $conexion->beginTransaction();
        $nombreRealTabla = $nombreTabla[0] ?? $tablaPrincipal;
        $conexion->exec("DELETE FROM `$nombreRealTabla`");

        foreach ($insertTablaPrincipal as $sqlInsert) {
            $conexion->exec($sqlInsert);
        }

        $conexion->commit();

        debug("Archivo procesado exitosamente: " . $archivo['name'], "INFO");
        return ['error' => null, 'msg' => 'Archivo procesado exitosamente', 'tablas' => [$nombreRealTabla], 'campoTabla' => $nombreCampoTabla];
    } catch (Throwable $e) {
        if ($conexion->inTransaction()) {
            $conexion->rollBack();
        }
        debug("Error al ejecutar SQL para tabla principal: " . $e->getMessage(), "ERROR");
        return ['error' => 'sql_execution_failed', 'msg' => 'Error al recargar la tabla principal: ' . $e->getMessage()];
    }
}

/**
 * @brief Elimina comentarios SQL para facilitar la extracción de sentencias
 * @param string $contenido Contenido del archivo SQL
 * @return string Contenido sin comentarios
 * Fecha de creación: 2026-02-24
 */
function limpiarComentariosSQL($contenido) {
    $contenido = preg_replace('/\/\*.*?\*\//s', '', $contenido);
    $contenido = preg_replace('/--.*$/m', '', $contenido);
    $contenido = preg_replace('/#.*$/m', '', $contenido);
    return $contenido;
}

/**
 * @brief Extrae el CREATE TABLE de una tabla específica
 * @param string $contenido Contenido del archivo SQL
 * @param string $tabla Nombre de la tabla a buscar
 * @return string|null Sentencia CREATE TABLE completa o null si no se encuentra
 * Fecha de creación: 2026-02-24
 */
function extraerCreateTablaSQL($contenido, $tabla) {
    $tablaEscapada = preg_quote($tabla, '/');

    if (preg_match('/\bCREATE\s+TABLE\s+(?:IF\s+NOT\s+EXISTS\s+)?`?' . $tablaEscapada . '`?\s+.*?;/is', $contenido, $matches)) {
        return trim($matches[0]);
    }

    return null;
}

/**
 * @brief Extrae todos los ALTER TABLE de una tabla específica
 * @param string $contenido Contenido del archivo SQL
 * @param string $tabla Nombre de la tabla a buscar
 * @return array Lista de sentencias ALTER TABLE encontradas
 * Fecha de creación: 2026-02-24
 */
function extraerAlterTablaSQL($contenido, $tabla) {
    $tablaEscapada = preg_quote($tabla, '/');
    preg_match_all('/\bALTER\s+TABLE\s+`?' . $tablaEscapada . '`?\s+.*?;/is', $contenido, $matches);
    return $matches[0] ?? [];
}

/**
 * @brief Extrae todos los INSERT INTO de una tabla específica
 * @param string $contenido Contenido del archivo SQL
 * @param string $tabla Nombre de la tabla a buscar
 * @return array Lista de sentencias INSERT INTO encontradas
 * Fecha de creación: 2026-02-24
 */
function extraerInsertTablaSQL($contenido, $tabla) {
    $tablaEscapada = preg_quote($tabla, '/');
    preg_match_all('/\bINSERT\s+INTO\s+`?' . $tablaEscapada . '`?\s+.*?;/is', $contenido, $matches);
    return $matches[0] ?? [];
}

/**
 * @brief Comprueba si una tabla existe en la base de datos actual
 * @param PDO $conexion Conexión PDO a la base de datos
 * @param string $tabla Nombre de la tabla a verificar
 * @return bool True si la tabla existe, false en caso contrario
 * Fecha de creación: 2026-02-24
 */
function existeTablaSQL($conexion, $tabla) {
    try {
        $sql = "SELECT COUNT(*) FROM information_schema.tables WHERE table_schema = DATABASE() AND table_name = :tabla";
        $stmt = $conexion->prepare($sql);

        if (!$stmt) {
            return false;
        }

        $stmt->execute([':tabla' => $tabla]);
        $total = $stmt->fetchColumn();

        return (int)$total > 0;
    } catch (PDOException $e) {
        return false;
    }
}

/**
 * @brief Extrae los nombres de las tablas creadas en un archivo SQL
 * @param string $contenido Contenido del archivo SQL
 * @return array Lista de nombres de tablas encontradas
 * Fecha de creación: 2026-02-24
 */
function obtenerTablasDesdeSQL($contenido) {

    $contenido = limpiarComentariosSQL($contenido);

    // Buscar todas las CREATE TABLE
    preg_match_all(
        '/CREATE\s+TABLE\s+(?:IF\s+NOT\s+EXISTS\s+)?`?([a-zA-Z0-9_]+)`?/i',
        $contenido,
        $matches
    );

    return $matches[1] ?? [];
}

/**
 * @brief Lee los datos de una tabla SQL y los devuelve como un array asociativo
 * @param PDO $db Conexión PDO a la base de datos
 * @param string $nombreTabla Nombre de la tabla a leer
 * @return array Resultado con clave 'ok' indicando éxito o error, y 'datos' o 'error' según corresponda
 * Fecha de creación: 2026-02-24
 */
function leertablaSQL($db, $nombreTabla) {

    try {

        $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        $stmt = $db->query("SELECT * FROM $nombreTabla");
        $datos = $stmt->fetchAll(PDO::FETCH_ASSOC);

        return ['ok' => true, 'datos' => $datos];

    } catch (PDOException $e) {
        
        return ['ok' => false, 'error' => $e->getMessage()];
    }

}

/**
 * @brief Lee los datos de un campo específico de una tabla SQL y los devuelve como un array asociativo
 * @param PDO $db Conexión PDO a la base de datos
 * @param string $nombreTabla Nombre de la tabla a leer
 * @param string $nombreCampoTabla Nombre del campo específico a leer
 * @return array Resultado con clave 'ok' indicando éxito o error, y 'datos' o 'error' según corresponda
 * Fecha de creación: 2026-03-03
 */
function leerCampoTablaSQL($db, $nombreTabla, $nombreCampoTabla) {

    try {

        $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        $stmt = $db->query("SELECT * FROM $nombreTabla WHERE mtable = '$nombreCampoTabla'");
        $datos = $stmt->fetchAll(PDO::FETCH_ASSOC);

        return ['ok' => true, 'datos' => $datos];

    } catch (PDOException $e) {
        
        return ['ok' => false, 'error' => $e->getMessage()];
    }

}

/**
 * @brief Lee un archivo JSON y devuelve su contenido como un array asociativo
 * @param string $ruta Ruta del archivo JSON a leer
 * @return array Resultado con clave 'ok' indicando éxito o error, y 'datos' o 'error' según corresponda
 * Fecha de creación: 2026-02-24
 */
function leerJSON($ruta) {
    if (!file_exists($ruta)) {
        return ['ok' => false, 'error' => 'Archivo no encontrado'];
    }

    $contenido = file_get_contents($ruta);
    if ($contenido === false) {
        return ['ok' => false, 'error' => 'No se pudo leer el archivo'];
    }

    $datos = json_decode($contenido, true);
    if (json_last_error() !== JSON_ERROR_NONE) {
        return ['ok' => false, 'error' => 'Error al decodificar JSON: ' . json_last_error_msg()];
    }

    debug("Archivo JSON leído correctamente: $ruta y los datos: " . json_encode($datos), "INFO");

    return ['ok' => true, 'datos' => $datos];
}

/**
 * @brief Escribe un array asociativo en un archivo JSON
 * @param string $ruta Ruta del archivo JSON a escribir
 * @param array $datos Datos a escribir en el archivo JSON
 * @return array Resultado con clave 'ok' indicando éxito o error, y 'error' en caso de fallo
 * Fecha de creación: 2026-02-24
 */
function escribirJSON($ruta, $nombreTabla, $datos = []) {
    $datos = ['nombre' => $nombreTabla, 'campoTabla' => $datos];

    $contenido = json_encode($datos, JSON_PRETTY_PRINT);
    if ($contenido === false) {
        return ['ok' => false, 'error' => 'Error al codificar JSON: ' . json_last_error_msg()];
    }

    if (file_put_contents($ruta, $contenido) === false) {
        return ['ok' => false, 'error' => 'No se pudo escribir en el archivo'];
    }

    return ['ok' => true];
}

/**
 * @brief Extrae los valores únicos de un campo específico en los INSERT de una tabla dada en un archivo SQL
 * @param string $contenido Contenido del archivo SQL
 * @param string $tabla Nombre de la tabla a analizar
 * @return array Lista de valores únicos encontrados para el campo especificado
 * Fecha de creación: 2026-03-03
 */
function obtenerCampoTablaSQL($contenido, $tabla) {
    
    $nombreDelCampo = 'mtable';

    // Buscar todos los INSERT de la tabla pelicula
    $tablaEscapada = preg_quote($tabla, '/');
    preg_match_all('/INSERT\s+INTO\s+`?' . $tablaEscapada . '`?\s*\((.*?)\)\s*VALUES\s*(.*?);/is', $contenido, $matches);

    $valoresUnicos = [];

    foreach ($matches[1] as $index => $columnasTexto) {
    
        $valoresTexto = $matches[2][$index];

        // Convertir columnas en array
        $columnas = array_map(function($col) {
            return trim(str_replace('`', '', $col));
        }, explode(',', $columnasTexto));

        // Buscar índice del campo que quieres
        $indiceCampo = array_search($nombreDelCampo, $columnas);

        if ($indiceCampo === false) continue;

        // Separar filas
        $filas = explode("),", $valoresTexto);

        foreach ($filas as $fila) {
            $fila = str_replace(['(', ')'], '', $fila);
            $campos = str_getcsv($fila, ',', "'");

            if (isset($campos[$indiceCampo])) {
                $valoresUnicos[] = $campos[$indiceCampo];
            }
        }

    }

    return array_values(array_unique($valoresUnicos));
}

/**
 * @brief Obtiene los valores de un campo específico para una tabla dada desde la base de datos
 * @param PDO $db Conexión PDO a la base de datos
 * @param string $tabla Nombre de la tabla a consultar
 * @return array Lista de valores encontrados para el campo especificado
 * Fecha de creación: 2026-03-09
 */
function obtenerCamposPorTabla($db, $tabla) {
    $sql = "SELECT mfield FROM vltfddb WHERE mtable = :tabla";
    $stmt = $db->prepare($sql);
    $stmt->execute(['tabla' => $tabla]);
    $campos = $stmt->fetchAll(PDO::FETCH_COLUMN);

    return $campos;
}

/**
 * @brief Obtiene los tipos de un campo específico para una tabla dada desde la base de datos
 * @param PDO $db Conexión PDO a la base de datos
 * @param string $tabla Nombre de la tabla a consultar
 * @param string $campo Nombre del campo a consultar
 * @return array Lista de tipos encontrados para el campo especificado
 * Fecha de creación: 2026-03-23
 */
function obtenerTiposPorTablaYCampo($db, $tabla, $campo) {
    $sql = "SELECT mtype FROM vltfddb WHERE mtable = :tabla AND mfield = :campo";
    $stmt = $db->prepare($sql);
    $stmt->execute(['tabla' => $tabla, 'campo' => $campo]);
    $tipos = $stmt->fetchAll(PDO::FETCH_COLUMN);

    return $tipos;
}

/**
 * @brief Guarda la configuración de un archivo CSV en un archivo JSON específico para el cliente
 * @param array $config Configuración del CSV a guardar
 * @param int $idCliente ID del cliente para el que se guarda la configuración
 * @return array Resultado con clave 'ok' indicando éxito o error, y 'msg' con información adicional
 * Fecha de creación: 2026-03-10
 */
function guardarConfiguracionCSV($config, $idCliente) {

    // Validar que el ID del cliente sea un entero positivo y no esté vacío
    if ($idCliente <= 0 || empty($idCliente)) {
        debug("ID de cliente inválido: $idCliente", "ERROR");
        return ['ok' => false, 'msg' => 'ID de cliente inválido o no proporcionado'];
    }

    // Validar que la configuración sea un array
    if (!is_array($config)) {
        debug("Configuración CSV no es un array válido", "ERROR");
        return ['ok' => false, 'msg' => 'Configuración inválida'];
    }

    // Validar que tenga el campo 'archivo'
    if (empty($config['archivo']) || empty($config['tabla'])) {
        debug("Faltan datos críticos: archivo o tabla destino", "ERROR");
        return ['ok' => false, 'msg' => 'Falta el nombre del archivo o la tabla en la configuración'];
    }

    $cacheDir = _ROOT_.DW._ASSETS_.DW._ARCHIVOSC_.DW."cliente_$idCliente".DW._CONFIG_.DW;

    if (!is_dir($cacheDir)) {
        if (!mkdir($cacheDir, 0755, true)) {
            debug("No se pudo crear el directorio: $cacheDir", "ERROR");
            return ['ok' => false, 'msg' => 'Error al crear directorio de configuración'];
        }
    }
    
    // Asegurar permisos de escritura
    if (!is_writable($cacheDir)) {
        debug("El directorio no tiene permisos de escritura: $cacheDir", "ERROR");
        return ['ok' => false, 'msg' => 'El directorio de configuración no tiene permisos de escritura'];
    }

    $nombreBase = pathinfo($config['archivo'], PATHINFO_FILENAME);
    // $tablaDestino = $config['tabla'];

    $nombreFinalId = "{$nombreBase}"./*_{$tablaDestino}*/".json";

    $cacheFile = $cacheDir . $nombreFinalId;
    
    if (file_put_contents($cacheFile, json_encode($config, JSON_PRETTY_PRINT)) === false) {
        debug("No se pudo escribir el archivo: $cacheFile", "ERROR");
        return ['ok' => false, 'msg' => 'Error al guardar el archivo de configuración'];
    }
    
    debug("Configuración guardada correctamente en: $cacheFile", "INFO");
    return ['ok' => true, 'msg' => 'Configuración guardada correctamente'];
}

/**
 * @brief Obtiene las configuraciones de archivos CSV para un cliente específico leyendo los archivos JSON correspondientes
 * @param string $nombreArchivoCSV Nombre del archivo CSV para el cual se buscan las configuraciones
 * @param int $idCliente ID del cliente para el que se obtienen las configuraciones
 * @return array Resultado con clave 'ok' indicando éxito o error, y 'datos' con las configuraciones encontradas o 'msg' con información adicional
 * Fecha de creación: 2026-03-10
 */
function obtenerConfiguracionesDeArchivo($nombreArchivoCSV, $idCliente) {
    
    $cacheDir = _ROOT_.DW._ASSETS_.DW._ARCHIVOSC_.DW."cliente_$idCliente".DW._CONFIG_.DW;
    $nombreBase = pathinfo($nombreArchivoCSV, PATHINFO_FILENAME);
    $configsEncontradas = [];

    if (is_dir($cacheDir)) {
        // Buscamos todos los archivos que empiecen por "nombreArchivo_"
        $archivos = glob($cacheDir . $nombreBase . "_*.json");

        foreach ($archivos as $archivo) {
            // Usamos tu función leerJSON existente
            $res = leerJSON($archivo); 
            if ($res['ok']) {
                $configsEncontradas[] = $res['datos'];
            }
        }
    }

    return [
        'ok' => !empty($configsEncontradas),
        'datos' => $configsEncontradas, // Devuelve un array de objetos de configuración
        'msg' => empty($configsEncontradas) ? 'No hay configuraciones' : 'Configs cargadas'
    ];
}

/**
 * @brief Lee un archivo CSV y devuelve su contenido como un array asociativo utilizando la primera fila como cabecera
 * @param string $ruta Ruta del archivo CSV a leer
 * @return array Resultado con clave 'ok' indicando éxito o error, y 'datos' con el contenido del CSV o 'error' con información adicional
 * Fecha de creación: 2026-03-16
 */
function leerCSV($ruta, $separador) {
    if (!file_exists($ruta)) {
        return ['ok' => false, 'error' => 'Archivo no encontrado'];
    }

    $datos = [];
    if (($handle = fopen($ruta, "r")) !== false) {
        $cabecera = fgetcsv($handle, 0, $separador); // Leer la primera línea como cabecera
        while (($fila = fgetcsv($handle, 0, $separador)) !== false) {
            $datos[] = array_combine($cabecera, $fila); // Combinar cabecera con fila para obtener un array asociativo
        }
        fclose($handle);
        return ['ok' => true, 'datos' => $datos];
    } else {
        return ['ok' => false, 'error' => 'No se pudo abrir el archivo'];
    }
}

/**
 * @brief Verifica si un valor dado coincide con formatos comunes de fecha y hora para determinar si es un valor de tipo DATETIME
 * @param string $valor Valor a verificar
 * @return bool True si el valor coincide con algún formato de fecha/hora, false en caso contrario
 * Fecha de creación: 2026-03-16
 */
function esDatetime($valor) {
    $formatos = [
        '/^\d{4}-\d{2}-\d{2}$/',                  // 2024-03-16
        '/^\d{2}\/\d{2}\/\d{4}$/',                // 16/03/2024
        '/^\d{2}-\d{2}-\d{4}$/',                  // 16-03-2024
        '/^\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2}$/', // 2024-03-16 12:00:00
        '/^\d{2}\/\d{2}\/\d{4} \d{2}:\d{2}:\d{2}$/', // 16/03/2024 12:00:00
    ];

    foreach ($formatos as $formato) {
        if (preg_match($formato, $valor)) return true;
    }
    return false;
}

/**
 * @brief Detecta el tipo de dato de un valor dado para mapearlo a un tipo SQL adecuado
 * @param string $valor Valor del cual se quiere detectar el tipo de dato
 * @return string Tipo de dato detectado (INT, FLOAT, DATETIME, TEXT)
 * Fecha de creación: 2026-03-16
 */
function detectarTipoDato($valor) {
    if (is_numeric($valor) && strpos($valor, '.') !== false) return 'FLOAT';
    if (is_numeric($valor)) return 'INT';
    if (esDatetime($valor)) return 'DATETIME';
    return 'VARCHAR(255)';
}

/**
 * @brief Detecta los tipos de datos de las columnas de un archivo CSV basándose en su contenido y devuelve un mapeo de columnas a tipos SQL
 * @param array $filas Array de filas del CSV, donde cada fila es un array asociativo con los nombres de las columnas como claves
 * @param array $mapC Mapeo de columnas del CSV a columnas de la base de datos (columnaCSV => columnaBD)
 * @return array Mapeo de columnas de la base de datos a tipos SQL detectados (columnaBD => tipoSQL)
 * Fecha de creación: 2026-03-16
 */
function detectarTiposColumnas($filas, $mapC) {
    $tipos = [];
    foreach ($mapC as $columnaCSV => $columnaBD) {
        $tiposEncontrados = [];
        foreach ($filas as $fila) {
            $valor = resolverExpresionesCSV($columnaCSV, $fila);

            // Ignorar valores no resolubles o vacíos
            if ($valor === null || $valor === '') {
                continue;
            }

            $tiposEncontrados[] = detectarTipoDato($valor);
        }
        // Si no encontramos nada, por seguridad dejamos texto
        if (empty($tiposEncontrados)) {
            $tipos[$columnaBD] = 'VARCHAR(255)';
        } elseif (in_array('VARCHAR(255)', $tiposEncontrados, true)) {
            $tipos[$columnaBD] = 'VARCHAR(255)';
        } elseif (in_array('FLOAT', $tiposEncontrados, true)) {
            $tipos[$columnaBD] = 'FLOAT';
        } elseif (in_array('DATETIME', $tiposEncontrados, true)) {
            $tipos[$columnaBD] = 'DATETIME';
        } else {
            $tipos[$columnaBD] = 'INT';
        }
    }
    return $tipos;
}

/**
 * @brief Exporta los datos de un archivo CSV a una base de datos específica utilizando la configuración guardada previamente
 * @param int $idCliente ID del cliente para el cual se realiza la exportación
 * @param string $bdDestino Nombre de la base de datos destino
 * @param string $prefijodb Prefijo para el nombre de la base de datos
 * @param PDO $conexionbd Conexión PDO a la base de datos
 * @param array $archivoCSV Array de configuraciones de archivos CSV a exportar, cada uno con 'nombre' y 'tabla'
 * @param array $separador Mapeo de nombres de archivos CSV a sus respectivos separadores (archivoCSV => separador)
 * @return array Resultado con clave 'ok' indicando éxito o error, y 'msg' con información adicional
 * Fecha de creación: 2026-03-16
 */
function exportarCSVABD($idCliente, $bdDestino, $prefijodb, $conexionbd, $archivoCSV, $separador) {
    
    // Esta función se encargará de exportar los datos de un archivo CSV a una base de datos específica, utilizando la configuración guardada previamente.

    if (empty($idCliente) || empty($bdDestino) || empty($prefijodb)) {
        debug("Parámetros inválidos para exportar CSV a BD: idCliente=$idCliente, bdDestino=$bdDestino, prefijodb=$prefijodb", "ERROR");
        return ['ok' => false, 'msg' => 'Parámetros inválidos para exportar CSV a base de datos'];
    }

    // Crear o seleccionar la base de datos destino
    $sql = "CREATE DATABASE IF NOT EXISTS `app2026_{$prefijodb}_{$bdDestino}`";
    try {
        $conexionbd->exec($sql);
        $conexionbd->exec("USE `app2026_{$prefijodb}_{$bdDestino}`");
    } catch (PDOException $e) {
        debug("Error al crear o seleccionar la base de datos: " . $e->getMessage(), "ERROR");
        return ['ok' => false, 'msg' => 'Error al preparar la base de datos destino: ' . $e->getMessage()];
    }
    
    // Recorrer cada configuración de archivo CSV proporcionada
    foreach ($archivoCSV as $i => $config) {

        if (isset($config['nombre']) && isset($config['tabla'])) {

            // Validar que el nombre del archivo y la tabla destino sean válidos
            if (empty($config['tabla']) || $config['tabla'] === '-' || str_contains($config['tabla'], 'Selecciona')) {
                continue;
            }

            // Aquí se implementaría la lógica para leer el CSV y exportarlo a la base de datos utilizando la configuración encontrada.
            $nombreArchivoCSV = pathinfo($config['nombre'], PATHINFO_FILENAME);
            $rutaCJson = _ROOT_.DW._ASSETS_.DW._ARCHIVOSC_.DW."cliente_$idCliente".DW._CONFIG_.DW."{$nombreArchivoCSV}.json";
            $resJSON = leerJSON($rutaCJson);
            $rutaCSV = _ROOT_.DW._ASSETS_.DW._ARCHIVOSC_.DW."cliente_$idCliente".DW."{$nombreArchivoCSV}.csv";
            $resCSV = leerCSV($rutaCSV, $separador[$nombreArchivoCSV.".csv"]);
            
            // Validar que se hayan leído correctamente tanto la configuración JSON como el archivo CSV
            if ($resJSON['ok'] && $resCSV['ok']) {
                $configExportacion = $resJSON['datos'];
                $mapC = $configExportacion['columnas'];

                $filas = $resCSV['datos'] ?? [];

                $tipos = detectarTiposColumnas($filas, $mapC);
                $columnasSql = array_map(fn($col, $tipo) => "`$col` $tipo", array_keys($tipos), array_values($tipos));
                
                // Crear la tabla destino en la base de datos con las columnas y tipos detectados
                $sql = "CREATE TABLE IF NOT EXISTS `$nombreArchivoCSV` (" . implode(", ", $columnasSql) . ")";
                try {
                    $conexionbd->exec("DROP TABLE IF EXISTS `$nombreArchivoCSV`");
                    $conexionbd->exec($sql);
                } catch (PDOException $e) {
                    debug("Error al crear la tabla '$nombreArchivoCSV': " . $e->getMessage(), "ERROR");
                    return ['ok' => false, 'msg' => 'Error al crear la tabla destino: ' . $e->getMessage()];
                }
                    
                // Insertar los datos del CSV en la tabla creada utilizando el mapeo de columnas definido en la configuración
                foreach ($filas as $fila) {
                    $campos = [];
                    $valores = [];

                    // Solo insertamos las columnas que estén definidas en el mapeo y presentes en la fila del CSV
                    foreach ($mapC as $columnaCSV => $columnaBD) {

                        $valor = resolverExpresionesCSV($columnaCSV, $fila);

                        if ($valor !== null) {
                            $campos[] = "`$columnaBD`";
                            $valores[] = $conexionbd->quote($valor);
                        }
                    }

                    // Solo intentamos insertar si tenemos campos y valores válidos para evitar errores de SQL
                    if (!empty($campos) && !empty($valores)) {
                        $sqlInsert = "INSERT INTO `$nombreArchivoCSV` (" . implode(", ", $campos) . ") VALUES (" . implode(", ", $valores) . ")";
                        try {
                            $conexionbd->exec($sqlInsert);
                        } catch (PDOException $e) {
                            debug("Error al insertar datos en la tabla '$nombreArchivoCSV': " . $e->getMessage(), "ERROR");
                            return ['ok' => false, 'msg' => 'Error al insertar datos en la tabla destino: ' . $e->getMessage()];
                        }
                    }
                }
                
            }

        } else {
            debug("Configuración incompleta para archivo index $i: " . json_encode($config), "ERROR");
            return ['ok' => false, 'msg' => "Configuración incompleta para el archivo index $i"];
        }
    }

    return ['ok' => true, 'msg' => 'Exportación completada'];

}

/**
 * @brief Inserta una fila de encabezados genéricos en un archivo CSV existente, sobrescribiendo el archivo con la nueva fila añadida al principio
 * @param string $ruta Ruta del archivo CSV a modificar
 * @param array $headers Array de encabezados a insertar como primera fila del CSV
 * @param string $delimiter Delimitador utilizado en el archivo CSV (por ejemplo, ',' o ';')
 * @return void
 * Fecha de creación: 2026-03-18
 */
function insertarColumnaGenericaCSV($ruta, $headers, $delimiter) {

    $handle = fopen($ruta, "r");

    while (($fila = fgetcsv($handle, 1000, $delimiter)) !== false) {
        $datos[] = $fila;
    }
    fclose($handle);

    array_unshift($datos, $headers);

    $handle = fopen($ruta, "w");

    foreach ($datos as $fila) {
        fputcsv($handle, $fila, ",");
    }

    fclose($handle);

}

/**
 * @brief Resuelve expresiones simples en la configuración del CSV, como CONCAT(columna1, columna2), utilizando los valores de la fila actual del CSV
 * @param string $expresion Expresión a resolver, que puede ser un nombre de columna o una función con argumentos
 * @param array $fila Fila actual del CSV como un array asociativo (columna => valor)
 * @return string|null Resultado de la expresión resuelta o null si no se pudo resolver
 * Fecha de creación: 2026-03-23
 */
function resolverExpresionesCSV($expresion, $fila) {

    // Detectar si es una expresión del tipo FUNCION(arg1, arg2, ...)
    if (preg_match('/^(\w+)\((.+)\)$/', trim($expresion), $matches)) {
        $funcion = strtoupper($matches[1]);
        $args = array_map('trim', explode(',', $matches[2]));

        // Resolver cada argumento contra la fila
        $valores = array_map(fn($arg) => $fila[$arg] ?? null, $args);

        switch ($funcion) {
            case 'CONCAT':
                // Si algún valor es null, lo tratamos como string vacío
                return implode(' ', array_map(fn($v) => $v ?? '', $valores));

            // Aquí se pueden añadir más funciones en el futuro
            // case 'UPPER': return strtoupper($valores[0] ?? '');
            // case 'TRIM': return trim($valores[0] ?? '');

            default:
                debug("Función no soportada en expresión CSV: $funcion", "WARNING");
                return null;
        }
    }

    // Si no es una expresión, devolver el valor directo de la columna
    return $fila[$expresion] ?? null;
}