<?php

/**
 * Vitória Oliver Atelier - Front Controller
 * Ponto de entrada único de todas as requisições HTTP.
 */

declare(strict_types=1);

require __DIR__ . '/../config/config.php';

// -----------------------------------------------------------------
// Autoload simples (PSR-4 manual, sem Composer, para manter o
// projeto 100% compatível com XAMPP sem dependências externas)
// -----------------------------------------------------------------
spl_autoload_register(function (string $class): void {
    // Namespace raiz "App\" mapeia para a pasta "app/"
    $prefix = 'App\\';

    if (!str_starts_with($class, $prefix)) {
        return;
    }

    $relativeClass = substr($class, strlen($prefix));
    // App\Core\Router -> app/core/Router.php (pasta em minúsculo)
    $parts = explode('\\', $relativeClass);
    $className = array_pop($parts);
    $folder = strtolower(implode('/', $parts));

    $file = APP_PATH . '/' . $folder . '/' . $className . '.php';

    if (file_exists($file)) {
        require $file;
    }
});

require APP_PATH . '/helpers/functions.php';

// -----------------------------------------------------------------
// Sessão segura
// -----------------------------------------------------------------
session_name(SESSION_NAME);

session_set_cookie_params([
    'lifetime' => SESSION_LIFETIME,
    'path'     => BASE_URL !== '' ? BASE_URL : '/',
    'httponly' => true,
    'samesite' => 'Lax',
    // 'secure' deve ser true quando o sistema estiver servido via HTTPS
    'secure'   => isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off',
]);

session_start();

// -----------------------------------------------------------------
// Cabeçalhos de segurança HTTP (revisão de segurança - Etapa 6)
// -----------------------------------------------------------------
header('X-Content-Type-Options: nosniff');       // impede o navegador de "adivinhar" tipos de arquivo
header('X-Frame-Options: SAMEORIGIN');            // impede que o sistema seja carregado dentro de um <iframe> de outro site (clickjacking)
header('Referrer-Policy: same-origin');           // não vaza a URL completa em requisições para fora do sistema
header('X-XSS-Protection: 0');                    // desativa o filtro legado do navegador (obsoleto e às vezes explorável); a proteção real vem do escaping consistente (htmlspecialchars) já aplicado em todas as views

// -----------------------------------------------------------------
// Roteamento
// -----------------------------------------------------------------
use App\Core\Router;

$router = new Router();
require BASE_PATH . '/routes/web.php';

try {
    $router->dispatch($_SERVER['REQUEST_METHOD'], $_SERVER['REQUEST_URI']);
} catch (\Throwable $e) {
    error_log('Erro não tratado: ' . $e->getMessage() . ' em ' . $e->getFile() . ':' . $e->getLine());
    http_response_code(500);
    require APP_PATH . '/views/errors/500.php';
}
