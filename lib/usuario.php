<?php



class Usuario {

    private PDO $db;

    public function __construct(PDO $db) {
        $this->db = $db;
    }

    /**
     * @brief Busca un usuario con ese nombre en la base de datos
     * Fecha de creación: 2026-01-27
     * @param string $nombre
     * @return array|null Devuelve un array asociativo con los datos del usuario o null si no existe
     */
    public function obtenerPorNombre(String $nombre): ?array {
        $sql = "SELECT *
            FROM Usuario
            WHERE nombre = :nombre
            LIMIT 1";

        $stmt = $this->db->prepare($sql);
        $stmt->execute(['nombre' => $nombre]);

        return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
    }

    /**
     * @brief Busca un usuario con ese email en la base de datos
     * Fecha de creación: 20206-01-27
     * @param string $email
     * @return array|null Devuelve un array asociativo con los datos del usuario o null si no existe
     */
    public function obtenerPorEmail(String $email): ?array {
        $sql = "SELECT *
            FROM Usuario
            WHERE email = :email
            LIMIT 1";

        $stmt = $this->db->prepare($sql);
        $stmt->execute(['email' => $email]);

        return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
    }
    
    /**
     * @brief Busca un usuario por ese id en la base de datos
     * Fecha de cración: 2026-01-27
     * @param int $id
     * @return array|null Devuelve un array asociativo con los datos del usuario o null si no existe
     */
    public function obtenerPorId(int $id): ?array {
        $sql = "SELECT *
            FROM Usuario
            WHERE id = :id
            LIMIT 1";

        $stmt = $this->db->prepare($sql);
        $stmt->execute(['id' => $id]);

        return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
    }

    /**
     * @brief Verifica si las credenciales de login son correctas
     * Fecha de creación: 2026-01-27
     * @param string $nombre
     * @param string $contrasena
     * @return bool Devuelve true si las credenciales son correctas, false en caso contrario
     */
    public function verificarLogin(string $nombre, string $contrasena): bool {

        $usuario = $this->obtenerPorNombre($nombre);

        if ($usuario && password_verify($contrasena, $usuario['contrasena'])) {
            return true;
        }

        return false;
    }

