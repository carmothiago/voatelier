<?php

/**
 * Vitória Oliver Atelier — Configuração de banco de dados
 *
 * Os valores são lidos do arquivo .env na raiz do projeto.
 * Nunca coloque credenciais diretamente aqui.
 *
 * Para configurar o ambiente local:
 *   1. Copie .env.example para .env
 *   2. Preencha DB_USERNAME e DB_PASSWORD com suas credenciais MySQL
 */

return [
<<<<<<< HEAD
    'driver'   => $_ENV['DB_DRIVER']   ?? 'mysql',
    'host'     => $_ENV['DB_HOST']     ?? '127.0.0.1',
    'port'     => $_ENV['DB_PORT']     ?? '3306',
    'database' => $_ENV['DB_DATABASE'] ?? 'voatelier',
    'username' => $_ENV['DB_USERNAME'] ?? '',
    'password' => $_ENV['DB_PASSWORD'] ?? '',
    'charset'  => $_ENV['DB_CHARSET']  ?? 'utf8mb4',
=======
    'driver'   => 'mysql',
    'host'     => 'mariadb',
    'port'     => '3306',
    'database' => 'voatelier',
    'username' => 'root',
    'password' => 'root',
    'charset'  => 'utf8mb4',
>>>>>>> c05301cc3a2b99ed089f573267258ef51f6a6d81
];
