<?php
/**
 * @brief Cambia la foto de perfil del usuario
 * Fecha de creación: 2026-02-10
 * @param PDO $db Conexión a la base de datos
 * @param int $idUsuario Id del usuario
 * @param array $archivo Información del archivo subido
 * @return string|array Ruta de la nueva foto o array con error si falla
 */
// Comentado para evitar que los warnings rompan la respuesta JSON
//ini_set('display_errors', 1);
//ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
function cambiarFotoPerfil($db, $idUsuario, $archivo) {
    
    if (!$idUsuario) {
        return ['error' => 'id_invalido', 'msg' => 'ID de usuario inválido'];
    }

    $permitido = ['image/jpeg', 'image/png', 'image/webp', 'image/jpg'];
    if (!in_array($archivo['type'], $permitido)) {
        if (function_exists('debug')) {
            debug("Formato de archivo no permitido: " . $archivo['type'], "WARNING");
        }
        return ['error' => 'formato_invalido', 'msg' => 'Formato no permitido: ' . $archivo['type']];
    }
    if ($archivo['size'] > 5 * 1024 * 1024) {
        if (function_exists('debug')) {
            debug("Archivo demasiado grande: " . $archivo['size'], "WARNING");
        }
        return ['error' => 'tamaño_excedido', 'msg' => 'Tamaño demasiado grande: ' . ($archivo['size'] / 1024 / 1024) . 'MB'];
    }
    
    // Crear directorio si no existe
    $dirDestino = _ROOT_.DW._ASSETS_.DW._IMAGES_.DW."user_{$idUsuario}_{$_SESSION['usuario']}".DW;
    
    if (!is_dir($dirDestino)) {
        if (!mkdir($dirDestino, 0755, true)) {
            debug("No se pudo crear directorio de foto: $dirDestino", "ERROR");
            return ['error' => 'mkdir_fallo', 'msg' => 'No se pudo crear el directorio: ' . $dirDestino];
        }
    }

    $ext = pathinfo($archivo['name'], PATHINFO_EXTENSION);
    $nuevoNombre = "user_{$idUsuario}_{$_SESSION['usuario']}." . $ext;
    $destino = $dirDestino . $nuevoNombre;

    // Redimensionar antes de guardar la ruta y mover el archivo
    $anchoFinal = 270;
    $altoFinal = 220;

    if (!redimensionarImagen($archivo['tmp_name'], $destino, $anchoFinal, $altoFinal)) {
        debug("Error al redimensionar la imagen", "ERROR");
        return ['error' => 'resize_failed', 'msg' => 'No se pudo redimensionar la imagen'];
    }

    $ruta = _ASSETS_.DW._IMAGES_.DW."user_{$idUsuario}_{$_SESSION['usuario']}".DW.$nuevoNombre;
    $sql = "UPDATE Usuario SET foto_perfil = :foto WHERE id = :id";
    $stmt = $db->prepare($sql);
    $stmt->execute([
        'foto' => $ruta,
        'id'   => $idUsuario
    ]);

    $_SESSION['foto'] = $ruta;
    
    return $ruta;
}

/**
 * @brief Redimensiona una imagen manteniendo la proporción
 * Fecha de creación: 2026-02-02
 * @param string $rutaOrigen Ruta de la imagen original
 * @param string $rutaDestino Ruta donde se guardará la imagen redimensionada
 * @param int $nuevoAncho Nuevo ancho deseado
 * @param int $nuevoAlto Nuevo alto deseado
 * @return bool True si se redimensionó correctamente, false en caso contrario
 */

function redimensionarImagen($rutaOrigen, $rutaDestino, $nuevoAncho, $nuevoAlto) {
    list($anchoOriginal, $altoOriginal, $tipo) = getimagesize($rutaOrigen);

    switch ($tipo) {
        case IMAGETYPE_JPEG:
            $imagenOriginal = imagecreatefromjpeg($rutaOrigen);
            break;
        case IMAGETYPE_PNG:
            $imagenOriginal = imagecreatefrompng($rutaOrigen);
            break;
        case IMAGETYPE_WEBP:
            $imagenOriginal = imagecreatefromwebp($rutaOrigen);
            break;
        default:
            return false;
    }

    $imagenRedim = imagecreatetruecolor($nuevoAncho, $nuevoAlto);

    if ($tipo == IMAGETYPE_PNG || $tipo == IMAGETYPE_WEBP) {
        imagealphablending($imagenRedim, false);
        imagesavealpha($imagenRedim, true);
    }

    imagecopyresampled($imagenRedim, $imagenOriginal, 0, 0, 0, 0, $nuevoAncho, $nuevoAlto, $anchoOriginal, $altoOriginal);

    switch ($tipo) {
        case IMAGETYPE_JPEG:
            imagejpeg($imagenRedim, $rutaDestino, 90);
            break;
        case IMAGETYPE_PNG:
            imagepng($imagenRedim, $rutaDestino);
            break;
        case IMAGETYPE_WEBP:
            imagewebp($imagenRedim, $rutaDestino, 90);
            break;
    }

    // PHP 8.5+ libera automáticamente los objetos GdImage, no es necesario llamar a imagedestroy()

    return true;
}
?>