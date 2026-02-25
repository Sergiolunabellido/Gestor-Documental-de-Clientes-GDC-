<?php

class Sesion {
    private PDO $db;

    public function __construct(PDO $db) {
        $this->db = $db;
    }

    /**
     * @brief Registra la sesión del usuario en la tabla sesiones_usuario
     * Fecha de creación: 2026-02-03
     * @param int $idUsuario ID del usuario
     * @param string $sessionId ID de la sesión
     * @return bool True si se registró correctamente, false en caso contrario
     */

    public function crearSesion($idUsuario, $sessionId, $forzar = false) {
        // Si forzar=true, eliminar sesiones anteriores primero
        if ($forzar) {
            $sqlDelete = "DELETE FROM sesiones_usuario WHERE usuario_id = :usuarioId";
            $stmtDelete = $this->db->prepare($sqlDelete);
            $stmtDelete->execute(['usuarioId' => $idUsuario]);
        } else {
            // Verificar si ya hay una sesión activa para este usuario
            $sqlCheck = "SELECT COUNT(*) FROM sesiones_usuario WHERE usuario_id = :usuarioId";
            $stmtCheck = $this->db->prepare($sqlCheck);
            $stmtCheck->execute(['usuarioId' => $idUsuario]);
            
            if ($stmtCheck->fetchColumn() > 0) {
                // Ya existe una sesión activa para este usuario
                return false;
            }
        }

        // Crear la nueva sesión
        $userAgent = $_SERVER['HTTP_USER_AGENT'] ?? null;
        $ip = $_SERVER['REMOTE_ADDR'] ?? null;

        $sql = "
                INSERT INTO sesiones_usuario (usuario_id, session_id, user_agent, ip, last_activity, creada_en)
                VALUES (:usuarioId, :sessionId, :userAgent, :ip, NOW(), NOW())
            ";

        $stmt = $this->db->prepare($sql);
        $result = $stmt->execute([
            'usuarioId' => $idUsuario,
            'sessionId' => $sessionId,
            'userAgent' => $userAgent,
            'ip' => $ip
        ]);
        
        return $result;
    }

    /**
     * @brief Elimina la sesión del usuario
     * Fecha de creación: 2026-02-03
     * @param string $sessionId ID de la sesión
     * @return bool True si se eliminó correctamente, false en caso contrario
     */
    public function eliminarSesion($idUsuario) {
        $sql = "DELETE FROM sesiones_usuario WHERE usuario_id = :usuarioId";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute(['usuarioId' => $idUsuario]);
    }

    /**
     * @brief Elimina la sesión del usuario por última actividad
     * Fecha de creación: 2026-02-17
     * @param string $lastActivity Última actividad de la sesión
     * @return bool True si se eliminó correctamente, false en caso contrario
     */
    public function eliminarSesionPorUltimaActividad($lastActivity) {
        $sql = "DELETE FROM sesiones_usuario WHERE last_activity = :lastActivity";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute(['lastActivity' => $lastActivity]);
    }

    /**
     * @brief Elimina todas las sesiones de un usuario
     * Fecha de creación: 2026-02-09
     * @param int $usuarioId ID del usuario
     * @return bool True si se eliminó correctamente, false en caso contrario
     */
    public function eliminarSesionesPorUsuario($usuarioId) {
        $sql = "DELETE FROM sesiones_usuario WHERE usuario_id = :usuarioId";
        $stmt = $this->db->prepare($sql);
        $ok = $stmt->execute(['usuarioId' => $usuarioId]);
        if (!$ok) {
            debug("Error al eliminar sesiones del usuario: $usuarioId", "ERROR");
        }

        return $ok;
    }

