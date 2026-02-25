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
    $createTablaPrincipal = extraerCreateTablaSQL($contenidoSinComentarios, $tablaPrincipal);
    $alterTablaPrincipal = extraerAlterTablaSQL($contenidoSinComentarios, $tablaPrincipal);
    $insertTablaPrincipal = extraerInsertTablaSQL($contenidoSinComentarios, $tablaPrincipal);
    escribirJSON(_ROOT_.DW._ASSETS_.DW._CACHE_.DW."nombreBD.json", ['nombre' => $tablaPrincipal]);

    try {
        $conexion = getDB();
        $conexion->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    } catch (PDOException $e) {
        debug("Error de conexión a la base de datos: " . $e->getMessage(), "ERROR");
        return ['error' => 'db_connection_failed', 'msg' => 'Error de conexión a la base de datos'];
    }

    try {
        $stmtDb = $conexion->query("SELECT DATABASE() AS db_activa");
        $rowDb = $stmtDb->fetch(PDO::FETCH_ASSOC);
        $dbActiva = $rowDb['db_activa'] ?? null;
    } catch (PDOException $e) {
        debug("No se pudo consultar la base de datos activa: " . $e->getMessage(), "ERROR");
        return ['error' => 'db_select_failed', 'msg' => 'No se pudo validar la base de datos activa'];
    }

    if ($dbActiva !== _DBNAME_) {
        debug("Base de datos activa inesperada. Esperada: " . _DBNAME_ . " - Activa: " . ($dbActiva ?? 'NULL'), "ERROR");
        return ['error' => 'db_select_failed', 'msg' => 'La base de datos activa no coincide con la configurada'];
    }

    try {
        $tablaExiste = existeTablaSQL($conexion, $tablaPrincipal);

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
        $conexion->exec("DELETE FROM `$tablaPrincipal`");

        foreach ($insertTablaPrincipal as $sqlInsert) {
            $conexion->exec($sqlInsert);
        }

        $conexion->commit();

        debug("Archivo procesado exitosamente: " . $archivo['name'], "INFO");
        return ['error' => null, 'msg' => 'Archivo procesado exitosamente', 'tablas' => [$tablaPrincipal]];
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
function escribirJSON($ruta, $datos) {
    $contenido = json_encode($datos, JSON_PRETTY_PRINT);
    if ($contenido === false) {
        return ['ok' => false, 'error' => 'Error al codificar JSON: ' . json_last_error_msg()];
    }

    if (file_put_contents($ruta, $contenido) === false) {
        return ['ok' => false, 'error' => 'No se pudo escribir en el archivo'];
    }

    return ['ok' => true];
}
