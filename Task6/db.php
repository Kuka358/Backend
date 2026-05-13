<?php
// db.php
$db_host = 'localhost';
$db_name = 'u82285';
$db_user = 'u82285';
$db_pass = '9623711';

function getDB(): PDO {
    static $pdo = null;
    if ($pdo === null) {
        global $db_host, $db_name, $db_user, $db_pass;
        $pdo = new PDO("mysql:host=$db_host;dbname=$db_name;charset=utf8mb4", $db_user, $db_pass, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false
        ]);
    }
    return $pdo;
}