    /**
     * @brief Actualiza la última actividad de la sesión
     * Fecha de creación: 2026-02-03
     * @param string $sessionId ID de la sesión
     * @param int $idUsuario ID del usuario
     * @return array|bool True si se actualizó correctamente, false en caso contrario
     */
    public function actualizarActividad($sessionId, $idUsuario) {

        $sql = "
                UPDATE sesiones_usuario
                SET last_activity = NOW()
                WHERE session_id = :sessionId
                AND usuario_id = :usuarioId
            ";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            'sessionId' => $sessionId,
            'usuarioId' => $idUsuario
        ]);

        $sql = "SELECT last_activity FROM sesiones_usuario WHERE usuario_id = :usuarioId";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['usuarioId' => $idUsuario]);
        $lastActivity = $stmt->fetchAll(PDO::FETCH_ASSOC);

        return $lastActivity;
    }

    /**
     * @brief Verifica si la sesión está activa según el tiempo de inactividad permitido
     * Fecha de creación: 2026-02-03
     * @param string $sessionId ID de la sesión
     * @param int $idUsuario ID del usuario
     * @param int|null $tiempoSegundos Tiempo de inactividad permitido en segundos (null para usar el de la sesión)
     * @return bool True si la sesión está activa, false si ha expirado
     */
    public function sessionActiva($sessionId, $idUsuario, $tiempoSegundos = null) {

        // Si no se especifica tiempo, usar el de la sesión del usuario o 30 minutos por defecto
        if ($tiempoSegundos === null) {
            $tiempoSegundos = $_SESSION['tiempo_sesion'] ?? 1800; // media hora por defecto
        }

        $fechaLimite = date('Y-m-d H:i:s', time() - $tiempoSegundos);

        $sql = 'SELECT 1
                FROM sesiones_usuario
                WHERE usuario_id = :usuarioId
                AND session_id = :sessionId
                AND last_activity > :fechaLimite';

        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            'usuarioId' => $idUsuario,
            'sessionId' => $sessionId,
            'fechaLimite' => $fechaLimite
        ]);

        return $stmt->fetchColumn() !== false;
    }

    /**
     * @brief Realiza un logout forzoso del usuario, eliminando la sesión y destruyendo la sesión PHP
     * Fecha de creación: 2026-02-03
     * @param string $sesionId ID de la sesión
     */
    public function logoutForzoso($usuarioId) {
        $this->eliminarSesion($usuarioId);
        session_unset();
        session_destroy();
        setcookie('remember', '', time() - 3600, '/');
    }

    /**
     * @brief Valida si el session_id existe en la base de datos (usado para validar sesiones después de session_regenerate_id)
     * Fecha de creación: 2026-02-09
     * @param string $sessionId ID de la sesión
     * @return bool True si el session_id es válido, false si no existe
     */
     public
    function validarSesionBD($sessionId) {

        $sql = "SELECT COUNT(*) 
                FROM sesiones_usuario 
                WHERE session_id = :sid";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            'sid' => $sessionId
        ]);

        return $stmt->fetchColumn() > 0;
    }

    /**
     * @brief Obtiene la sesión de un usuario si existe
     * Fecha de creación: 2026-02-09
     * @param int $idUsuario ID del usuario
     * @return array|false Datos de la sesión o false si no existe
     */
    public function obtenerSesionUsuario($idUsuario) {
        $sql = "SELECT session_id, last_activity 
                FROM sesiones_usuario 
                WHERE usuario_id = :usuarioId 
                LIMIT 1";

        $stmt = $this->db->prepare($sql);
        $stmt->execute(['usuarioId' => $idUsuario]);

        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * @brief Actualiza el session_id de una sesión existente (usado después de session_regenerate_id)
     * Fecha de creación: 2026-02-09
     * @param int $idUsuario ID del usuario
     * @param string $oldSessionId ID de sesión antiguo
     * @param string $newSessionId ID de sesión nuevo
     * @return bool True si se actualizó correctamente
     */
    public function actualizarSessionId($idUsuario, $oldSessionId, $newSessionId) {
        $sql = "UPDATE sesiones_usuario 
                SET session_id = :newSessionId 
                WHERE usuario_id = :usuarioId 
                AND session_id = :oldSessionId";

        $stmt = $this->db->prepare($sql);
        return $stmt->execute([
            'usuarioId' => $idUsuario,
            'oldSessionId' => $oldSessionId,
            'newSessionId' => $newSessionId
        ]);
    }

}