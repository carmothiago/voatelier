<?php
/**
 * Vitória Oliver Atelier - Configuração de banco de dados
 *
 * Ajuste os valores abaixo conforme o seu ambiente XAMPP.
 * Por padrão, o XAMPP usa usuário "root" sem senha.
 *
 * Em uma instalação real, crie um usuário MySQL dedicado
 * (não use root) com permissões apenas sobre o banco "voatelier".
 */

return [
    'driver'   => 'mysql',
    'host'     => '127.0.0.1',
    'port'     => '3306',
    'database' => 'voatelier',
    'username' => 'root',
    'password' => '',
    'charset'  => 'utf8mb4',
];
