
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
                $host = getenv('MYSQLHOST') ?: 'localhost';
                $port = getenv('MYSQLPORT') ?: '3306';
                $db = getenv('DB_DATABASE') ?: 'sistema_chamados';
                $user = getenv('MYSQLUSER') ?: 'root';
                $pass = getenv('MYSQLPASSWORD');

                if ($pass === false) {
                    $pass = '';
                }

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
                error_log("Erro na conexão com o banco: " . $e->getMessage());
                throw new \RuntimeException("Não foi possível conectar ao banco de dados.");
            }
        }

        return self::$instancia;
    }
}