    /**
     * @brief Obtiene la lista de todos los usuarios
     * Fecha de creación: 2026-01-27
     * @return array Devuelve un array de arrays asociativos con los datos de los usuarios
     */
    public function obtenerListaUsuarios(): array {
        $sql = "SELECT id, nombre, email, foto_perfil, timestamp, estado
                FROM Usuario WHERE deleted = 0";

        $stmt = $this->db->prepare($sql);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * @brief Obtiene de la lista de usuarios el usuario con el nombre dado
     * Fecha de creación: 2026-01-27
     * @param string $nombre Nombre del usuario a excluir
     * @return array Devuelve un array de arrays asociativos con los datos de los usuarios
     */
    public function obtenerListaUsuariosNombre($nombre): array {
        $sql = "SELECT id, nombre, email, foto_perfil, timestamp, estado
                FROM Usuario WHERE LOWER(nombre) LIKE :nombre AND deleted = 0";

        $stmt = $this->db->prepare($sql);
        $stmt->execute(['nombre' => strtolower($nombre) . '%']);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }


    /**
     * @brief Obtiene de la lista de usuarios el usuario con el nombre dado
     * Fecha de creación: 2026-01-27
     * @param string $nombre Nombre del usuario a excluir
     * @return array Devuelve un array de arrays asociativos con los datos de los usuarios
     */
    public function obtenerListaUsuariosCorreo($correo): array {
        $sql = "SELECT id, nombre, email, foto_perfil, timestamp, estado
                FROM Usuario WHERE email LIKE :correo AND deleted = 0";

        $stmt = $this->db->prepare($sql);
        $stmt->execute(['correo' => $correo . '%']);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    

     /**
     * @brief Obtiene de la lista de usuarios el usuario con el nombre dado
     * Fecha de creación: 2026-01-27
     * @param string $nombre Nombre del usuario a excluir
     * @return array Devuelve un array de arrays asociativos con los datos de los usuarios
     */
    public function obtenerListaUsuariosFecha($fecha): array {
        $sql = "SELECT id, nombre, email, foto_perfil, timestamp, estado
                FROM Usuario WHERE DATE(timestamp) = :fecha AND deleted = 0";

        $stmt = $this->db->prepare($sql);
        $stmt->execute(['fecha' => $fecha->format('Y-m-d')]);
       

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }


    /**
     * @deprecated El nuevo metodo esta en la clase sesion y se llama crearSesion
     * @brief Registra la sesión del usuario en la base de datos
     * Fecha de creación: 2026-01-28
     * @param int $idUsuario ID del usuario
     * @param string $sessionId ID de la sesión
     * @return bool True si se registró correctamente, false en caso contrario
     */

    public function registrarSesion($idUsuario, $sessionId) {
        // Comprobar si ya hay una sesión activa
        $sql = "SELECT session_id FROM Usuario WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['id' => $idUsuario]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!empty($user['session_id'])) {
            // Ya hay sesión activa, bloquear
            return false;
        }

        $sql = "UPDATE Usuario SET session_id = :sid WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([
            'sid' => $sessionId,
            'id'  => $idUsuario
        ]);
    }

    /**
     * @brief Elimina el token de "recordarme" del usuario
     * Fecha de creación: 2026-01-28
     * @param int $idUsuario ID del usuario
     * @return bool True si se eliminó correctamente, false en caso contrario
     */
    public function eliminarTokenRecordarme($idUsuario) {
        $stmt = $this->db->prepare("
            UPDATE Usuario 
            SET token_recordarme = NULL 
            WHERE nombre = :nombre
        ");
        return $stmt->execute(['nombre' => $_SESSION['usuario'] ?? '']);
    }

    /**
     * @deprecated El nuevo metodo esta en la clase sesion
     * @brief Elimina el session_id del usuario
     * Fecha de creación: 2026-01-28
     * @param int $idUsuario ID del usuario
     * @return bool True si se eliminó correctamente, false en caso contrario
     */
    public function eliminarSesionId($idUsuario) {
        $sql = "UPDATE Usuario SET session_id = NULL WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute(['id' => $idUsuario]);
    }

    /**
     * @brief Modifica los datos de un usuario por su nombre
     * Fecha de creación: 2026-02-02
     * @param string $nombre Nombre del usuario a modificar
     * @param array $cambios Array asociativo con los campos a modificar y sus nuevos valores
     * @return bool True si se modificó correctamente, false en caso contrario
     */
    public function modificarUsuarioPorNombre($usuario, $cambios) {
        $setParts = [];
        $params = [];

        // Reccorremos los cambios de un array asociativo campo => valor
        foreach ($cambios as $campo => $valor) {
            $setParts[] = "$campo = :$campo";
            $params[$campo] = $valor;
        }

        $params['usuario'] = $usuario;
        $setClause = implode(", ", $setParts);

        $sql = "UPDATE Usuario SET $setClause WHERE nombre = :usuario";

        $stmt = $this->db->prepare($sql);
        return $stmt->execute($params);
    }

    /**
     * @brief Libera un usuario que estaba siendo modificado por un administrador
     * Fecha de creación: 2026-02-02
     * @param int $idAdmin ID del administrador que libera el usuario
     * @param int $idUsuario ID del usuario a liberar
     * @return bool True si se liberó correctamente, false en caso contrario
     */

    public function noModificandoUsuario($idUsuario) {
        // Verificar estado actual antes de actualizar
        $ruta = _ROOT_.DW._ASSETS_.DW._CACHE_.DW."jsonBloqueados.json";

        if (!is_dir(dirname($ruta))) {
            mkdir(dirname($ruta), 0755, true);
        }

        if (!file_exists($ruta)) {
            file_put_contents($ruta, json_encode([]));
        }

        $clave = 'Usuario|' . $idUsuario;

        $json = file_get_contents($ruta);
        $datos = json_decode($json, true);

        if (isset($datos[$clave])) {

            unset($datos[$clave]);
            file_put_contents($ruta, json_encode($datos), LOCK_EX);

            return true;

        } else {

            return false;

        }
    }

    /**
     * @brief Marca un usuario como siendo modificado por un administrador
     * Fecha de creación: 2026-02-02
     * @param int $idAdmin ID del administrador que modifica el usuario
     * @param int $idUsuario ID del usuario a modificar
     * @return bool True si se marcó correctamente, false en caso contrario
     */

    public function modificandoUsuario($idUsuario) {

        $ruta = _ROOT_.DW._ASSETS_.DW._CACHE_.DW."jsonBloqueados.json";

        if (!is_dir(dirname($ruta))) {
            mkdir(dirname($ruta), 0755, true);
        }

        if (!file_exists($ruta)) {
            file_put_contents($ruta, json_encode([]));
        }

        $tiempo = time();
        $tiempoLimite = 600;
        $clave = 'Usuario|' . $idUsuario;

        $json = file_get_contents($ruta);
        $datos = json_decode($json, true);

        if (isset($datos[$clave])) {

            $tiempoA = $datos['Usuario|' . $idUsuario];

            if ($tiempoA + $tiempoLimite > $tiempo) {  
                debug("Usuario $idUsuario ya está siendo modificado por otro administrador", "ERROR");
                return false;
            }
        }

        $datos[$clave] = $tiempo;
        file_put_contents($ruta, json_encode($datos), LOCK_EX);

        return true;

    }


    /**
     * @brief Marca un usuario como eliminado y devuelve su session_id si lo tiene.
     * @param int $idUsuario ID del usuario a eliminar.
     * @return string|null Devuelve el session_id del usuario si se eliminó correctamente, null en caso contrario.
     * Fecha de creación: 2026-02-09
     */
    public function eliminarUsuario(int $idUsuario): ?string {

        try {

            $sqlGetSessionId = "SELECT id FROM Usuario WHERE id = :usuarioId AND deleted = 0";
            $stmtGetSessionId = $this->db->prepare($sqlGetSessionId);
            $stmtGetSessionId->execute(['usuarioId' => $idUsuario]);
            $stmtGetSessionId->fetch(PDO::FETCH_ASSOC);
            
        } catch (PDOException $e) {

            debug("ERROR PDO: " . $e->getMessage(), "ERROR");
        }
        // Luego, marcar el usuario como eliminado
        $sql = "UPDATE Usuario
                SET deleted = 1
                WHERE id = :usuarioId";
        $stmt = $this->db->prepare($sql);

        return $stmt->execute(['usuarioId' => $idUsuario]);;
    }

    /**
     * @brief Obtiene el ID de un usuario por su nombre
     * @param string $nombre Nombre del usuario
     * @return int|null Devuelve el ID del usuario si se encuentra, null en caso contrario
     * Fecha de creación: 2026-02-17
     */

    public function obtenerIdUsuarioPorNombre($nombre): ?int {
        $sql = "SELECT id FROM Usuario WHERE nombre = :nombre AND deleted = 0";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['nombre' => $nombre]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);

        return $result ? (int)$result['id'] : null;
    }

}
