<?php
/**
 * @brief Comprueba que el usuario sea admin
 * Fecha de creación: 2026-01-26
 * @param PDO $db Conexión a la base de datos
 * @param string $usuario Nombre de usuario
 * @return bool 
 */
function comprobarAdmin($db, $usuario) {
    $sql = "SELECT admin
            FROM Usuario
            WHERE nombre = :usuario
            LIMIT 1";

    $stmt = $db->prepare($sql);
    $stmt->execute([
        'usuario' => $usuario
    ]);

    $user = $stmt->fetch();

    if ($user && $user['admin'] == 0) {
        return 0;
    }

    return 1;
}
/**
 * @brief Comprobamos las credenciales del usuario para iniciar sesión
 * Fecha de creación: 2026-01-21
 * @param PDO $db Conexión a la base de datos
 * @param string $usuario Nombre de usuario o email
 * @param string $password Contraseña del usuario
 * @return array|bool
 */
function loginUsuario($db, $usuario, $password) {
    // Comprobar si el usuario es un email o un nombre de usuario
    if (strpos($usuario, '@') !== false) {
        $sql = "SELECT *
                FROM Usuario
                WHERE email = :usuario
                LIMIT 1";
    } else {
        $sql = "SELECT *
                FROM Usuario
                WHERE nombre = :usuario
                LIMIT 1";
    }

    $stmt = $db->prepare($sql);
    $stmt->execute([
        'usuario' => $usuario
    ]);

    $user = $stmt->fetch();

    if (!$user) {
        debug("Login: usuario no encontrado - $usuario", "INFO");
        return false;
    }
    
    if (password_verify($password, $user['contrasenia'])) {
        unset($user['contrasenia']);
        return $user;
    }

    debug("Login: contraseña incorrecta para usuario - $usuario", "WARNING");
    return false;
}
/**
 * @brief Iniciar sesión con cookie de "Recuérdame"
 * Fecha de creación: 2026-01-21
 * @param PDO $db
 * @return bool
 */

function loginConCookie($db) {

    if (empty($_COOKIE['remember']) || $_COOKIE['remember'] === false) {
        return false;
    }

    $token = $_COOKIE['remember'];

    $sql = "
        SELECT *
        FROM Usuario
        WHERE token_recordarme = :token
        LIMIT 1
    ";

    $stmt = $db->prepare($sql);
    $stmt->execute([
        'token' => $token
    ]);

    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$user) {
        debug("LoginConCookie: token inválido o expirado", "WARNING");
        setcookie('remember', '', time() - 3600, '/');
        return false;
    }

    if ($user['deleted'] == 1) {
        debug("LoginConCookie: usuario eliminado - " . $user['nombre'], "WARNING");
        setcookie('remember', '', time() - 3600, '/');
        return false;
    }

    $_SESSION['login']   = true;
    $_SESSION['usuario'] = $user['nombre'];
    $_SESSION['idUsuario'] = $user['id'];
    $_SESSION['admin']   = $user['admin'];
    $_SESSION['foto']    = $user['foto_perfil'] ?? null;

    $tiempoSesion = $user['temporizador_sesion'] ?? 1800;

    $_SESSION['tiempo_sesion'] = $tiempoSesion;
    $_SESSION['inicio_sesion'] = time();

    // Crear sesión en la BD forzando eliminación de sesiones anteriores
    include_once _ROOT_.DW._LIB_.DW."sesion.php";
    $sesionModel = new Sesion($db);
    $sesionModel->crearSesion($user['id'], session_id(), true);

    return true;
}
/**
 * @brief Crear token para "Recuérdame" y establecer cookie
 * Fecha de creación: 2026-01-21
 * @param PDO $db
 * @param string $nombreUsuario
 * @return string Token generado
 */

function crearRecuerdameToken($db, $nombreUsuario) {
    $token = bin2hex(random_bytes(32));

    $sql = "
        UPDATE Usuario
        SET token_recordarme = :token
        WHERE nombre = :usuario
    ";

    $stmt = $db->prepare($sql);
    $stmt->execute([
        'token'   => $token,
        'usuario' => $nombreUsuario
    ]);

    setcookie(
        'remember',
        $token,
        time() + (30 * 24 * 60 * 60),
        '/',
        '',
        false,
        false // HttpOnly
    );

    return $token;
}

/**
 * @brief Cambia la contraseña del usuario
 * Fecha de creación: 2026-01-26
 * @param PDO $db
 * @param int $idUsuario
 * @param string $nuevaContrasenia
 * @return bool
 */
function cambiarContrasenia($db, $idUsuario, $nuevaContrasenia, $usuario) {

    if (empty($usuario)){
        debug("CambiarContrasenia: usuario vacío", "ERROR");
        return false;
    }

    $hash = password_hash($nuevaContrasenia, PASSWORD_BCRYPT);

    $sql = "UPDATE Usuario SET contrasenia = :contrasenia WHERE nombre = :usuario";
    $stmt = $db->prepare($sql);
    $stmt->execute([
        'contrasenia' => $hash,
        'usuario'     => $usuario
    ]);

    if ($stmt->rowCount() === 0) {
        debug("CambiarContrasenia: no se pudo cambiar la contraseña para usuario: $usuario", "ERROR");
        return false;
    }

    return true;
}

/**
 * @brief Actualiza el estado del usuario (activo, desconectado, etc.)
 * Fecha de creación: 2026-02-10
 * @param PDO $db
 * @param int $idUsuario
 * @param string $estado Nuevo estado del usuario
 * @return bool
 */
function actualizarEstadoUsuario($db, $idUsuario, $estado) {
    if (empty($idUsuario)){
        debug("ActualizarEstadoUsuario: ID de usuario vacío", "ERROR");
        return false;
    }
    if (empty($estado)){
        debug("ActualizarEstadoUsuario: estado vacío", "ERROR");
        return false;
    }

    $sql = "UPDATE Usuario SET estado = :estado WHERE id = :idUsuario";
    $stmt = $db->prepare($sql);
    $stmt->execute([
        'estado'    => $estado,
        'idUsuario' => $idUsuario
    ]);
    if ($stmt->rowCount() === 0) {
        debug("ActualizarEstadoUsuario: no se pudo actualizar el estado para usuario: $idUsuario", "ERROR");
        return false;
    }
    return true;
}



