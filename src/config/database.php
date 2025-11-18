<?php

namespace Src\Config;

use PDO;
use PDOException;

class Database
{
    public static function conn($config)
    {
        try {
            $host = '127.0.0.1'; // atau 'localhost'
            $db   = 'apiphp';
            $user = 'root';
            $pass = ''; // kosongkan jika default Laragon
            $charset = 'utf8mb4';

            $dsn = "mysql:host=$host;dbname=$db;charset=$charset";
            $options = [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            ];

            return new PDO($dsn, $user, $pass, $options);
        } catch (PDOException $e) {
            die('Database connection failed: ' . $e->getMessage());
        }
    }
}
