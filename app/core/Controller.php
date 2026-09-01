<?php

namespace App\Core;

/**
 * Controller base. Todos os controllers do sistema devem estendê-la.
 */
abstract class Controller
{
    /**
     * Renderiza uma view dentro do layout padrão.
     *
     * @param string $view Caminho da view relativo a app/views, sem extensão. Ex: 'auth/login'
     * @param array $data Variáveis disponibilizadas para a view
     */
    protected function view(string $view, array $data = [], bool $withLayout = true): void
    {
        extract($data, EXTR_SKIP);

        $viewFile = APP_PATH . '/views/' . $view . '.php';

        if (!file_exists($viewFile)) {
            error_log("View não encontrada: {$viewFile}");
            http_response_code(500);
            require APP_PATH . '/views/errors/500.php';
            return;
        }

        if ($withLayout) {
            require APP_PATH . '/views/layout/header.php';
            require $viewFile;
            require APP_PATH . '/views/layout/footer.php';
        } else {
            require $viewFile;
        }
    }

    protected function redirect(string $path): void
    {
        header('Location: ' . BASE_URL . $path);
        exit;
    }

    /**
     * Retorna todos os dados de $_POST já sem tags HTML perigosas.
     * Para saída em telas, use sempre htmlspecialchars() na view.
     */
    protected function input(string $key, $default = null)
    {
        return $_POST[$key] ?? $_GET[$key] ?? $default;
    }

    /**
     * Interrompe a execução com 403 caso o usuário logado não tenha a
     * permissão informada (formato "modulo.acao", ex: "clientes.criar").
     */
    protected function requirePermission(string $permissao): void
    {
        if (!Auth::can($permissao)) {
            http_response_code(403);
            require APP_PATH . '/views/errors/403.php';
            exit;
        }
    }

    protected function requireLogin(): void
    {
        if (!Auth::check()) {
            $this->redirect('/login');
        }
    }
}
