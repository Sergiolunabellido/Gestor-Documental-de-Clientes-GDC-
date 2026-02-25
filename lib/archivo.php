<?php

class Archivo {
    private PDO $db;

    public function __construct(PDO $db){
        $this->db = $db;
    }

    /**
     * @brief Obtiene los archivos asociados a un cliente
     * Fecha de creación: 2026-02-18
     * @param int $id_cliente ID del cliente
     * @return array Devuelve un array con los datos de los archivos del cliente
     */
    public function regogerArchivosCliente($id_cliente){

        $sql = "SELECT * FROM Archivo WHERE id_Cliente = :clienteId";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['clienteId' => $id_cliente]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);

    }

    /**
     * @brief Crea o añade un archivo en el directorio del cliente
     * @param int $idCliente ID del cliente
     * @param array $archivo Información del archivo
     * @return array{error: string, msg: string|array{msg: string, success: bool}}
     * Fecha de creación: 2026-02-18
     */
    public function agregarAchivo($idCliente, $archivo) {

        $baseDir = _ROOT_.DW._ASSETS_.DW._ARCHIVOSC_.DW;

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

        $dir_destino = $baseDir . "cliente_{$idCliente}" . DW;

        if (!is_dir($dir_destino)) {
            if (!mkdir($dir_destino, 0755, true)) {
                debug("No se pudo crear el directorio para el cliente: $dir_destino", "ERROR");
                return ['error' => 'mkdir_fallo', 'msg' => 'No se pudo crear el directorio: ' . $dir_destino];
            }
        }

        $nuevoNombre = basename($archivo['name']);
        $destino = $dir_destino . $nuevoNombre;

        if (!move_uploaded_file($archivo['tmp_name'], $destino)) {
            debug("Error al mover el archivo subido: " . $archivo['name'], "ERROR");
            return ['error' => 'move_failed', 'msg' => 'No se pudo guardar el archivo'];
        }

        $ruta = _ASSETS_.DW._ARCHIVOSC_.DW."cliente_{$idCliente}".DW.$nuevoNombre;

        $sql = "SELECT * FROM Archivo WHERE nombre = :nombre AND id_Cliente = :id_Cliente";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            'nombre' => $nuevoNombre,
            'id_Cliente'   => $idCliente
        ]);
        
        if ($stmt->fetch(PDO::FETCH_ASSOC)) {
            debug("Archivo ya existe en la base de datos: $nuevoNombre para cliente ID: $idCliente", "ERROR");
            return ['error' => 'archivo_existente', 'msg' => 'Ya existe un archivo con ese nombre para este cliente'];
        }

        $sql = "INSERT INTO Archivo (nombre, ruta, id_Cliente) VALUES (:nombre, :ruta, :id_Cliente)";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            'nombre' => $nuevoNombre,
            'ruta' => $ruta,
            'id_Cliente'   => $idCliente
        ]);

        return ['success' => true, 'msg' => "Archivo subido correctamente"];
    }

    /**
     * @brief Elimina un archivo tanto del sistema de archivos como de la base de datos
     * @param int $idArchivo
     * @return array{error: string, msg: string|array{success: bool, msg: string}}
     * Fecha de creación: 2026-02-23
     */
    function eliminarArchivo($idArchivo, $idCliente) {
        $sql = "SELECT * FROM Archivo WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['id' => $idArchivo]);
        $archivo = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$archivo) {
            debug("Archivo no encontrado con ID: $idArchivo", "ERROR");
            return ['error' => 'archivo_no_encontrado', 'msg' => 'Archivo no encontrado'];
        }

        $rutaCompleta = _ROOT_ . DW . $archivo['ruta'];

        if (file_exists($rutaCompleta)) {
            if (!unlink($rutaCompleta)) {
                debug("Error al eliminar el archivo del sistema: " . $rutaCompleta, "ERROR");
                return ['error' => 'eliminar_fallo', 'msg' => 'No se pudo eliminar el archivo del sistema'];
            }
        } else {
            debug("Archivo no encontrado en el sistema: " . $rutaCompleta, "WARNING");
        }

        $sql = "DELETE FROM Archivo WHERE id = :id AND id_Cliente = :idCliente";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['id' => $idArchivo, 'idCliente' => $idCliente]);

        return ['success' => true, 'msg' => 'Archivo eliminado correctamente'];
    }

    /**
     * @brief Elimina todos los archivos asociados a un cliente, tanto del sistema de archivos como de la base de datos
     * @param int $idCliente ID del cliente
     * @return array{success: bool, msg: string} Resultado de la operación
     * Fecha de creación: 2026-02-25
     */
    public function eliminarArchivosCliente(int $idCliente): array {
        $sql = "SELECT ruta FROM Archivo WHERE id_Cliente = :idCliente";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['idCliente' => $idCliente]);
        $rutasA = $stmt->fetchAll(PDO::FETCH_ASSOC);

        foreach ($rutasA as $archivo) {
            $rutaCompleta = _ROOT_ . DW . $archivo['ruta'];
            if (file_exists($rutaCompleta)) {
                if (!unlink($rutaCompleta)) {
                    debug("Error al eliminar el archivo del sistema: " . $rutaCompleta, "ERROR");
                    return ['success' => false, 'msg' => 'No se pudo eliminar el archivo del sistema'];
                }
            } else {
                debug("Archivo no encontrado en el sistema: " . $rutaCompleta, "WARNING");
            }
        }

        $sql = "DELETE FROM Archivo WHERE id_Cliente = :idCliente";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['idCliente' => $idCliente]);

        return ['success' => true, 'msg' => 'Archivos del cliente eliminados correctamente'];
    }
}
