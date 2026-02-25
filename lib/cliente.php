<?php

class Cliente {
    private PDO $db;

    public function __construct(PDO $db) {
        $this->db = $db;
    }

    /**
     * @brief Obtiene todos los clientes
     * Fecha de creación: 2026-02-16
     * @return array Devuelve un array con los datos de los clientes
     */
    public function obtenerClientes() {
        $sql = "SELECT * FROM Cliente WHERE deleted = 0";
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * @brief Obtiene un cliente por su ID
     * Fecha de creación: 2026-02-17
     * @param int $id_cliente ID del cliente
     * @return array Devuelve un array con los datos del cliente o false si no se encuentra
     */
    public function obtenerClienteId($id_cliente) {
        $sql = "SELECT * FROM Cliente WHERE deleted = 0 AND id = :clienteId";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['clienteId' => $id_cliente]);
        $cliente = $stmt->fetchAll(PDO::FETCH_ASSOC);
        return $cliente;
    }

    /**
     * @brief Elimina un cliente por su ID (marcar como eliminado)
     * Fecha de creación: 2026-02-17
     * @param int $idCliente ID del cliente a eliminar
     * @return bool|string Devuelve true si se eliminó correctamente, o un mensaje de error en caso contrario
     */
    public function eliminarCliente(int $idCliente): ?string {

        try {

            $sqlGetSessionId = "SELECT id FROM Cliente WHERE id = :clienteId AND deleted = 0";
            $stmtGetSessionId = $this->db->prepare($sqlGetSessionId);
            $stmtGetSessionId->execute(['clienteId' => $idCliente]);
            $stmtGetSessionId->fetch(PDO::FETCH_ASSOC);
            
        } catch (PDOException $e) {

            debug("ERROR PDO: " . $e->getMessage(), "ERROR");
        }
        // Luego, marcar el usuario como eliminado
        $sql = "UPDATE Cliente
                SET deleted = 1
                WHERE id = :clienteId";
        $stmt = $this->db->prepare($sql);

        return $stmt->execute(['clienteId' => $idCliente]);;
    }

    /**
     * @brief Agrega un nuevo cliente a la base de datos
     * Fecha de creación: 2026-02-17
     * @param string $nombre
     * @param string $telefono
     * @return array<bool|string> Devuelve un array con el resultado de la operación y un mensaje de error si es necesario
     */
    public function agregarCliente(string $nombre, string $telefono): array {
        try {
            // Comprobar si ya existe el cliente por nombre o teléfono
            $sqlCheck = "SELECT nombre, telefono 
                        FROM Cliente 
                        WHERE (nombre = :nombre OR telefono = :telefono)
                        AND deleted = 0";
            
            $stmtCheck = $this->db->prepare($sqlCheck);
            $stmtCheck->execute([
                'nombre' => $nombre,
                'telefono' => $telefono
            ]);

            $clienteExistente = $stmtCheck->fetch(PDO::FETCH_ASSOC);

            if ($clienteExistente) {

                if ($clienteExistente['nombre'] === $nombre) {
                    debug("Registro: cliente '$nombre' ya existe", "WARNING");
                    return [false, "Error: El cliente ya existe"];
                }

                if ($clienteExistente['telefono'] === $telefono) {
                    debug("Registro: teléfono '$telefono' ya existe", "WARNING");
                    return [false, "Error: El teléfono ya existe"];
                }
            }

            // Insertar cliente
            $sql = "INSERT INTO Cliente (nombre, telefono) 
                    VALUES (:nombre, :telefono)";
            
            $stmt = $this->db->prepare($sql);
            $stmt->execute([
                'nombre' => $nombre,
                'telefono' => $telefono
            ]);

            return [true, "Cliente registrado correctamente"];

        } catch (PDOException $e) {
            debug("ERROR PDO: " . $e->getMessage(), "ERROR");
            return [false, "Error: No se pudo registrar el cliente"];
        }
    }

    
}