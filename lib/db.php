<?php
function getDB() {
    static $db = null;

    if ($db === null) {
        try {
            $db = new PDO(
                "mysql:host="._SERVER_.";dbname="._DBNAME_.";charset=utf8",
                _USER_,
                _PASSWORD_,
                [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_AUTOCOMMIT => true  // FORZAR AUTOCOMMIT
                ]
            );

            if (function_exists('debug')) {
                
            }

        } catch (PDOException $e) {
            if (function_exists('debug')) {
                debug("Error conexión BD: " . $e->getMessage(), "ERROR");
            }
            die("Error de conexión BD");
        }
    }

    return $db;
}