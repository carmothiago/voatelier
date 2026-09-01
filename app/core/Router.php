<?php

namespace App\Core;

/**
 * Router simples para o padrão MVC do sistema.
 * Suporta rotas GET e POST com parâmetros nomeados, ex: /clientes/{id}
 */
class Router
{
    private array $routes = [
        'GET'  => [],
        'POST' => [],
    ];

    public function get(string $path, callable|array $handler): void
    {
        $this->routes['GET'][$path] = $handler;
    }

    public function post(string $path, callable|array $handler): void
    {
        $this->routes['POST'][$path] = $handler;
    }

    public function dispatch(string $method, string $uri): void
    {
        $method = strtoupper($method);
        $path = parse_url($uri, PHP_URL_PATH);

        // Remove o prefixo BASE_URL (ex: /voatelier) da URI recebida
        if (BASE_URL !== '' && str_starts_with($path, BASE_URL)) {
            $path = substr($path, strlen(BASE_URL));
        }

        if ($path === '' || $path === false) {
            $path = '/';
        }

        // Remove barra final duplicada (exceto raiz)
        if ($path !== '/' && str_ends_with($path, '/')) {
            $path = rtrim($path, '/');
        }

        $routesForMethod = $this->routes[$method] ?? [];

        foreach ($routesForMethod as $routePath => $handler) {
            $params = $this->match($routePath, $path);
            if ($params !== null) {
                $this->call($handler, $params);
                return;
            }
        }

        // Nenhuma rota encontrada
        http_response_code(404);
        require APP_PATH . '/views/errors/404.php';
    }

    /**
     * Verifica se a rota registrada casa com o caminho solicitado.
     * Retorna os parâmetros extraídos ou null se não houver correspondência.
     */
    private function match(string $routePath, string $requestPath): ?array
    {
        $routeParts = explode('/', trim($routePath, '/'));
        $requestParts = explode('/', trim($requestPath, '/'));

        if (count($routeParts) !== count($requestParts)) {
            return null;
        }

        $params = [];

        foreach ($routeParts as $index => $part) {
            if (preg_match('/^\{(\w+)\}$/', $part, $matches)) {
                $params[$matches[1]] = $requestParts[$index];
            } elseif ($part !== $requestParts[$index]) {
                return null;
            }
        }

        return $params;
    }

    private function call(callable|array $handler, array $params): void
    {
        if (is_array($handler)) {
            [$controllerClass, $method] = $handler;
            $controller = new $controllerClass();
            call_user_func_array([$controller, $method], $params);
            return;
        }

        call_user_func_array($handler, $params);
    }
}
