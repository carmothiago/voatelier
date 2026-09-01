<?php

namespace App\Core;

use PDO;
use PDOException;

/**
 * Conexão única (singleton) com o banco de dados via PDO.
 * Sempre utiliza prepared statements — nunca concatene SQL diretamente.
 */
class Database
{
    private static ?PDO $instance = null;

    public static function getConnection(): PDO
    {
        if (self::$instance === null) {
            $config = require BASE_PATH . '/config/database.php';

            $dsn = sprintf(
                '%s:host=%s;port=%s;dbname=%s;charset=%s',
                $config['driver'],
                $config['host'],
                $config['port'],
                $config['database'],
                $config['charset']
            );

            $options = [
                PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES   => false,
            ];

            try {
                self::$instance = new PDO($dsn, $config['username'], $config['password'], $options);
            } catch (PDOException $e) {
                // Nunca exibir detalhes técnicos de conexão para o usuário final.
                error_log('Erro de conexão com o banco de dados: ' . $e->getMessage());
                http_response_code(500);
                require APP_PATH . '/views/errors/500.php';
                exit;
            }
        }

        return self::$instance;
    }
}
