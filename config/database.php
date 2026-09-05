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
    'driver'   => $_ENV['DB_DRIVER']   ?? 'mysql',
    'host'     => $_ENV['DB_HOST']     ?? '127.0.0.1',
    'port'     => $_ENV['DB_PORT']     ?? '3306',
    'database' => $_ENV['DB_DATABASE'] ?? 'voatelier',
    'username' => $_ENV['DB_USERNAME'] ?? 'root',
    'password' => $_ENV['DB_PASSWORD'] ?? '',
    'charset'  => $_ENV['DB_CHARSET']  ?? 'utf8mb4',
];
