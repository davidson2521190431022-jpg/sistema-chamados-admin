<?php

namespace App\Config;

use PDO;

class Conexao
{
    private static $instancia = null;

    public static function getConexao()
    {
        if (self::$instancia === null) {
            try {

                $host = getenv('MYSQLHOST');
                $port = getenv('MYSQLPORT') ?: '3306';
                $db   = getenv('MYSQLDATABASE');
                $user = getenv('MYSQLUSER');
                $pass = getenv('MYSQLPASSWORD');

                $dsn = "mysql:host={$host};port={$port};dbname={$db};charset=utf8mb4";

                $options = [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_EMULATE_PREPARES => false,
                ];

                self::$instancia = new PDO(
                    $dsn,
                    $user,
                    $pass,
                    $options
                );

            } catch (\PDOException $e) {
                throw new \PDOException(
                    $e->getMessage(),
                    (int) $e->getCode()
                );
            }
        }

        return self::$instancia;
    }
}