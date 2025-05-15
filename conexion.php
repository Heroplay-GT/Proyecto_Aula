<?php
if (!defined('DB_CONNECTED')) {
    define('DB_CONNECTED', true);

    $host = "localhost";    
    $user = "root";        
    $password = "";           
    $database = "parkingo1";  

    $conexion = new mysqli($host, $user, $password, $database);

    if ($conexion->connect_error) {
        die("Error de conexión: " . $conexion->connect_error);
    }

    $conexion->set_charset("utf8");
}
?>
