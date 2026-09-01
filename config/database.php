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
    'host'     => 'mariadb',
    'port'     => '3306',
    'database' => 'voatelier',
    'username' => 'root',
    'password' => 'root',
    'charset'  => 'utf8mb4',
];
