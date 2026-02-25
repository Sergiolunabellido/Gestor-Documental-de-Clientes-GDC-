<?php
/**
 * @brief Comprobamos que el usuario no exista ya en la base de datos y lo registramos
 * Fecha de creación: 2026-01-21
 * @param PDO $db Conexión a la base de datos
 * @param string $usuario Nombre de usuario
 * @param string $password Contraseña del usuario
 * @param string $email Correo electrónico del usuario
 * @param bool $esAdmin Indica si el usuario es administrador
 * @return array [bool, string] Devuelve [éxito, mensaje]
 */
function registroUsuario($db, $usuario, $password, $email, $esAdmin): array {
    try {

        if (empty($usuario) || empty($password) || empty($email)) {
            debug("Registro: datos incompletos para usuario '$usuario'", "WARNING");
            return [false, "Error: Campos incompletos"];
        }

        $stmt = $db->prepare("SELECT nombre, email FROM Usuario WHERE nombre = :usuario OR email = :email LIMIT 1");
        $stmt->execute(['usuario' => $usuario, 'email' => $email]);

        $user = $stmt->fetch();

        if ($user['nombre'] == $usuario) {
            debug("Registro: usuario '$usuario' ya existe", "WARNING");
            return [false, "Error: El usuario ya existe"];
        } else if ($user['email'] == $email) {
            debug("Registro: email '$email' ya existe para usuario '$usuario'", "WARNING");
            return [false, "Error: El email ya existe"];
        }
        
        $sql = "INSERT INTO Usuario (nombre, contrasenia, email, admin , estado) 
                VALUES (:usuario, :password, :email, :esAdmin, :estado)";

        $contraseniaC = password_hash($password, PASSWORD_DEFAULT);
        
        $esAdmin = $esAdmin ? 1 : 0;
        

        $estado = ($esAdmin == 1) ? 'activo': 'desconectado';


        $stmt = $db->prepare($sql);
        $stmt->execute([
            'usuario' => $usuario,
            'password' => $contraseniaC,
            'email' => $email,
            'esAdmin' => $esAdmin,
            'estado'=> $estado
        ]);

        return [true, "Usuario registrado correctamente"];
        
    } catch (Exception $e) {
        debug("Registro: excepción al insertar usuario '$usuario' - " . $e->getMessage(), "ERROR");
        return [false, "Error: Error al registrar usuario"];
    }
    
}
